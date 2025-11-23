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
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>public/css/auth.css">
    <style>
        /* Critical CSS fallback - ensures basic styling loads immediately */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; }
        .auth-container { width: 100%; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; background: #f9fafb; }
        .auth-box { background: #fff; border-radius: 1rem; box-shadow: 0 20px 60px rgba(0,0,0,0.15); max-width: 450px; width: 100%; padding: 2.5rem; }
    </style>
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
    
    <style>
        .form-group {
            position: relative;
        }
        .error-message {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        }
        .error-message.show {
            display: block;
        }
        .form-group input.invalid,
        .form-group textarea.invalid {
            border-color: #dc2626;
            background-color: #fef2f2;
        }
        .form-group input.valid,
        .form-group textarea.valid {
            border-color: #10b981;
            background-color: #f0fdf4;
        }
        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.875rem;
        }
        .password-strength-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.25rem;
            color: #6b7280;
        }
        .password-strength-item.valid {
            color: #10b981;
        }
        .password-strength-item.valid::before {
            content: "✓ ";
            color: #10b981;
            font-weight: bold;
            margin-right: 0.25rem;
        }
    </style>
    
    <script>
        (function() {
            'use strict';
            
            const PASSWORD_MIN_LENGTH = <?php echo PASSWORD_MIN_LENGTH; ?>;
            const form = document.getElementById('registerForm');
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const phoneInput = document.getElementById('phone');
            const addressInput = document.getElementById('address');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const registerBtn = document.getElementById('registerBtn');
            
            // Create error message elements
            function createErrorMessage(input) {
                let errorMsg = input.parentElement.querySelector('.error-message');
                if (!errorMsg) {
                    errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message';
                    input.parentElement.appendChild(errorMsg);
                }
                return errorMsg;
            }
            
            // Show error
            function showError(input, message) {
                const errorMsg = createErrorMessage(input);
                errorMsg.textContent = message;
                errorMsg.classList.add('show');
                input.classList.remove('valid');
                input.classList.add('invalid');
            }
            
            // Show success
            function showSuccess(input) {
                const errorMsg = createErrorMessage(input);
                errorMsg.classList.remove('show');
                input.classList.remove('invalid');
                input.classList.add('valid');
            }
            
            // Validate name
            function validateName() {
                const name = nameInput.value.trim();
                if (name === '') {
                    showError(nameInput, 'Full name is required');
                    return false;
                }
                if (name.length < 2) {
                    showError(nameInput, 'Name must be at least 2 characters');
                    return false;
                }
                if (name.length > 100) {
                    showError(nameInput, 'Name must be less than 100 characters');
                    return false;
                }
                if (!/^[a-zA-Z\s'-]+$/.test(name)) {
                    showError(nameInput, 'Name can only contain letters, spaces, hyphens, and apostrophes');
                    return false;
                }
                showSuccess(nameInput);
                return true;
            }
            
            // Validate email
            function validateEmail() {
                const email = emailInput.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (email === '') {
                    showError(emailInput, 'Email address is required');
                    return false;
                }
                if (!emailRegex.test(email)) {
                    showError(emailInput, 'Please enter a valid email address');
                    return false;
                }
                if (email.length > 255) {
                    showError(emailInput, 'Email address is too long');
                    return false;
                }
                showSuccess(emailInput);
                return true;
            }
            
            // Validate phone
            function validatePhone() {
                const phone = phoneInput.value.trim();
                if (phone === '') {
                    showSuccess(phoneInput);
                    return true; // Optional field
                }
                // Allow various phone formats: +1234567890, (123) 456-7890, 123-456-7890, etc.
                const phoneRegex = /^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/;
                if (!phoneRegex.test(phone)) {
                    showError(phoneInput, 'Please enter a valid phone number');
                    return false;
                }
                if (phone.length > 20) {
                    showError(phoneInput, 'Phone number is too long');
                    return false;
                }
                showSuccess(phoneInput);
                return true;
            }
            
            // Validate address
            function validateAddress() {
                const address = addressInput.value.trim();
                if (address === '') {
                    showSuccess(addressInput);
                    return true; // Optional field
                }
                if (address.length < 10) {
                    showError(addressInput, 'Address must be at least 10 characters');
                    return false;
                }
                if (address.length > 500) {
                    showError(addressInput, 'Address must be less than 500 characters');
                    return false;
                }
                showSuccess(addressInput);
                return true;
            }
            
            // Validate password
            function validatePassword() {
                const password = passwordInput.value;
                
                if (password === '') {
                    showError(passwordInput, 'Password is required');
                    return false;
                }
                if (password.length < PASSWORD_MIN_LENGTH) {
                    showError(passwordInput, `Password must be at least ${PASSWORD_MIN_LENGTH} characters`);
                    return false;
                }
                if (password.length > 128) {
                    showError(passwordInput, 'Password is too long');
                    return false;
                }
                
                // Check password strength
                let strengthIssues = [];
                if (!/[a-z]/.test(password)) {
                    strengthIssues.push('lowercase letter');
                }
                if (!/[A-Z]/.test(password)) {
                    strengthIssues.push('uppercase letter');
                }
                if (!/[0-9]/.test(password)) {
                    strengthIssues.push('number');
                }
                if (!/[^a-zA-Z0-9]/.test(password)) {
                    strengthIssues.push('special character');
                }
                
                if (strengthIssues.length > 0 && password.length < 12) {
                    showError(passwordInput, `For better security, include: ${strengthIssues.join(', ')}`);
                    return false;
                }
                
                showSuccess(passwordInput);
                return true;
            }
            
            // Validate confirm password
            function validateConfirmPassword() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (confirmPassword === '') {
                    showError(confirmPasswordInput, 'Please confirm your password');
                    return false;
                }
                if (password !== confirmPassword) {
                    showError(confirmPasswordInput, 'Passwords do not match');
                    return false;
                }
                showSuccess(confirmPasswordInput);
                return true;
            }
            
            // Real-time validation
            nameInput.addEventListener('blur', validateName);
            nameInput.addEventListener('input', function() {
                if (this.classList.contains('invalid')) {
                    validateName();
                }
            });
            
            emailInput.addEventListener('blur', validateEmail);
            emailInput.addEventListener('input', function() {
                if (this.classList.contains('invalid')) {
                    validateEmail();
                }
            });
            
            phoneInput.addEventListener('blur', validatePhone);
            phoneInput.addEventListener('input', function() {
                if (this.classList.contains('invalid')) {
                    validatePhone();
                }
            });
            
            addressInput.addEventListener('blur', validateAddress);
            addressInput.addEventListener('input', function() {
                if (this.classList.contains('invalid')) {
                    validateAddress();
                }
            });
            
            passwordInput.addEventListener('blur', validatePassword);
            passwordInput.addEventListener('input', function() {
                validatePassword();
                if (confirmPasswordInput.value !== '') {
                    validateConfirmPassword();
                }
            });
            
            confirmPasswordInput.addEventListener('blur', validateConfirmPassword);
            confirmPasswordInput.addEventListener('input', function() {
                if (passwordInput.value !== '') {
                    validateConfirmPassword();
                }
            });
            
            // Form submission validation
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate all fields
                const isNameValid = validateName();
                const isEmailValid = validateEmail();
                const isPhoneValid = validatePhone();
                const isAddressValid = validateAddress();
                const isPasswordValid = validatePassword();
                const isConfirmPasswordValid = validateConfirmPassword();
                
                if (isNameValid && isEmailValid && isPhoneValid && isAddressValid && isPasswordValid && isConfirmPasswordValid) {
                    // Disable button to prevent double submission
                    registerBtn.disabled = true;
                    registerBtn.textContent = 'Registering...';
                    
                    // Submit the form
                    form.submit();
                } else {
                    // Scroll to first error
                    const firstError = form.querySelector('.invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                }
            });
        })();
    </script>
</body>
</html>
