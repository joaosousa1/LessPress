<?php
// app/init.php

// 1. Loading core classes safely (they are inside the sibling 'core' directory)
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/models/Role.php';
require_once __DIR__ . '/models/User.php';

// 2. Database config
// =========================================================================
// DATABASE CONFIGURATION FACTORY (AGNOSTIC COMPLIANCE)
// Toggle drivers by commenting/uncommenting the respective block.
// =========================================================================

// Option A: SQLite (Default Standard for Development & Shared Hosting Isolation) mapping directly to the 'data' directory
$dbConfig = [
    'driver'   => 'sqlite',
    'database' => __DIR__ . '/data/database.sqlite'
];

// Option B: MySQL / MariaDB Production Environment Example
/*
$dbConfig = [
    'driver'   => 'mysql',
    'host'     => '127.0.0.1',
    'port'     => '3306', // Optional: defaults to 3306 if omitted
    'database' => 'production_mysql_db',
    'username' => 'db_user_mysql',
    'password' => 'HardenedMySQLPassword2026$'
];
*/

// Option C: PostgreSQL Production Enterprise Environment Example
/*
$dbConfig = [
    'driver'   => 'pgsql',
    'host'     => '127.0.0.1',
    'port'     => '5432', // Optional: defaults to 5432 if omitted
    'database' => 'production_postgres_db',
    'username' => 'db_user_postgres',
    'password' => 'HardenedPostgresPassword2026$'
];
*/

// Initialize the core agnostic PDO connection instance
$db = Database::connect($dbConfig);

// 3. Automated Schema Creation (DDL Execution)
// Standard ANSI SQL syntax used to support SQLite, MySQL, and PostgreSQL gracefully
$db->exec("CREATE TABLE IF NOT EXISTS roles (
    id VARCHAR(50) PRIMARY KEY,
    description TEXT
)");

$db->exec("CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(36) PRIMARY KEY, 
    email VARCHAR(255) NOT NULL UNIQUE, 
    password_hash VARCHAR(255) NOT NULL, 
    full_name VARCHAR(150) NULL, 
    status VARCHAR(20) NOT NULL, 
    role_id VARCHAR(50) NOT NULL,
    requires_password_reset INTEGER DEFAULT 0, 
    created_at VARCHAR(30) NOT NULL,
    updated_at VARCHAR(30) NOT NULL, -- Added for compliance tracking
    FOREIGN KEY(role_id) REFERENCES roles(id)
)");

echo "=== STEP 1: SEEDING SYSTEM ROLES ===\n";
Role::seed($db);
echo "Roles (admin, teamMember, subscriber) successfully synchronized.\n";

// State inspection: Check if the system has already been seeded to prevent override collisions
$stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE role_id = :role");
$stmt->execute([':role' => Role::ADMIN]);
$adminExists = (int)$stmt->fetchColumn() > 0;

if ($adminExists) {
    echo "\n[!] Notice: Database already contains an operational Admin account.\n";
    return false; // Intercepts the process and alerts install.php that it is already configured
}

echo "\n=== STEP 2: SEEDING MANDATORY SUPER USER (ADMIN) ===\n";
// Using Email as the primary unique authentication key
$adminEmail = "admin@system.local";
$adminUser = User::findByEmail($db, $adminEmail);

if (!$adminUser) {
    $adminUser = new User($db);
    $adminUser->email = $adminEmail;
    $adminUser->full_name = "Master Administrator";
    $adminUser->status = "active";
    $adminUser->role_id = Role::ADMIN; 
    $adminUser->setPassword("123");
    $adminUser->requires_password_reset = 1; // Enforces immediate profile update on login

    if ($adminUser->save()) {
        echo "Super User initialized! ID: {$adminUser->id} | Email: {$adminUser->email} | Role: {$adminUser->role_id}\n";
    }
}

echo "\n=== STEP 3: CREATING STANDARD SUBSCRIBER DEMO ===\n";
$subscriberEmail = "subscriber@gmail.local";
$regularUser = User::findByEmail($db, $subscriberEmail);

if (!$regularUser) {
    $regularUser = new User($db);
    $regularUser->email = $subscriberEmail;
    $regularUser->full_name = "John Doe Subscriber";
    $regularUser->status = "active";
    $regularUser->role_id = Role::SUBSCRIBER; 
    $regularUser->setPassword("123");

    if ($regularUser->save()) {
        echo "Regular Subscriber created! ID: {$regularUser->id} | Email: {$regularUser->email} | Role: {$regularUser->role_id}\n";
    }
}

return true; // Signals successful fresh install back to install.php