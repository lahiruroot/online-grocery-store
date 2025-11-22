<?php
require_once '../config/db.php';
require_once '../config/constants.php';
require_once '../config/functions.php';

$page_title = 'Contact Us';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error = 'All fields are required';
    } elseif (!validateEmail($email)) {
        $error = 'Invalid email format';
    } else {
        // In a real application, you would save this to a database or send an email
        $success = 'Thank you for your message. We will get back to you soon!';
    }
}

require_once '../includes/header.php';
?>

<div class="container mt-4">
    <div style="max-width: 600px; margin: 0 auto;">
        <h1>Contact Us</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label>Name*</label>
                        <input type="text" name="name" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Email*</label>
                        <input type="email" name="email" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Message*</label>
                        <textarea name="message" required class="form-control" style="min-height: 150px;"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
