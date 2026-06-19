<?php
// app/pages/create_user.php

// Force the database instance from the Router into this scope
global $db; 
global $appPath;

// 2. Force dependency links inside this view scope
require_once $appPath . '/app/models/User.php';
require_once $appPath . '/app/models/Role.php';

$pageTitle = "Provision New User Account";
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $roleId   = $_POST['role_id'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check if email already exists before processing
    if (User::findByEmail($db, $email)) {
        $error = "This email address is already registered in the system.";
    } else {
        // Instantiate the User model using the router's active $db connection
        $newUser = new User($db);
        $newUser->email = $email;
        $newUser->full_name = empty($fullName) ? null : $fullName;
        $newUser->role_id = $roleId;
        $newUser->status = 'active'; 
        $newUser->requires_password_reset = 1; // Forces the user to rotate password on first login
        $newUser->setPassword($password);

        // Save to database using internal validations
        if ($newUser->save()) {
            $success = "User account successfully created for " . htmlspecialchars($email) . "!";
        } else {
            // Catch validation layer messages
            $validationErrors = $newUser->getErrors();
            $error = reset($validationErrors) ?: "Failed to create user account.";
        }
    }
}
?>

<div class="container">
    <h1>Create New System User</h1>

    <?php if ($error): ?>
        <p style="color: red;"><strong>Error:</strong> <?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p style="color: green;"><strong>Success:</strong> <?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form action="/admin/create/user" method="POST">
        <div>
            <label for="email">User Email Address (Login Identifier):</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div>
            <label for="full_name">Full Name (Optional):</label>
            <input type="text" id="full_name" name="full_name">
        </div>

        <div>
            <label for="role_id">System Role / Privilege Tier:</label>
            <select id="role_id" name="role_id" required>
                <option value="<?php echo Role::SUBSCRIBER; ?>">Subscriber (Standard Client)</option>
                <option value="<?php echo Role::EDITOR; ?>">Editor (Staff / Editor)</option>
                <option value="<?php echo Role::ADMIN; ?>">Administrator (Full System Control)</option>
            </select>
        </div>

        <div>
            <label for="password">Temporary Password:</label>
            <input type="password" id="password" name="password" required>
            <small style="display: block; color: gray;">The user will be strictly forced to update this password on their very first login session.</small>
        </div>

        <br>
        <button type="submit">Provision User Account</button>
    </form>
    </div>