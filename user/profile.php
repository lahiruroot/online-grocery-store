<?php
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (empty(trim($_POST['name'] ?? ''))) {
        $error = 'Full name is required';
    } else {
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
            $error = $result['error'] ?? 'Failed to update profile. Please try again.';
        }
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
$extra_css = 'profile.css';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="profile-container">
    <div class="profile-header">
        <h1>My Profile</h1>
        <p>Manage your account information and preferences</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <span class="alert-icon">⚠️</span>
            <span><?php echo e($error); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <span class="alert-icon">✓</span>
            <span><?php echo e($success); ?></span>
        </div>
    <?php endif; ?>

    <!-- Profile Information -->
    <div class="profile-card" id="profileCard">
        <div class="profile-card-header">
           
            <h2 class="profile-card-title">Profile Information</h2>
        </div>
        <form method="POST" class="profile-form" id="profileForm">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo e($userData['name'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" value="<?php echo e($userData['email'] ?? ''); ?>" disabled>
                <span class="form-help-text">Email cannot be changed</span>
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" value="<?php echo e($userData['phone'] ?? ''); ?>" placeholder="Enter your phone number">
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3" placeholder="Enter your street address"><?php echo e($userData['address'] ?? ''); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="<?php echo e($userData['city'] ?? ''); ?>" placeholder="City">
                </div>
                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state" value="<?php echo e($userData['state'] ?? ''); ?>" placeholder="State">
                </div>
                <div class="form-group">
                    <label for="zip">ZIP Code</label>
                    <input type="text" id="zip" name="zip" value="<?php echo e($userData['zip'] ?? ''); ?>" placeholder="ZIP">
                </div>
            </div>

            <div class="form-group">
                <label for="country">Country</label>
                <input type="text" id="country" name="country" value="<?php echo e($userData['country'] ?? 'USA'); ?>" placeholder="Country">
            </div>

            <div class="btn-group">
                <button type="submit" name="update_profile" class="btn-primary" id="updateProfileBtn">Update Profile</button>
            </div>
        </form>
    </div>

    <!-- Change Password -->
    <div class="profile-card">
        <div class="profile-card-header">
            <h2 class="profile-card-title">Change Password</h2>
        </div>
        <form method="POST" class="profile-form" id="passwordForm">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <div class="password-input-wrapper">
                    <input type="password" id="current_password" name="current_password" required>
                    <button type="button" class="password-toggle" data-target="current_password">👁️</button>
                </div>
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <div class="password-input-wrapper">
                    <input type="password" id="new_password" name="new_password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                    <button type="button" class="password-toggle" data-target="new_password">👁️</button>
                </div>
                <div class="password-strength">
                    <div class="password-strength-bar" id="passwordStrengthBar"></div>
                </div>
                <div class="password-requirements" id="passwordRequirements">
                    <ul>
                        <li id="req-length">At least <?php echo PASSWORD_MIN_LENGTH; ?> characters</li>
                        <li id="req-uppercase">One uppercase letter</li>
                        <li id="req-lowercase">One lowercase letter</li>
                        <li id="req-number">One number</li>
                    </ul>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <div class="password-input-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                    <button type="button" class="password-toggle" data-target="confirm_password">👁️</button>
                </div>
                <span class="form-help-text" id="passwordMatch"></span>
            </div>

            <div class="btn-group">
                <button type="submit" name="change_password" class="btn-primary" id="changePasswordBtn">Change Password</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (input.type === 'password') {
                input.type = 'text';
                this.textContent = '🙈';
            } else {
                input.type = 'password';
                this.textContent = '👁️';
            }
        });
    });

    // Password strength checker
    const newPasswordInput = document.getElementById('new_password');
    const strengthBar = document.getElementById('passwordStrengthBar');
    const passwordRequirements = {
        length: document.getElementById('req-length'),
        uppercase: document.getElementById('req-uppercase'),
        lowercase: document.getElementById('req-lowercase'),
        number: document.getElementById('req-number')
    };

    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            checkPasswordStrength(password);
        });
    }

    function checkPasswordStrength(password) {
        const minLength = <?php echo PASSWORD_MIN_LENGTH; ?>;
        let strength = 0;
        let validCount = 0;

        // Check length
        if (password.length >= minLength) {
            passwordRequirements.length.classList.add('valid');
            strength += 25;
            validCount++;
        } else {
            passwordRequirements.length.classList.remove('valid');
        }

        // Check uppercase
        if (/[A-Z]/.test(password)) {
            passwordRequirements.uppercase.classList.add('valid');
            strength += 25;
            validCount++;
        } else {
            passwordRequirements.uppercase.classList.remove('valid');
        }

        // Check lowercase
        if (/[a-z]/.test(password)) {
            passwordRequirements.lowercase.classList.add('valid');
            strength += 25;
            validCount++;
        } else {
            passwordRequirements.lowercase.classList.remove('valid');
        }

        // Check number
        if (/[0-9]/.test(password)) {
            passwordRequirements.number.classList.add('valid');
            strength += 25;
            validCount++;
        } else {
            passwordRequirements.number.classList.remove('valid');
        }

        // Update strength bar
        strengthBar.className = 'password-strength-bar';
        if (validCount === 0) {
            strengthBar.style.width = '0%';
        } else if (validCount <= 2) {
            strengthBar.classList.add('weak');
        } else if (validCount === 3) {
            strengthBar.classList.add('medium');
        } else {
            strengthBar.classList.add('strong');
        }
    }

    // Password match checker
    const confirmPasswordInput = document.getElementById('confirm_password');
    if (confirmPasswordInput && newPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            const newPassword = newPasswordInput.value;
            const confirmPassword = this.value;
            const matchText = document.getElementById('passwordMatch');

            if (confirmPassword.length === 0) {
                matchText.textContent = '';
                matchText.style.color = '';
            } else if (newPassword === confirmPassword) {
                matchText.textContent = '✓ Passwords match';
                matchText.style.color = 'var(--color-success)';
            } else {
                matchText.textContent = '✗ Passwords do not match';
                matchText.style.color = 'var(--color-danger)';
            }
        });
    }

    // Form submission handlers
    const profileForm = document.getElementById('profileForm');
    const passwordForm = document.getElementById('passwordForm');
    const updateProfileBtn = document.getElementById('updateProfileBtn');
    const changePasswordBtn = document.getElementById('changePasswordBtn');

    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            // Validate name field
            const nameInput = document.getElementById('name');
            if (!nameInput.value.trim()) {
                e.preventDefault();
                alert('Full name is required');
                nameInput.focus();
                return false;
            }
            
            // Allow form to submit normally
            updateProfileBtn.classList.add('btn-loading');
            updateProfileBtn.disabled = true;
        });
    }

    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }

            changePasswordBtn.classList.add('btn-loading');
            changePasswordBtn.disabled = true;
        });
    }

    // Success animation
    <?php if ($success): ?>
    const profileCard = document.getElementById('profileCard');
    if (profileCard) {
        profileCard.classList.add('success-animation');
        setTimeout(() => {
            profileCard.classList.remove('success-animation');
        }, 500);
    }
    <?php endif; ?>

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease-out';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
