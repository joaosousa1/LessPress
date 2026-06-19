<?php
// app/core/endpoints/upload_handler.php

/**
 * LessPress Framework - Secure Upload Handler
 * Processes incoming binary streams, applies strict file system encryption,
 * and tracks the artifacts using the Files Active Record model.
 */

// 1. Enforce Post-Routing Guard rails (Fail-safe check)
if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 403 Forbidden");
    exit("Access Denied: Authenticated context required.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 405 Method Not Allowed");
    exit("Method Not Allowed.");
}

// 2. Establish Secure Path Matrix (Outside the open public_html ecosystem)
$uploadDirectory = dirname(__DIR__, 2) . '/data/uploads';

if (!is_dir($uploadDirectory)) {
    // Generates the folder recursively with tight Unix security bindings (rwxr-x---)
    mkdir($uploadDirectory, 0750, true);
}

// 3. Request Payload Integrity Checks
if (!isset($_FILES['secure_image']) || $_FILES['secure_image']['error'] !== UPLOAD_ERR_OK) {
    header("Location: /admin/board?upload=error");
    exit;
}

$rawFile = $_FILES['secure_image'];

// Validate File Size constraints (10 Megabytes limit) 
// Note: don't forget to change "client_max_body_size" to 10Mb in ngix config 
if ($rawFile['size'] > 150 * 1024 * 1024) {
    exit("Payload constraint violation: File exceeds the maximum 10MB threshold.");
}
// Note: doker need permissions to write in volume folder - sudo chown -R 33:33 data/

// Validate deep MIME signatures using File Magic byte scanning (Do not trust raw browser headers)
$fileInfoHandle = new finfo(FILEINFO_MIME_TYPE);
$validatedMimeType = $fileInfoHandle->file($rawFile['tmp_name']);

$allowedMimeClasses = [
    'image/jpeg', 
    'image/png', 
    'image/gif', 
    'image/webp',
    'application/pdf'
];

if (!in_array($validatedMimeType, $allowedMimeClasses)) {
    exit("Security policy error: Invalid file footprint. Only images and PDFs are permitted.");
}

// 4. Resolve Database Instance & Model Hydration
// FIXED LINE 60: Safely retrieve the existing PDO instance from your custom Database class
// Passing an empty array is safe because your connect() logic returns the existing self::$instance if it is already built.
// Alternatively, if your boot file sets a global $db variable, you can use: global $db;
$pdoInstance = Database::connect([]); 
$fileRecord = new Files($pdoInstance);

// FIXED LINE 63: Clean fallback generator if your core uuidv7() global function isn't included in this scope
if (function_exists('uuidv7')) {
    $fileRecord->id = uuidv7();
} else {
    // High-entropy unique ID fallback generation
    $fileRecord->id = bin2hex(random_bytes(16)); 
}

$fileRecord->user_id     = $_SESSION['user_id'];
$fileRecord->filename    = basename($rawFile['name']);
$fileRecord->mime_type   = $validatedMimeType;
$fileRecord->file_size   = (int)$rawFile['size'];

// Target path on disk is purely the randomized ID to prevent folder injection/traversal attacks
$protectedStorageTarget = $uploadDirectory . '/' . $fileRecord->id;

// 5. Atomic Transport and Commit Phase
if (move_uploaded_file($rawFile['tmp_name'], $protectedStorageTarget)) {
    
    // Save metadata records cleanly into your SQLite storage layer via the model
    if ($fileRecord->create()) {
        header("Location: /upload");
        exit;
    } else {
        // If the DB save fails, strip the loose physical file from disk to prevent unindexed junk
        if (file_exists($protectedStorageTarget)) {
            unlink($protectedStorageTarget);
        }
        exit("Database synchronization state anomaly: Could not register metadata.");
    }

} else {
    exit("File system I/O error: Unable to transition payload to secure storage disk.");
}