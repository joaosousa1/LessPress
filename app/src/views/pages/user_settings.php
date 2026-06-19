<?php
// app/pages/user_settings.php

// 1. Pull global architecture references from the Router scope
global $db;
global $currentUser;
global $appPath;

// 2. Load the User model to access the update methods
require_once $appPath . '/app/models/User.php';
require_once $appPath . '/app/models/Role.php';

$pageTitle = "Account Settings & Security";
$error = null;
$success = null;

// Determine if this is a forced governance password rotation
$isForcedReset = ((int)$currentUser->requires_password_reset === 1);

// Handle the password update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_password') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Basic frontend-level validation match check
    if ($newPassword !== $confirmPassword) {
        $error = "The new passwords do not match. Please re-type them.";
    } else {
        // Use the model's built-in resetPassword method
        // This hashes the password, flips requires_password_reset to 0, and updates the DB
        if ($currentUser->resetPassword($newPassword)) {
            $success = "Your password has been securely updated!";

            // If it was a forced reset, refresh the flag locally so the UI updates immediately
            $isForcedReset = false;
        } else {
            // Catch validation messages from the model (e.g., minimum 8 characters)
            $validationErrors = $currentUser->getErrors();
            $error = reset($validationErrors) ?: "An error occurred while updating your password.";
        }
    }
}
?>

<div class="container">
    <article>
        <header>
            <h1>Account Security Settings</h1>
        </header>
        <div style="background-color: #f4f4f4; padding: 15px; border-left: 5px solid #2196F3; margin: 20px 0;">
            <h3 style="color: #151515;">Your Profile Meta-Data (Audit Clearance)</h3>
            <ul style="color: #151515;">
                <li><strong>Internal ID:</strong> <?php echo htmlspecialchars($currentUser->id); ?></li>
                <li><strong>Verified Email:</strong> <?php echo htmlspecialchars($currentUser->email); ?></li>
                <li><strong>Assigned Privilege Role:</strong> <?php echo htmlspecialchars($currentUser->role_id); ?></li>
                <li><strong>Account Created:</strong> <?php echo htmlspecialchars($currentUser->created_at); ?></li>
            </ul>
        </div>
        <?php if ($isForcedReset): ?>
            <p>
                <strong>⚠️ Action Required:</strong> Your account was provisioned with a temporary password.
                You must pick a new secure password before you can access the rest of the system features.
            </p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p>
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p>
                <strong>Success:</strong> <?php echo htmlspecialchars($success); ?>
            </p>
            <?php if (!$isForcedReset): ?>
                <p><a href="/secure"><strong>👉 Proceed to your Secure Dashboard</strong></a></p>
            <?php endif; ?>
        <?php endif; ?>
        <h4>Change password</h4>
        <?php if ($isForcedReset || !$success): ?>
            <form action="/user/settings" method="POST">
                <input type="hidden" name="action" value="update_password">

                <label for="new_password">New Secure Password</label>
                <input type="password" id="new_password" name="new_password" required minlength="8" placeholder="Minimum 8 characters">

                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8">

                <button type="submit">Update Account Password</button>
            </form>
        <?php endif; ?>
    </article>
        </div>