<body>
    <div id="top">
        <div style="display: inline-flex; width: 100%;">
            <img id="logo" src="/assets/images/logo.png" alt="logo">
            <nav style="margin-left: auto;">
                <ul>
                    <li><a href="/home">Home</a></li>
                    <li><a href="/about">About</a></li>
                    <li><a href="/contacts">Contacts</a></li>
                </ul>
            </nav>
            <ul id="menu-user">
                <?php if (isset($_SESSION['user_id']) && isset($_SESSION['role_id'])): ?>
                        <li><b><?php echo htmlspecialchars($_SESSION['email']) . "</b>"; ?></li>
                        <li><a href="/logout">Logout</a></li>
                    <?php else: ?>
                        <?php if ($_SERVER['REQUEST_URI'] != "/login"): ?>
                            <li><a href="/login">Login</a></li>
                        <?php endif ?>
                    <?php endif; ?>
            </ul>
        </div>
                        </div>
    <div id="loader-container">
        <p aria-busy="true"></p>
    </div>
    <main id="main-content">