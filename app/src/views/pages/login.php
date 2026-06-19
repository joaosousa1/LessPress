<?php
// app/pages/login.php

// If the user is already logged in.
if (isset($_SESSION['user_id']) && isset($_SESSION['role_id'])) {
    header("Location: /home");
    exit;
}

// 1. Pull the global database connection from the Router scope
global $db;
global $appPath;

// 2. Load core models required for authentication and routing
require_once $appPath . '/app/models/User.php';
require_once $appPath . '/app/models/Role.php';

$pageTitle = "Sign In";
$error = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Query user record using the global $db instance
    $user = User::findByEmail($db, $email);

    // Verify password against stored cryptographic hash and confirm account is active
    if ($user && password_verify($password, $user->password_hash) && $user->status === 'active') {

        // Prevent session fixation attacks by rotating the ID
        session_regenerate_id(true);

        // Populate session state indicators (Role validation matches strict text)
        $_SESSION['user_id']   = $user->id;
        $_SESSION['email']     = $user->email;
        $_SESSION['full_name'] = $user->full_name;
        $_SESSION['role_id']   = trim((string)$user->role_id);

        // CRITICAL GUARD: Check if password rotation is strictly mandated
        if ((int)$user->requires_password_reset === 1) {
            header('Location: /user/settings');
            exit;
        }

        // Standard operational routing based on privilege tier
        if ($_SESSION['role_id'] === Role::ADMIN) {
            header('Location: /admin/board');
        } else {
            header('Location: /user/settings');
        }
        exit;
    } else {
        $error = "Invalid credentials supplied or account suspended.";
    }
}
?>

<div class="container">
    <div style="display: flex;">
        <article style="max-width: 400px; margin-left: auto; margin-right: auto;">
            <header>
                <h1>System Login</h1>
            </header>

            <?php if ($error): ?>
                <p style="color: red; font-size: 1.2em;">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </p>
            <?php endif; ?>

            <form action="/login" method="POST">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="name@company.com">

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Sign In</button>
            </form>
        </article>
    </div>
</div>