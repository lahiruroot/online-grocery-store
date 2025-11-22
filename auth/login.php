<?php

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../classes/User.php';

// Check if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$login_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $login_email = $email;

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        $user = new User();
        $result = $user->login($email, $password);
        
        if ($result['success']) {
            setFlashMessage('success', 'Welcome back, ' . $_SESSION['user_name'] . '!');
            redirect('user/dashboard.php');
        } else {
            $error = $result['error'];
        }
    }
                    }
                    
$page_title = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?php echo SITE_URL; ?>">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="public/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h1>Welcome Back</h1>
            <p>Login to your <?php echo SITE_NAME; ?> account</p>

            <?php 
            $flash = getFlashMessage();
            if ($flash): 
            ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <?php echo e($flash['message']); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form" id="loginForm">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?php echo e($login_email); ?>"
                        required 
                        autocomplete="email"
                        placeholder="Enter your email address">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        autocomplete="current-password"
                        placeholder="Enter your password">
                </div>

                <button type="submit" class="btn btn-primary" id="loginBtn">
                    Login
                </button>
            </form>

            <div class="auth-link">
                <p>Don't have an account? <a href="auth/register.php">Register here</a></p>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            btn.disabled = true;
            btn.textContent = 'Logging in...';
        });
    </script>
</body>
</html>
