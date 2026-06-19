<?php
// app/Role.php

class Role 
{
    private PDO $db;

    public string $id;
    public string $name;
    public ?string $description = null;

    // Hardcoded roles for consistency across environments
    const ADMIN = 'admin';
    const EDITOR = 'editor';
    const SUBSCRIBER = 'subscriber';

    public function __construct(PDO $db) 
    {
        $this->db = $db;
    }

    /**
     * Seeds the default roles into the database if they don't exist
     */
    public static function seed(PDO $db): void 
    {
        $roles = [
            self::ADMIN => 'System Administrator with full access keys.',
            self::EDITOR => 'Staff member with management capabilities.',
            self::SUBSCRIBER => 'Regular client or subscriber with basic access.'
        ];

        $stmt = $db->prepare("INSERT OR IGNORE INTO roles (id, description) VALUES (:id, :description)");
        
        foreach ($roles as $id => $description) {
            $stmt->execute([
                ':id' => $id,
                ':description' => $description
            ]);
        }
    }
}