<?php
// app/core/User.php
require_once __DIR__ . '/../core/tools/uuidv7.php';
require_once __DIR__ . '/Role.php';

class User 
{
    private PDO $db;
    private array $errors = [];
    
    // Database columns mapped to properties
    public ?string $id = null;
    public string $email;
    public string $password_hash;
    public ?string $full_name = null;
    public string $status = 'pending';
    public string $role_id = Role::SUBSCRIBER; 
    public int $requires_password_reset = 0;
    public ?string $created_at = null;
    public ?string $updated_at = null; // Added for compliance auditing

    public function __construct(PDO $db) 
    {
        $this->db = $db;
    }

    /**
     * Hashes and sets the user password securely using Argon2id
     */
    public function setPassword(string $password): void 
    {
        $this->password_hash = password_hash($password, PASSWORD_ARGON2ID);
    }

    /**
     * Validates properties according to data types and constraints
     */
    public function validate(): bool 
    {
        $this->errors = [];

        // Email validation
        if (empty($this->email) || !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = "A valid email address is required.";
        } elseif (strlen($this->email) > 255) {
            $this->errors['email'] = "Email cannot exceed 255 characters.";
        }

        // Password validation (only required on creation)
        if ($this->id === null && empty($this->password_hash)) {
            $this->errors['password'] = "Password is required for new users.";
        }

        // Full Name validation (optional field)
        if ($this->full_name !== null && strlen($this->full_name) > 150) {
            $this->errors['full_name'] = "Full name cannot exceed 150 characters.";
        }

        // Role validation
        $validRoles = [Role::ADMIN, Role::EDITOR, Role::SUBSCRIBER];
        if (!in_array($this->role_id, $validRoles)) {
            $this->errors['role_id'] = "The assigned role is invalid.";
        }

        return empty($this->errors);
    }

    /**
     * Returns validation error messages
     */
    public function getErrors(): array 
    {
        return $this->errors;
    }

    /**
     * Saves or updates the record in the database after validation
     */
    public function save(): bool 
    {
        if (!$this->validate()) {
            return false;
        }

        // Always stamp the last modification time
        $this->updated_at = date('Y-m-d H:i:s');

        // INSERT (New Record)
        if ($this->id === null) {
            $this->id = generate_uuidv7();
            $this->created_at = $this->updated_at; // On creation, created_at matches updated_at

            $sql = "INSERT INTO users (id, email, password_hash, full_name, status, role_id, requires_password_reset, created_at, updated_at) 
                    VALUES (:id, :email, :password_hash, :full_name, :status, :role_id, :requires_password_reset, :created_at, :updated_at)";
        } else {
            // UPDATE (Existing Record)
            $sql = "UPDATE users SET email = :email, full_name = :full_name, status = :status, 
                    role_id = :role_id, requires_password_reset = :requires_password_reset, updated_at = :updated_at WHERE id = :id";
        }

        $stmt = $this->db->prepare($sql);

        // Bind common parameters
        $stmt->bindValue(':id', $this->id);
        $stmt->bindValue(':email', $this->email);
        $stmt->bindValue(':full_name', $this->full_name);
        $stmt->bindValue(':status', $this->status);
        $stmt->bindValue(':role_id', $this->role_id);
        $stmt->bindValue(':requires_password_reset', $this->requires_password_reset, PDO::PARAM_INT);
        $stmt->bindValue(':updated_at', $this->updated_at);

        // Bind insertion-specific parameters
        if (str_contains($sql, 'INSERT')) {
            $stmt->bindValue(':password_hash', $this->password_hash);
            $stmt->bindValue(':created_at', $this->created_at);
        }

        return $stmt->execute();
    }

    /**
     * Updates only the password, clears the reset flag and stamps updated_at
     */
    public function resetPassword(string $newPassword): bool 
    {
        if (empty($newPassword) || strlen($newPassword) < 8) {
            $this->errors['password'] = "The new password must be at least 8 characters long.";
            return false;
        }

        $this->setPassword($newPassword);
        $this->requires_password_reset = 0;
        $this->updated_at = date('Y-m-d H:i:s'); // Audit trail stamp

        $sql = "UPDATE users SET password_hash = :password_hash, requires_password_reset = 0, updated_at = :updated_at WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':password_hash' => $this->password_hash,
            ':updated_at' => $this->updated_at,
            ':id' => $this->id
        ]);
    }

    /**
     * Finds a single user by their Unique ID (UUID v7)
     */
    public static function findById(PDO $db, string $id): ?self 
    {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();

        if (!$data) return null;

        return self::hydrate($db, $data);
    }

    /**
     * Finds a single user by their Unique Email Address
     */
    public static function findByEmail(PDO $db, string $email): ?self 
    {
        $cleanEmail = strtolower(trim($email));

        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $cleanEmail]);
        $data = $stmt->fetch();

        if (!$data) return null;

        return self::hydrate($db, $data);
    }

    /**
     * Helper method to map database array rows back into a clean User Object instance
     */
    private static function hydrate(PDO $db, array $data): self
    {
        $user = new self($db);
        $user->id = $data['id'];
        $user->email = $data['email'];
        $user->password_hash = $data['password_hash'];
        $user->full_name = $data['full_name'];
        $user->status = $data['status'];
        $user->role_id = $data['role_id'];
        $user->requires_password_reset = (int) $data['requires_password_reset'];
        $user->created_at = $data['created_at'];
        $user->updated_at = $data['updated_at'];
        
        return $user;
    }
    
    /**
     * Retrieves all user records from the database ordered by creation date
     * @param PDO $db Database connection instance
     * @return self[] Array of hydrated User object instances
     */
    public static function all(PDO $db): array 
    {
        // 1. Execute the query to pull all records
        // We order by created_at DESC so newer users appear first
        $stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
        $rows = $stmt->fetchAll();

        $users = [];

        // 2. Loop through raw database rows and convert them into domain objects
        foreach ($rows as $data) {
            $users[] = self::hydrate($db, $data);
        }

        return $users;
    }
}