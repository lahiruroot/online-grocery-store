<?php
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/classes/User.php';

// Check if already logged in as admin
if (isLoggedIn() && isAdmin()) {
    die("You are already logged in as admin. <a href='index.php'>Go to Home</a>");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif (!validateEmail($email)) {
        $error = 'Invalid email address';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            $user = new User();
            
            // Check if email exists
            if ($user->emailExists($email)) {
                $error = 'Email already registered';
            } else {
                // Register user
                $result = $user->register($name, $email, $password);
                
                if ($result['success']) {
                    // Update to admin role
                    $stmt = $db->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
                    $stmt->execute([$result['user_id']]);
                    
                    $success = 'Admin account created successfully! <a href="auth/login.php">Login here</a>';
                } else {
                    $error = $result['error'];
                }
            }
        } catch (Exception $e) {
            $error = 'Failed to create admin account: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?php echo SITE_URL; ?>">
    <title>Create Admin Account</title>
    <link rel="stylesheet" href="public/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h1>Create Admin Account</h1>
            <p>Create the first admin account for <?php echo SITE_NAME; ?></p>
        
        <?php if ($error): ?>
                <div class="alert alert-error"><?php echo e($error); ?></div>
        <?php endif; ?>
        
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
            <form method="POST" class="auth-form">
            <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required autocomplete="name" placeholder="Enter your full name">
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required autocomplete="email" placeholder="Enter your email address">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" autocomplete="new-password" placeholder="Enter password (min <?php echo PASSWORD_MIN_LENGTH; ?> characters)">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" placeholder="Confirm your password">
            </div>
            
                <button type="submit" class="btn btn-primary">Create Admin Account</button>
        </form>
        
            <div class="auth-link">
                <p><a href="index.php">Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>
