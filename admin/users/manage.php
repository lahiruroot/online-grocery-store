<?php

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../classes/User.php';

if (!isAdmin()) {
    redirect('admin/index.php');
}

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed.");
}

$user = new User();

// Handle delete - MUST be before any output
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    
    if ($deleteId > 0) {
        // Prevent deleting yourself
        if ($deleteId === getCurrentUserId()) {
            setFlashMessage('error', 'You cannot delete your own account');
        } else {
            $result = $user->delete($deleteId);
            if ($result['success']) {
                setFlashMessage('success', 'User deleted successfully!');
            } else {
                setFlashMessage('error', $result['error'] ?? 'Failed to delete user');
            }
        }
    } else {
        setFlashMessage('error', 'Invalid user ID');
    }
    
    redirect('admin/users/manage.php');
    exit(); // Ensure script stops
}

// Get users with pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$result = $user->getAll($page, ITEMS_PER_PAGE);
$users = $result['users'];
$totalPages = $result['pages'];

$page_title = 'Manage Users';

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Manage Users</h1>
        <div>
            <span style="color: #6b7280;">Total Users: <?php echo $result['total']; ?></span>
        </div>
    </div>

    <?php 
    $flash = getFlashMessage();
    if ($flash): 
    ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo e($flash['message']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($users)): ?>
        <div style="text-align: center; padding: 3rem; background: white; border-radius: 8px;">
            <p style="color: #6b7280; font-size: 1.1rem;">No users found.</p>
        </div>
    <?php else: ?>
        <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                    <tr>
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">ID</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">Name</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">Email</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">Phone</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">Role</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">Joined</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $userData): ?>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 1rem; color: #6b7280;"><?php echo e($userData['id']); ?></td>
                            <td style="padding: 1rem;"><?php echo e($userData['name']); ?></td>
                            <td style="padding: 1rem;"><?php echo e($userData['email']); ?></td>
                            <td style="padding: 1rem; color: #6b7280;"><?php echo e($userData['phone'] ?? 'N/A'); ?></td>
                            <td style="padding: 1rem;">
                                <span style="background-color: <?php echo $userData['role'] === 'admin' ? '#dbeafe' : '#dcfce7'; ?>; color: <?php echo $userData['role'] === 'admin' ? '#0c4a6e' : '#15803d'; ?>; padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 500;">
                                    <?php echo ucfirst($userData['role']); ?>
                                </span>
                            </td>
                            <td style="padding: 1rem; color: #6b7280;"><?php echo formatDate($userData['created_at']); ?></td>
                            <td style="padding: 1rem;">
                                <?php if ($userData['id'] !== getCurrentUserId()): ?>
                                    <a href="<?php echo SITE_URL; ?>admin/users/manage.php?delete=<?php echo $userData['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this user?');"
                                       style="color: #ef4444; text-decoration: none; font-size: 0.875rem; padding: 0.25rem 0.5rem; border-radius: 4px; transition: background 0.2s;"
                                       onmouseover="this.style.background='#fee2e2'" 
                                       onmouseout="this.style.background='transparent'">
                                        Delete
                                    </a>
                                <?php else: ?>
                                    <span style="color: #9ca3af; font-size: 0.875rem;">Current User</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div style="margin-top: 2rem; display: flex; justify-content: center; gap: 0.5rem;">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" style="padding: 0.5rem 1rem; background: white; border: 1px solid #e5e7eb; border-radius: 6px; text-decoration: none; color: var(--color-dark);">Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span style="padding: 0.5rem 1rem; background: var(--color-primary); color: white; border-radius: 6px; font-weight: 600;"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>" style="padding: 0.5rem 1rem; background: white; border: 1px solid #e5e7eb; border-radius: 6px; text-decoration: none; color: var(--color-dark);"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" style="padding: 0.5rem 1rem; background: white; border: 1px solid #e5e7eb; border-radius: 6px; text-decoration: none; color: var(--color-dark);">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
