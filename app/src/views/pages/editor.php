<?php
// app/pages/staff.php

// 1. Pull the globally validated user instance into this file's scope
global $currentUser;
global $appPath;

// 2. Load necessary dependencies for template conditional logic
require_once $appPath . '/app/models/Role.php';
//require_once __DIR__ . '/../models/Role.php';

// 3. Override SEO Metadata (Captured cleanly via Output Buffering)
$pageTitle = "Editor area";
?>

<div class="container">
    <h1>🔒 Editor-Only Protected Area</h1>
    <p>Welcome to the private dashboard, <strong><?php echo htmlspecialchars($currentUser->full_name ?? $currentUser->email); ?></strong>!</p>
    
    <div style="background-color: #f4f4f4; padding: 15px; border-left: 5px solid #ef2e10; margin: 20px 0;">
        <h3 style="color: #151515;">Your Profile Meta-Data (Audit Clearance)</h3>
        <ul style="color: #151515;">
            <li><strong>Internal ID:</strong> <?php echo htmlspecialchars($currentUser->id); ?></li>
            <li><strong>Verified Email:</strong> <?php echo htmlspecialchars($currentUser->email); ?></li>
            <li><strong>Assigned Privilege Role:</strong> <?php echo htmlspecialchars($currentUser->role_id); ?></li>
            <li><strong>Account Created:</strong> <?php echo htmlspecialchars($currentUser->created_at); ?></li>
        </ul>
    </div>

    <p>This content is cryptographically hidden behind our session middleware. Visitors cannot access this URL.</p>

</div>