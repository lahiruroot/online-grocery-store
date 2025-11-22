<?php
/**
 * User Profile Page
 * Edit user profile
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/User.php';

if (!isLoggedIn()) {
    redirect('auth/login.php');
}

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed.");
}

$user = new User();
$userId = getCurrentUserId();
$userData = $user->getById($userId);

$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $data = [
        'name' => sanitize($_POST['name'] ?? ''),
        'phone' => sanitize($_POST['phone'] ?? ''),
        'address' => sanitize($_POST['address'] ?? ''),
        'city' => sanitize($_POST['city'] ?? ''),
        'state' => sanitize($_POST['state'] ?? ''),
        'zip' => sanitize($_POST['zip'] ?? ''),
        'country' => sanitize($_POST['country'] ?? 'USA')
    ];
    
    $result = $user->updateProfile($userId, $data);
    if ($result['success']) {
        $success = 'Profile updated successfully!';
        $userData = $user->getById($userId); // Refresh data
    } else {
        $error = $result['error'];
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword)) {
        $error = 'All password fields are required';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New passwords do not match';
    } elseif (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
        $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
    } else {
        $result = $user->changePassword($userId, $currentPassword, $newPassword);
        if ($result['success']) {
            $success = 'Password changed successfully!';
        } else {
            $error = $result['error'];
        }
    }
}

$page_title = 'My Profile';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4" style="max-width: 800px;">
    <h1>My Profile</h1>

    <?php if ($error): ?>
        <div class="alert alert-error mt-4"><?php echo e($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success mt-4"><?php echo e($success); ?></div>
    <?php endif; ?>

    <!-- Profile Information -->
    <div class="card mt-4">
        <div class="card-body">
            <h2>Profile Information</h2>
            <form method="POST">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Full Name</label>
                    <input type="text" name="name" value="<?php echo e($userData['name'] ?? ''); ?>" required class="form-control">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Email</label>
                    <input type="email" value="<?php echo e($userData['email'] ?? ''); ?>" disabled class="form-control" style="background: #f3f4f6;">
                    <small style="color: #6b7280;">Email cannot be changed</small>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Phone</label>
                    <input type="tel" name="phone" value="<?php echo e($userData['phone'] ?? ''); ?>" class="form-control">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Address</label>
                    <textarea name="address" rows="3" class="form-control"><?php echo e($userData['address'] ?? ''); ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">City</label>
                        <input type="text" name="city" value="<?php echo e($userData['city'] ?? ''); ?>" class="form-control">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">State</label>
                        <input type="text" name="state" value="<?php echo e($userData['state'] ?? ''); ?>" class="form-control">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">ZIP</label>
                        <input type="text" name="zip" value="<?php echo e($userData['zip'] ?? ''); ?>" class="form-control">
                    </div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Country</label>
                    <input type="text" name="country" value="<?php echo e($userData['country'] ?? 'USA'); ?>" class="form-control">
                </div>

                <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>

    <!-- Change Password -->
    <div class="card mt-4">
        <div class="card-body">
            <h2>Change Password</h2>
            <form method="POST">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Current Password</label>
                    <input type="password" name="current_password" required class="form-control">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">New Password</label>
                    <input type="password" name="new_password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" class="form-control">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Confirm New Password</label>
                    <input type="password" name="confirm_password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" class="form-control">
                </div>

                <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
