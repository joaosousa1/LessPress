<?php
// public_html/install.php

echo "<pre>";
echo "=== SYSTEM INSTALLATION CHECK ===\n";

$initScriptPath = __DIR__ . '/../app/init.php';

if (!file_exists($initScriptPath)) {
    die("CRITICAL ERROR: Core installation file missing.\n");
}

// Execute init.php and capture its return value
$installationResult = include $initScriptPath;

if ($installationResult === false) {
    echo "\n-------------------------------------------------------------\n";
    echo "⚠️  SECURITY ALERT: SYSTEM ALREADY INSTALLED!\n";
    echo "The database is active and an administrator account already exists.\n";
    echo "For safety reasons, you must DELETE 'public_html/install.php' NOW.\n";
    echo "-------------------------------------------------------------\n";
} else {
    echo "\n=============================================================\n";
    echo "✅ SUCCESS: Database tables built and Super User seeded!\n";
    echo "Login Email: admin@system.local\n";
    echo "Password: 123\n\n";
    echo "⚠️  CRITICAL: Delete this 'install.php' file immediately from production.\n";
    echo "=============================================================\n";
}
echo "</pre>";