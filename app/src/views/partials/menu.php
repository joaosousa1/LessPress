<div class="container">
    <div id="menu">
        <ul>
            <?php if (isset($_SESSION['user_id']) && isset($_SESSION['role_id'])): ?>
    
                <li><a href="/user/settings">Settings</a></li>
    
                <?php if ($_SESSION['role_id'] === Role::ADMIN || $_SESSION['role_id'] === Role::EDITOR): ?>
                    <li><a href="/editor">Editor</a></li>
                    <li><a href="/upload">Uploads</a></li>
                <?php endif; ?>
    
                <?php if ($_SESSION['role_id'] === Role::ADMIN): ?>
                    <li><a href="/admin/board" style="color: orange;">Admin Board</a></li>
                    <li><a href="/create/user">[+] New User</a></li>
                <?php endif; ?>
    
                <?php $userEmail = $_SESSION['email'] ?? 'Profile'; ?>
    
            <?php endif; ?>
        </ul>
    </div>
</div>