<?php

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../classes/Category.php';

if (!isAdmin()) {
    redirect('index.php');
}

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed.");
}

$category = new Category();

$error = '';
$success = '';
$formData = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'name' => sanitize($_POST['name'] ?? ''),
        'description' => sanitize($_POST['description'] ?? ''),
        'status' => sanitize($_POST['status'] ?? 'active'),
        'sort_order' => (int)($_POST['sort_order'] ?? 0)
    ];
    
    // Validation
    if (empty($formData['name'])) {
        $error = 'Category name is required';
    } else {
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadFile($_FILES['image']);
            if ($uploadResult['success']) {
                $formData['image'] = $uploadResult['filename'];
            } else {
                $error = $uploadResult['error'];
            }
        }
        
        if (empty($error)) {
            $result = $category->create($formData);
            if ($result['success']) {
            $success = 'Category created successfully!';
                $formData = [];
        } else {
                $error = $result['error'];
            }
        }
    }
}

$page_title = 'Add Category';
$extra_css = 'admin-add-category.css';

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="admin-category-form">
    <div class="admin-category-form__header">
        <h1 class="admin-category-form__title">Add New Category</h1>
        <p class="admin-category-form__subtitle">Fill in the details below to create a new category</p>
    </div>

    <div class="admin-category-form__card">
        <?php if ($error): ?>
            <div class="admin-alert admin-alert--error">
                <span class="admin-alert__icon">⚠️</span>
                <span><?php echo e($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="admin-alert admin-alert--success">
                <span class="admin-alert__icon">✓</span>
                <span><?php echo e($success); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="admin-category-form__form" id="categoryForm">
            <div class="admin-form__section">
                <div class="admin-form__group">
                    <label for="name" class="admin-form__label admin-form__label--required">Category Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="<?php echo e($formData['name'] ?? ''); ?>" 
                        required 
                        class="admin-form__input"
                        placeholder="Enter category name"
                    >
                </div>

                <div class="admin-form__group">
                    <label for="description" class="admin-form__label">Description</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="4" 
                        class="admin-form__textarea"
                        placeholder="Enter category description (optional)"
                    ><?php echo e($formData['description'] ?? ''); ?></textarea>
                </div>

                <div class="admin-form__group admin-form__image-upload">
                    <label for="image" class="admin-form__label">Category Image</label>
                    <div class="admin-form__image-preview" id="imagePreview" onclick="document.getElementById('image').click()">
                        <div class="admin-form__image-placeholder">
                            <div class="admin-form__image-placeholder-icon">📷</div>
                            <div class="admin-form__image-placeholder-text">Click to upload or drag and drop</div>
                        </div>
                        <img id="previewImg" src="" alt="Preview">
                    </div>
                    <div class="admin-form__file-input">
                        <input 
                            type="file" 
                            id="image" 
                            name="image" 
                            accept="image/*" 
                            class="admin-form__input"
                        >
                    </div>
                </div>

                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label for="status" class="admin-form__label">Status</label>
                        <select id="status" name="status" class="admin-form__select">
                            <option value="active" <?php echo ($formData['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($formData['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="admin-form__group">
                        <label for="sort_order" class="admin-form__label">Sort Order</label>
                        <input 
                            type="number" 
                            id="sort_order" 
                            name="sort_order" 
                            value="<?php echo e($formData['sort_order'] ?? 0); ?>" 
                            min="0" 
                            class="admin-form__input"
                            placeholder="0"
                        >
                    </div>
                </div>
            </div>

            <div class="admin-form__actions">
                <button type="submit" class="admin-btn admin-btn--primary" id="submitBtn">
                    <span>Create Category</span>
                </button>
                <a href="<?php echo SITE_URL; ?>admin/categories/manage.php" class="admin-btn admin-btn--secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const form = document.getElementById('categoryForm');
    const submitBtn = document.getElementById('submitBtn');

    // Image preview functionality
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert('Please select a valid image file');
                    this.value = '';
                    return;
                }

                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Image size should be less than 5MB');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.classList.add('has-image');
                };
                reader.readAsDataURL(file);
            } else {
                previewImg.src = '';
                imagePreview.classList.remove('has-image');
            }
        });

        // Drag and drop functionality
        imagePreview.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.style.borderColor = '#10b981';
            this.style.background = '#f0fdf4';
        });

        imagePreview.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.style.borderColor = '#e5e7eb';
            this.style.background = '#f9fafb';
        });

        imagePreview.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.style.borderColor = '#e5e7eb';
            this.style.background = '#f9fafb';

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                imageInput.files = files;
                imageInput.dispatchEvent(new Event('change'));
            }
        });
    }

    // Form submission handling
    if (form) {
        form.addEventListener('submit', function(e) {
            // Show loading state
            submitBtn.classList.add('admin-btn--loading');
            submitBtn.disabled = true;
        });
    }

    // Auto-hide success message after 5 seconds
    const successAlert = document.querySelector('.admin-alert--success');
    if (successAlert) {
        setTimeout(function() {
            successAlert.style.opacity = '0';
            successAlert.style.transform = 'translateY(-10px)';
            setTimeout(function() {
                successAlert.style.display = 'none';
            }, 300);
        }, 5000);
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
