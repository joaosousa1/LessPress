# System Architecture, Compliance & Lifecycle Blueprint

## 1. Context & Executive Summary
This document serves as the absolute source of truth for the project state, codebase rules, and file mapping. Use this context to continue building, refactoring, or extending the application without changing its core structural design or patterns.

---

## 2. Core Architectural Principles & Stack
The application is a lightweight, secure, and modular PHP system specifically engineered to run seamlessly on **Shared Hosting environments** without any third-party package dependencies (Zero-Composer requirement).

* **Backend Language:** PHP 7.4+ / PHP 8.x native code.
* **Architecture Pattern:** Page-Controller MVC (Model-View-Page).
* **Database Engine:** Agnostic raw PDO supporting SQLite, MySQL, and PostgreSQL.
* **Identity & Authentication:** * Primary Keys: **UUID v7** (Time-sorted, 128-bit cryptographically secure identifiers).
    * Single Sign-On ID: **Email addresses** are used as the unique username/login identifier.
* **Security Standards:**
    * Passwords: Hardened using native **Argon2id** (`PASSWORD_ARGON2ID`).
    * Session Security: Automatic identification rotation (`session_regenerate_id`).
* **Code Style & Naming Conventions:**
    * Core Classes & Models: `PascalCase.php` (e.g., `User.php`).
    * Public Pages / Page Controllers: `snake_case.php` (e.g., `login.php`).
    * Documentation & Comments: Strictly in **English**.
* **URL Routing Translation Rule:**
    * Clean public routing URLs use forward slashes (`/`) to represent nested resources, which map directly to flat file-system structures using underscores (`_`). 
    * The underscore in file names translates directly into a routing slash.
    * Example: The file `../app/pages/user_settings.php` is explicitly registered in the Router map under the public path `/user/settings`.

---

## 3. Directory & File Tree Structure

```text
/home/user/ (Private Base Directory)
├── app/                               # Private Application Container
│   ├── build_tools/                   # Compilation, minification & build automation
│   │   └── build.php                  # Native PHP build script (CLI operational)
│   │
│   ├── core/                          # Infrastructure & Low-Level Core System
│   │   ├── Database.php               # Agnostic raw PDO connection manager instance
│   │   └── tools/                     # Core system utilities & internal scripts
│   │       ├── install.txt            # Safe text-only backup of the installer hook blueprint
│   │       └── uuidv7.php             # Native bitwise/time UUID v7 generator
│   │
│   ├── data/                          # Stateful Private Data Engine
│   │   ├── database.sqlite            # SQLite production database file
│   │   └── uploads/                   # Secured binary assets (Persistent across builds)
│   │
│   ├── dist/                          # Target Distribution Output (Generated via build-tools)
│   │   ├── public_html/               # Optimized public web files ready for deployment
│   │   └── views/                     # Processed internal layouts and page controllers
│   │
│   ├── documentation/                 # Compliance and Knowledge management
│   │   ├── ARCHITECTURE.md            # Modernized Master Meta-Prompt & Status file
│   │   ├── ARCHITECTURE_OLD.md        # Legacy architecture reference
│   │   └── TODO.md                    # System Execution Status & Active Milestones
│   │
│   ├── init.php                       # Database DDL factory and automated seeder
│   │
│   ├── models/                        # M from MVC: Data Domain & Logic Layer (Active Record)
│   │   ├── Role.php                   # Role entity (RBAC rules, database mapping & seeders)
│   │   └── User.php                   # User entity (Auth, validations, lifecycle states)
│   │
│   ├── src/                           # Source Development Environment (Source Code)
│   │   ├── public_html/               # Raw public web assets and entry points
│   │   │   ├── .htaccess              # Apache URL rewriting router middleware engine
│   │   │   ├── assets/                # Static frontend assets
│   │   │   │   ├── css/
│   │   │   │   │   ├── pico.min.css   # Classless CSS framework dependency
│   │   │   │   │   └── style.css      # Custom platform style extensions
│   │   │   │   ├── images/
│   │   │   │   └── js/
│   │   │   ├── index.php              # Main application frontend entry point router for dev mode
│   │   │   └── install.php            # Active stateful web-trigger installer
│   │   │
│   │   └── views/                     # Raw Templates & Controllers
│   │       ├── pages/                 # C from MVC: Page Controllers / Routing Actions
│   │       │   ├── admin_board.php
│   │       │   ├── create_user.php
│   │       │   ├── editor.php
│   │       │   ├── home.php
│   │       │   ├── login.php
│   │       │   ├── logout.php
│   │       │   ├── members.php
│   │       │   └── user_settings.php
│   │       │
│   │       └── partials/              # V from MVC: Global Layout Partials / UI Components
│   │           ├── footer.php
│   │           ├── head.php
│   │           └── top.php
│   │
│   └── tests/                         # Test suite isolation layer
│       └── test_01.php                # Core application assertion tests
│
├── nginx_config/                      # Docker volume folder for nginx configuration
└── public_html/                       # Public Web-Root (Mirror copy from app/dist/public_html)
    └── index.php                      # Main application frontend entry point router for production
```

