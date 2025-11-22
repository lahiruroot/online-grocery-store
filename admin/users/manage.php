<?php
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/constants.php';
require_once '../../config/functions.php';
require_once '../../config/db.php';

// Get database connection
$conn = getDbConnection();

// Validate connection
if (!$conn || !($conn instanceof mysqli)) {
    die("Database connection failed. Please check your database configuration.");
}

if (!isAdmin()) {
    redirect('../index.php');
}

$page_title = 'Manage Users';

// Get users
$sql = "SELECT * FROM users ORDER BY created_at DESC";
$users_result = $conn->query($sql);

require_once '../../includes/header.php';
?>

<div class="container mt-4">
    <h1>Manage Users</h1>

    <table style="width: 100%; border-collapse: collapse;">
        <thead style="background-color: #f9fafb; border-bottom: 2px solid #e5e7eb;">
            <tr>
                <th style="padding: 1rem; text-align: left;">Name</th>
                <th style="padding: 1rem; text-align: left;">Email</th>
                <th style="padding: 1rem; text-align: left;">Phone</th>
                <th style="padding: 1rem; text-align: left;">Role</th>
                <th style="padding: 1rem; text-align: left;">Joined</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $users_result->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 1rem;"><?php echo htmlspecialchars($user['name']); ?></td>
                    <td style="padding: 1rem;"><?php echo htmlspecialchars($user['email']); ?></td>
                    <td style="padding: 1rem;"><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                    <td style="padding: 1rem;">
                        <span style="background-color: <?php echo $user['role'] === 'admin' ? '#dbeafe' : '#dcfce7'; ?>; color: <?php echo $user['role'] === 'admin' ? '#0c4a6e' : '#15803d'; ?>; padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-size: 0.875rem;">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </td>
                    <td style="padding: 1rem;"><?php echo formatDate($user['created_at']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../includes/footer.php'; ?>
