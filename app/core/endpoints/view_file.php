<?php
// app/core/endpoints/view-file.php

/**
 * LessPress Framework - Secure Media & Document Streamer
 * intercepts resource requests, validates ownership via active session scopes,
 * and streams encrypted binaries directly into the browser viewport.
 */

global $db; 

// 1. Structural Guard Rails (Fail-safe check since index.php handles this)
if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 403 Forbidden");
    exit("Access Denied: Authenticated context required.");
}

// 2. Capture and Sanitize Request Parameter
$fileId = $_GET['id'] ?? '';
if (empty($fileId)) {
    header("HTTP/1.1 400 Bad Request");
    exit("Missing resource identifier.");
}

// 3. Query Metadata via the Files Entity Model
// The $db PDO instance is globally accessible here as initialized by index.php boot
$file = Files::findById($db, $fileId);

if (!$file) {
    header("HTTP/1.1 404 Not Found");
    exit("Resource record not found in system registry.");
}

// 4. Multi-Tenant / RBAC Security Cross-Check (Resource Governance)
// Optimization: If the user is an administrator, let them audit any file.
// If they are regular users, they can ONLY view files that they uploaded themselves.
if ($_SESSION['role_id'] !== Role::ADMIN && $file->user_id !== $_SESSION['user_id']) {
    header("HTTP/1.1 403 Forbidden");
    exit("Access Denied: You do not own this resource artifact.");
}

// 5. Resolve Physical File System Target Location
$protectedStorageTarget = dirname(__DIR__, 2) . '/data/uploads/' . $file->id;

if (!file_exists($protectedStorageTarget)) {
    header("HTTP/1.1 404 Not Found");
    exit("Physical binary payload is missing from storage array.");
}

// 6. Output Buffer Management
// Wipe any potential whitespaces or layout leak outputs before writing binary buffers
if (ob_get_level()) {
    ob_end_clean();
}

// 7. Inject Binary Stream Headers
header("Content-Type: " . $file->mime_type);
header("Content-Length: " . $file->file_size);

// SYSTEM DESIGN TRICK: "inline" instructs the browser to open images and PDFs 
// directly inside the window workspace tab instead of forcing an instant file download.
header("Content-Disposition: inline; filename=\"" . addslashes($file->filename) . "\"");
header("Cache-Control: private, max-age=86400");

// 8. Stream Payload directly from the unexposed filesystem matrix
readfile($protectedStorageTarget);
exit;