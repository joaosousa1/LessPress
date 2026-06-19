<?php
// app/pages/admin_board.php
$pageTitle = "Administration Control"; // Dynamic title override works perfectly!

global $db;
global $appPath;

// Model User
require_once $appPath . '/app/models/User.php';

// Get all users
$allUsers = User::all($db);

?>
<div class="container">
    <h1>⚙️ System Administration Board</h1>
    <p>Welcome back, Chief Admin. You are cleared to oversee infrastructure operations here.</p>
    <div class="overflow-auto">
        <table class="striped">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Full Name</th>
                    <th scope="col">Email Address</th>
                    <th scope="col">Access</th>
                    <th scope="col">Account Status</th>
                    <th scope="col">Created At</th>
                    <th scope="col">Updated At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($allUsers)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">No registered users found in the system.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($allUsers as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user->id); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($user->full_name ?? 'N/A'); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($user->email); ?></td>
                            <td>
                                <code><?php echo htmlspecialchars($user->role_id); ?></code>
                            </td>
                            <td>
                                <?php if ($user->status === 'active'): ?>
                                    <span style="color: green;">● Active</span>
                                <?php else: ?>
                                    <span style="color: orange;">● <?php echo ucfirst($user->status); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($user->created_at))); ?></small>
                            </td>
                            <td>
                                <small><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($user->updated_at))); ?></small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>