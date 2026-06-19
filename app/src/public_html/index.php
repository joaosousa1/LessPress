<?php
// public_html/index.php
session_start();

// 1. PATH AUTO-DETECTION ENGINE
// Captures the folder where index.php is placed
// But we check the parent of public_html to know if we are in 'src'
$parentDirectoryPath = dirname(__DIR__, 1);
$parentDirectoryName = basename($parentDirectoryPath);

if ($parentDirectoryName === 'src') {
        // ----------------------------------------------------------------
        // DEVELOPMENT MODE (Running from: /app/src/public_html/index.php)
        // ----------------------------------------------------------------
        define('APP_PATH', dirname(__DIR__, 3));
        define('PUB_PATH', dirname(__DIR__, 1));
    } else {
        // ----------------------------------------------------------------
        // PRODUCTION MODE (Running from live: /public_html/index.php)
        // ----------------------------------------------------------------
        define('APP_PATH', dirname(__DIR__, 1));
        //define('PUB_PATH', dirname(__DIR__, 1) . '/app/src/');
        define('PUB_PATH', dirname(__DIR__, 1) . '/app/dist-views');
}

//echo APP_PATH;
//echo '<br>';
//echo PUB_PATH;

// 2. INITIALIZE GLOBAL VARIABLES OUTSIDE ANY SCOPE
global $db;
global $appPath;
global $currentUser;

$currentUser = null; 
$appPath = APP_PATH;

// 3. LOAD GLOBAL CORE DEPENDENCIES
require_once APP_PATH . '/app/core/Database.php';
require_once APP_PATH . '/app/models/Role.php';
require_once APP_PATH . '/app/models/User.php';
require_once APP_PATH . '/app/models/Files.php';

// 4. SINGLE DATABASE CONNECTION INSTANCE
$dbConfig = ['driver' => 'sqlite', 'database' => APP_PATH . '/app/data/database.sqlite'];
$db = Database::connect($dbConfig);

// 5. SANITIZE AND PARSE INCOMING ROUTE (Forces consistent syntax)
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route = '/' . trim($requestUri, '/');
if ($route === '//') {
    $route = '/';
}

// 6. GRANULAR RBAC ROUTING MAP (Relative to APP_PATH)
$routes = [
    '/'                => ['file' => '/views/pages/home.php',              'allowed_roles' => []],
    '/home'            => ['file' => '/views/pages/home.php',              'allowed_roles' => []],
    '/about'           => ['file' => '/views/pages/about.php',             'allowed_roles' => []],
    '/contacts'        => ['file' => '/views/pages/contacts.php',          'allowed_roles' => []],
    '/login'           => ['file' => '/views/pages/login.php',             'allowed_roles' => []],
    '/logout'          => ['file' => '/views/pages/logout.php',            'allowed_roles' => []],
    '/editor'          => ['file' => '/views/pages/editor.php',            'allowed_roles' => [Role::ADMIN, Role::EDITOR]],
    '/user/settings'   => ['file' => '/views/pages/user_settings.php',     'allowed_roles' => [Role::ADMIN, Role::EDITOR, Role::SUBSCRIBER]],
    '/create/user'     => ['file' => '/views/pages/create_user.php',       'allowed_roles' => [Role::ADMIN]],
    '/admin/board'     => ['file' => '/views/pages/admin_board.php',       'allowed_roles' => [Role::ADMIN]],
    '/upload'          => ['file' => '/views/pages/upload.php',            'allowed_roles' => [Role::ADMIN, Role::EDITOR]],

    // RESOURCE ENDPOINTS
    '/upload-handler'  => ['file' => '/core/endpoints/upload_handler.php', 'allowed_roles' => [Role::ADMIN, Role::EDITOR]],
    '/view-file'       => ['file' => '/core/endpoints/view_file.php',      'allowed_roles' => [Role::ADMIN, Role::EDITOR, Role::SUBSCRIBER]]
];

// 7. ROUTE DISPATCHER AND MIDDLEWARE CHECK
if (array_key_exists($route, $routes)) {
    $pageInfo = $routes[$route];
    
    if (!empty($pageInfo['allowed_roles'])) {
        
        // Guard A: Strict Authentication Check
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id'])) {
            header('Location: /login');
            exit;
        }
        
        // Guard B: RBAC Authorization Level Check (Pure String Matching)
        $currentSessionRole = trim((string)$_SESSION['role_id']);

        if (!in_array($currentSessionRole, $pageInfo['allowed_roles'], true)) {
            header("HTTP/1.1 403 Forbidden");
            $pageTitle = "403 - Forbidden Access";
            // require_once '/var/www/app/dist-views/views/partials/head.php';
            require_once PUB_PATH . '/views/partials/head.php';
            echo "DEBUG " . PUB_PATH . "<br>";
            echo "<main><article><header><h1>403 Forbidden</h1></header><p>Your account tier does not have permission to view this resource. <br> <a href='/home'>Back to Home</a></p></article></main>";
            // require_once '/var/www/app/dist-views/views/partials/footer.php';
            require_once PUB_PATH . '/views/partials/footer.php';
            exit;
        }

        // Guard C: Account Lifecycle and Status Verification
        $currentUser = User::findById($db, $_SESSION['user_id']);
        if (!$currentUser || $currentUser->status !== 'active') {
            header('Location: /logout');
            exit;
        }
        
        // Guard D: Governance Rule (Enforce temporary password reset)
        if ((int)$currentUser->requires_password_reset === 1 && $route !== '/user/settings') {
            header('Location: /user/settings');
            exit;
        }
    }

    // FALLBACK SEO METADATA
    $pageTitle = "Home";

    // 8. OUTPUT BUFFERING & ROUTE EXECUTION ENGINE
    // Core endpoints execute raw code and exit, while views run through the layout assembly template.
    if (str_starts_with($pageInfo['file'], '/core/')) {
        // Run core logic file directly from the uncompiled application directory scope
        require_once APP_PATH . '/app' . $pageInfo['file'];
        exit;
    }

    // Standard View Workflow
    ob_start();
    require_once PUB_PATH . $pageInfo['file'];
    $mainContent = ob_get_clean();
    // 9. COMPONENT LAYOUT ASSEMBLY TODO: layout mediaquery
    require_once PUB_PATH . '/views/partials/head.php';
    require_once PUB_PATH . '/views/partials/top.php';
    require_once PUB_PATH . '/views/partials/menu.php';
    echo $mainContent; 
    require_once PUB_PATH . '/views/partials/footer.php';

} else {
    header("HTTP/1.1 404 Not Found");
    $pageTitle = "404 - Page Not Found";
    require_once PUB_PATH . '/views/partials/head.php';
    require_once PUB_PATH . '/views/partials/top.php';
    echo '<div class="container" ><h1>404 Not Found</h1><p>The requested URL path could not be resolved.</p></div>';
    require_once PUB_PATH . '/views/partials/footer.php';
    exit;
}
