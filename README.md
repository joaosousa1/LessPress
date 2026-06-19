# 🍃 LessPress

> **LessPress** is a modern, ultra-lightweight, and high-performance PHP boilerplate and micro-framework. It is designed from the ground up to replace heavy CMS platforms like WordPress for smaller websites deployed on affordable, traditional shared hosting environments.

Unlike bloated modern frameworks or massive legacy setups, LessPress emphasizes zero-runtime overhead, localized SQLite data persistence, static asset versioning (cache busting), native Role-Based Access Control (RBAC), and a minimalist CSS footprint using Pico.css.

---

## 🎯 The Philosophy

Most websites on the internet are simple institutional pages, landing sites, portfolios, or micro-member portals. Yet, developers consistently deploy them using legacy systems that require endless plugin security updates, massive database layers, or heavy virtual machines.

**LessPress** shifts the paradigm:

* **⚡ Modern Native PHP:** Built using modern PHP practices, leveraging clean structures, localized utilities (such as explicit `uuidv7.php`), and a direct procedural/OOP balanced architecture.
* **💾 SQLite over MySQL:** No more setting up database users, managing privileges, or dealing with connection lags on oversold shared servers. SQLite stores everything in a single, local file (`database.sqlite`). This makes **backups as simple as copying a file** and migration as easy as moving a folder.
* **🎨 Micro-Frontend with Pico.css:** No heavy JavaScript frameworks or multi-megabyte CSS bundles. LessPress uses **Pico.css** to provide a clean, modern, fully responsive, and automatically themeable (Dark/Light mode) semantic CSS design out of the box with virtually zero footprint.
* **🔒 Built-in RBAC:** No messy plugin trees just to hide a page or structural component. Roles and User validation models are baked straight into the core engine.
* **📦 Compiled Asset & Routing Pipeline:** Includes an isolated local build system that splits optimization tasks into modular specialized scripts, handles asset minification/hashing, and automatically provisions production SEO controls (`robots.txt` and `sitemap.xml`).

---

## 🏗️ Project Architecture & Directory Structure

The framework keeps your raw, human-readable workspace (`src/`) completely separated from the automated, asset-versioned production output (`public_html/`). Root structural directory mounts like `app/` live safely one level above the server web root for maximum hosting isolation:

