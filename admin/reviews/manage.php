<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../classes/Review.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('auth/login.php');
}

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed.");
}

$review = new Review();

$page_title = 'Manage Reviews';

// Handle delete - MUST be before any output
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $reviewId = (int)$_GET['delete'];
    
    if ($reviewId > 0) {
        $result = $review->delete($reviewId);
        
        if ($result['success']) {
            setFlashMessage('success', 'Review deleted successfully!');
        } else {
            setFlashMessage('error', $result['error'] ?? 'Failed to delete review');
        }
    } else {
        setFlashMessage('error', 'Invalid review ID');
    }
    
    redirect('admin/reviews/manage.php');
    exit(); // Ensure script stops
}

// Handle approval/rejection - MUST be before any output
if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $review_id = (int)$_GET['approve'];
    
    if ($review_id > 0) {
        $result = $review->updateStatus($review_id, 'approved');
        if ($result['success']) {
            setFlashMessage('success', 'Review approved successfully!');
        } else {
            setFlashMessage('error', $result['error'] ?? 'Failed to approve review');
        }
    } else {
        setFlashMessage('error', 'Invalid review ID');
    }
    
    redirect('admin/reviews/manage.php');
    exit(); // Ensure script stops
}

if (isset($_GET['reject']) && is_numeric($_GET['reject'])) {
    $review_id = (int)$_GET['reject'];
    
    if ($review_id > 0) {
        $result = $review->updateStatus($review_id, 'rejected');
        if ($result['success']) {
            setFlashMessage('success', 'Review rejected successfully!');
        } else {
            setFlashMessage('error', $result['error'] ?? 'Failed to reject review');
        }
    } else {
        setFlashMessage('error', 'Invalid review ID');
    }
    
    redirect('admin/reviews/manage.php');
    exit(); // Ensure script stops
}

// Get all reviews (pending and approved)
$result = $review->getAll(['status' => 'all'], 1, 100);
$reviews = $result['reviews'] ?? [];

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
    <h1>Manage Reviews</h1>

    <?php 
    $flash = getFlashMessage();
    if ($flash): 
    ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo e($flash['message']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($reviews)): ?>
        <div class="alert alert-info">
            <p>No reviews found.</p>
        </div>
    <?php else: ?>
    <table style="width: 100%; border-collapse: collapse;">
        <thead style="background-color: #f9fafb; border-bottom: 2px solid #e5e7eb;">
            <tr>
                <th style="padding: 1rem; text-align: left;">Customer</th>
                <th style="padding: 1rem; text-align: left;">Product</th>
                <th style="padding: 1rem; text-align: center;">Rating</th>
                <th style="padding: 1rem; text-align: left;">Comment</th>
                <th style="padding: 1rem; text-align: center;">Status</th>
                <th style="padding: 1rem; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reviews as $reviewData): ?>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 1rem;"><?php echo e($reviewData['user_name'] ?? 'N/A'); ?></td>
                    <td style="padding: 1rem;"><?php echo e($reviewData['product_name'] ?? 'N/A'); ?></td>
                    <td style="padding: 1rem; text-align: center;"><?php echo str_repeat('★', (int)$reviewData['rating']); ?></td>
                    <td style="padding: 1rem;"><?php echo e(substr($reviewData['comment'] ?? '', 0, 50)); ?><?php echo strlen($reviewData['comment'] ?? '') > 50 ? '...' : ''; ?></td>
                    <td style="padding: 1rem; text-align: center;">
                        <span style="padding: 0.25rem 0.75rem; border-radius: 0.25rem; background: <?php 
                            echo $reviewData['status'] === 'approved' ? '#dcfce7' : ($reviewData['status'] === 'rejected' ? '#fee2e2' : '#fef3c7'); 
                        ?>;">
                            <?php echo ucfirst($reviewData['status'] ?? 'pending'); ?>
                        </span>
                    </td>
                    <td style="padding: 1rem; text-align: center;">
                        <?php if ($reviewData['status'] === 'pending'): ?>
                            <a href="<?php echo SITE_URL; ?>admin/reviews/manage.php?approve=<?php echo $reviewData['id']; ?>" class="btn btn-primary btn-small">Approve</a>
                            <a href="<?php echo SITE_URL; ?>admin/reviews/manage.php?reject=<?php echo $reviewData['id']; ?>" class="btn btn-secondary btn-small">Reject</a>
                        <?php endif; ?>
                        <a href="<?php echo SITE_URL; ?>admin/reviews/manage.php?delete=<?php echo $reviewData['id']; ?>" 
                           onclick="return confirm('Are you sure you want to delete this review?');" 
                           class="btn btn-small" style="background: #ef4444; color: white;">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
