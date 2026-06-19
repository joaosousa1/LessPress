<?php
// app/models/Files.php

class Files 
{
    private PDO $db;

    public string $id;
    public string $user_id;
    public string $filename;
    public string $mime_type;
    public int $file_size;
    public string $created_at;

    public function __construct(PDO $db) 
    {
        $this->db = $db;
    }

    /**
     * Initializes the SQLite uploads table if it doesn't exist
     */
    public static function initTable(PDO $db): void 
    {
        $db->exec("CREATE TABLE IF NOT EXISTS uploads (
            id TEXT PRIMARY KEY,
            user_id TEXT NOT NULL,
            filename TEXT NOT NULL,
            mime_type TEXT NOT NULL,
            file_size INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
    }

    /**
     * Registers a new file metadata block into the SQLite database
     */
    public function create(): bool 
    {
        $stmt = $this->db->prepare("
            INSERT INTO uploads (id, user_id, filename, mime_type, file_size) 
            VALUES (:id, :user_id, :filename, :mime_type, :file_size)
        ");
        
        return $stmt->execute([
            ':id'        => $this->id,
            ':user_id'   => $this->user_id,
            ':filename'  => $this->filename,
            ':mime_type' => $this->mime_type,
            ':file_size' => $this->file_size
        ]);
    }

    /**
     * Finds a file record by its UUIDv7 index and returns an hydrated instance
     */
    public static function findById(PDO $db, string $id): ?self 
    {
        $stmt = $db->prepare("SELECT * FROM uploads WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return null;
        }

        $file = new self($db);
        $file->id         = $row['id'];
        $file->user_id     = $row['user_id'];
        $file->filename    = $row['filename'];
        $file->mime_type   = $row['mime_type'];
        $file->file_size   = (int)$row['file_size'];
        $file->created_at  = $row['created_at'];

        return $file;
    }

    /**
     * Lists all uploaded files belonging to a specific user (Useful for RBAC scoping)
     * Returns an array of populated Files instances
     */
    public static function findByUserId(PDO $db, string $userId): array 
    {
        $stmt = $db->prepare("SELECT * FROM uploads WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        foreach ($rows as $row) {
            $file = new self($db);
            $file->id         = $row['id'];
            $file->user_id     = $row['user_id'];
            $file->filename    = $row['filename'];
            $file->mime_type   = $row['mime_type'];
            $file->file_size   = (int)$row['file_size'];
            $file->created_at  = $row['created_at'];
            $results[] = $file;
        }

        return $results;
    }
}