```text
├── app                           # Core backend system application folder
│   ├── build_tools               # Automation & compilation scripts (Scoped pipeline)
│   │   ├── build-styles.php      # Compiles, minifies, and hashes CSS layers
│   │   ├── build-scripts.php     # Compresses and hashes JavaScript components
│   │   ├── build-images.php      # Optimizes and hashes visual media (PNG, JPG, SVG, GIF)
│   │   ├── build-robots.php      # Generates a secure, virtual-route aware robots.txt
│   │   ├── build-sitemap.xml     # Compiles valid XML search engine mappings
│   │   ├── build-refs.php        # Core engine: Maps hashes and rewrites all view links
│   │   ├── build-manifest.php    # Generates final build audit logs/reports
│   │   └── build.php             # Master sequence pipeline orchestrator
│   ├── core                      # Core framework files (Database wrapper, Router)
│   │   ├── Database.php
│   │   └── tools                 # Internal helpers (UUIDv7 generator, installer blueprints)
│   ├── data                      # File-based embedded persistence layer
│   │   └── database.sqlite       # Main system database (Zero-config, portable backups)
│   ├── dist-views                # Compiled Production Layout Templates
│   │   └── views                 # Processed, hashed, and rewritten layout views
│   ├── documentation             # Local markdown specs & feature architecture blueprints
│   ├── init.php                  # Core app bootstrap sequence
│   ├── models                    # Application business entities
│   │   ├── Role.php
│   │   └── User.php
│   └── src                       # Raw development source code workspace (Your work area)
│       ├── public_html           # Clean development entry files and static assets
│       │   ├── assets            # Raw unhashed static development assets
│       │   │   ├── css
│       │   │   │   ├── pico.min.css 
│       │   │   │   └── style.css
│       │   │   ├── images
│       │   │   └── js
│       │   ├── index.php         # Environment-aware front-controller
│       │   └── install.php       # Initial schema & admin setup script
│       └── views                 # Raw layout fragments (Standard paths, clean formatting)
│           ├── pages             # Core view states (login, admin dashboard, settings)
│           └── partials          # Modular templates (head, headers, footers)
├── docker-compose.yml            # Local isolated development ecosystem
├── Dockerfile                    # Application environment image specification
└── public_html                   # Production / Web root server exposure target (FTP ready)
    ├── assets                    # Post-compiled and cache-busted production assets
    │   ├── css
    │   │   ├── pico.min.css 
    │   │   └── style.[hash].css  # Compressed, uniquely versioned stylesheet
    │   ├── images
    │   │   └── logo.[hash].png   # Hashed production graphics
    │   └── js
    ├── index.php                 # Production front-controller pointing to dist-views
    ├── install.php               # Production-ready initialization script
    ├── robots.txt                # Static production access directives (Auto-generated)
    └── sitemap.xml               # Static production search engine map (Auto-generated)

---

## 🛠️ Compilation & Asset Versioning Workflow

To prevent browsers from caching old versions of your CSS, JS, or images when you deploy updates, LessPress handles **Cache Busting** locally using an efficient single-pass I/O pipeline before code reaches the server:

1. **Development (`app/src/`):** You write clean, standard code. Your development file inside `app/src/views/partials/header.php` references a simple, readable path like `<link rel="stylesheet" href="/assets/css/style.css">`.
2. **The Build Pipeline (`app/build_tools/`):** You trigger the master builder command.
* Specialized asset scripts process files from `app/src/public_html/assets/`, minify content, generate unique 8-character MD5 hashes, and copy them to the production asset root (`public_html/assets/`).
* `build-robots.php` and `build-sitemap.php` write clean SEO mappings directly to `public_html/` without exposing backend paths.
* `build-refs.php` reverse-engineers the generated file names to construct a translation map in memory. It opens your templates **only once**, replaces all raw links with the hashed equivalents, and writes the production artifacts to `app/dist-views/views/`.


3. **Production Output (`public_html/`):** The fully optimized web assets, compiled SEO text maps, and entry front-controllers sit ready inside the root directory.
4. **Deploy:** Upload the contents of the project to your shared hosting environment via FTP. The server runs at native speeds with zero dynamic file resolution costs.

---

## 🔐 Authentication & RBAC Focus

Development is currently prioritizing explicit **Role-Based Access Control (RBAC)** to enable administrative, editing, and regular membership isolation out of the box without complex dependencies.

### Current Mechanics:

1. **`User.php` Model:** Manages user authentication cycles, secure password hashing, and active session validation states.
2. **`Role.php` Model:** Defines multi-level permissions across standard system roles mapped directly to views inside `app/src/views/pages/`:

* **Admin (`admin_board.php`, `create_user.php`)**: Full structural authorization, installer control, and user management.
* **Editor (`editor.php`)**: Content modification access without administrative or configuration rights.
* **Member (`members.php`, `user_settings.php`)**: Private/premium interface states accessible to verified account holders.

3. **Session Interceptor Gates:** Lightweight programmatic gates validated inside `login.php` and `logout.php` before view compilation.

---

## 🚀 Getting Started

### Local Development (with Docker)

1. Clone the repository locally.
2. Run your orchestration layer to bring up the environment:

```bash
docker-compose up --build

```

3. Navigate to `http://localhost:8000` (or your configured port).
4. Run `install.php` to seed the initial SQLite schemas and establish your first Administrative User.

### Triggering the Production Build Pipeline

When you are ready to prepare your production artifacts for server deployment, execute the master builder script from your workspace:

```bash
php app/build_tools/build.php

```

*Note: The builder safely purges old staging contents while preserving root directories, ensuring that active Docker volume mount inodes remain unbroken.*

### Local Development (Native PHP Fallback)

1. Ensure your local PHP server has the SQLite extension enabled (`php_sqlite3`).
2. Run the internal server targeting your public entry directory:

```bash
php -S localhost:8000 -t app/src/public_html/

```