## 4. Database Architectural Guidelines & Typings
To preserve maximum engine portability (agnostic compatibility between SQLite, MySQL, and PostgreSQL) and support structural compliance auditing, specific column definitions are enforced:

### 4.1 Role-Based Access Control (RBAC Typings)
* Roles are strictly validated as **text strings** matching the exact values of the hardcoded class constants (`admin`, `teamMember`, `subscriber`). 
* **Never** use integer-based casting on `role_id` fields during session storage or matching comparisons, as database drivers represent text identifiers distinctly from mathematical digits.

### 4.2 Account Status Flag (`status` column)
* Handled as a native **TEXT/VARCHAR String**, explicitly avoiding binary booleans. 
* This follows the State Machine Pattern allowing a detailed user lifestyle progression tracking matrices: `pending` (created but unverified), `active` (operational), `suspended` (blocked due to security incidents), and `archived` (soft-deleted profile preserved for auditing and relational ledger continuity).

### 4.3 Governance Password Rotation (`requires_password_reset` column)
* Represented as a native **INTEGER** containing `0` or `1`, avoiding engine-specific boolean variations.
* This addresses relational engine inconsistencies (where MySQL treats BOOLEAN as `TINYINT(1)`, SQLite interprets it as standard integers, and PostgreSQL handles it as a distinct primitive datatype). This column guarantees uniform execution when handled by PHP's PDO.

---

## 5. Scope Management & Output Buffering (Engine Middleware rules)
Because the central Front Controller (`public_html/index.php`) matches and executes page actions inside an isolated output buffer capturing scope variables (`ob_start()`), standard variable inheritance is restricted:

* Every protected file or controller script loaded from the `app/view/pages/` directory that demands access to the database engine or authenticated entity context **MUST** explicitly invoke the global architecture bindings at the immediate opening of its execution block:
```php
global $db;
global $currentUser;
```

## 6. Shared Hosting Deployment Blueprint

To support clean user-facing URL routing structures on strict shared hosting servers without forcing /index.php/ prefixes inside the browser address line, the public_html/.htaccess configuration file must actively enforce the following engine settings:
Apache

RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

## 7. Development, Compilation & Shared Hosting Deployment Pipeline

This section defines the operational lifecycle required to run the platform locally, compile the source files into a distribution build, and safely deploy the application to a production shared hosting architecture.

### 7.1 Local Development Environment (Source Mode)
To run and develop the application locally without compilation overhead, use the native PHP built-in web server pointing strictly to the development public web-root directory (`src`).

* **Execution Command:**
```bash
cd app/src/public_html
php -S 127.0.0.1:8000
```

Local Access Endpoint: http://127.0.0.1:8000

Internal Runtime Context: The main index.php entry point leverages the path auto-detection engine. It recognizes the parent folder as src, triggering Development Mode. It maps APP_PATH and PUB_PATH directly to the live raw source files for real-time iteration.

### 7.2 The Compilation Engine (Build Stage)

Before moving code out of development, the raw source assets must be processed through the local automated compilation pipeline to isolate and prepare the distribution tier (dist).

* **Execution Command:**
```bash
cd app/build-tools
php build.php
```

Target Output: The compilation engine populates the target /app/dist/ container. It places optimized, public-facing web files into /app/dist/public_html/ and layout components into /app/dist/views/.
Copy the compiled, optimized files from /home/$USER/app/dist/public_html/* to the live production web-root at /home/$USER/public_html/.
