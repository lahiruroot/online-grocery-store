<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../classes/User.php';

// Check if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    
    $formData = ['name' => $name, 'email' => $email, 'phone' => $phone, 'address' => $address];

    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Name, email, and password are required';
    } elseif (!validateEmail($email)) {
        $error = 'Invalid email address';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        $user = new User();
        $result = $user->register($name, $email, $password, $phone, $address);

        if ($result['success']) {
            setFlashMessage('success', 'Registration successful! Please login.');
            redirect('auth/login.php');
        } else {
            $error = $result['error'];
        }
    }
}

$page_title = 'Register';
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
            <h1>Create Account</h1>
            <p>Join <?php echo SITE_NAME; ?> today</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo e($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo e($success); ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form" id="registerForm">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="<?php echo e($formData['name'] ?? ''); ?>"
                        required 
                        autocomplete="name"
                        placeholder="Enter your full name">
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?php echo e($formData['email'] ?? ''); ?>"
                        required 
                        autocomplete="email"
                        placeholder="Enter your email address">
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number (Optional)</label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        value="<?php echo e($formData['phone'] ?? ''); ?>"
                        autocomplete="tel"
                        placeholder="Enter your phone number">
                </div>

                <div class="form-group">
                    <label for="address">Address (Optional)</label>
                    <textarea 
                        id="address" 
                        name="address" 
                        rows="3"
                        placeholder="Enter your address"><?php echo e($formData['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        autocomplete="new-password"
                        minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                        placeholder="Enter your password (min <?php echo PASSWORD_MIN_LENGTH; ?> characters)">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required 
                        autocomplete="new-password"
                        placeholder="Confirm your password">
                </div>

                <button type="submit" class="btn btn-primary" id="registerBtn">
                    Register
                </button>
            </form>

            <div class="auth-link">
                <p>Already have an account? <a href="auth/login.php">Login here</a></p>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return false;
            }
            
            const btn = document.getElementById('registerBtn');
            btn.disabled = true;
            btn.textContent = 'Registering...';
        });
    </script>
</body>
</html>
