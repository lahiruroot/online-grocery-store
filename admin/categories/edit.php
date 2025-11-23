<?php

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../classes/Category.php';

if (!isAdmin()) {
    redirect('admin/index.php');
}

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed.");
}

$category = new Category();

$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($categoryId <= 0) {
    redirect('admin/categories/manage.php');
}

// Get category
$categoryData = $category->getById($categoryId);

if (!$categoryData) {
    redirect('admin/categories/manage.php');
}

$error = '';
$success = '';

// Handle category update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => sanitize($_POST['name'] ?? ''),
        'description' => sanitize($_POST['description'] ?? ''),
        'status' => sanitize($_POST['status'] ?? 'active'),
        'sort_order' => (int)($_POST['sort_order'] ?? 0)
    ];

    // Validation
    if (empty($data['name'])) {
        $error = 'Category name is required';
    } else {
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Delete old image if exists
            if (!empty($categoryData['image'])) {
                deleteFile($categoryData['image']);
            }
            
            $uploadResult = uploadFile($_FILES['image']);
            if ($uploadResult['success']) {
                $data['image'] = $uploadResult['filename'];
            } else {
                $error = 'Image upload failed: ' . $uploadResult['error'];
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Handle upload errors
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            $error = 'Upload error: ' . ($uploadErrors[$_FILES['image']['error']] ?? 'Unknown error');
        }
        
        if (empty($error)) {
            $result = $category->update($categoryId, $data);
            if ($result['success']) {
                setFlashMessage('success', 'Category updated successfully!');
                redirect('admin/categories/manage.php');
            } else {
                $error = $result['error'] ?? 'Failed to update category';
            }
        }
    }
}

$page_title = 'Edit Category';
$extra_css = 'admin-add-category.css';

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="admin-category-form">
    <div class="admin-category-form__header">
        <h1 class="admin-category-form__title">Edit Category</h1>
        <p class="admin-category-form__subtitle">Update the category details below</p>
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
                        value="<?php echo e($categoryData['name'] ?? ''); ?>" 
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
                    ><?php echo e($categoryData['description'] ?? ''); ?></textarea>
                </div>

                <div class="admin-form__group admin-form__image-upload">
                    <label class="admin-form__label">Category Image</label>
                    <?php if (!empty($categoryData['image'])): ?>
                        <div style="margin-bottom: 1rem;">
                            <p style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Current Image:</p>
                            <img src="<?php echo SITE_URL; ?>public/uploads/<?php echo e($categoryData['image']); ?>" 
                                 alt="<?php echo e($categoryData['name']); ?>" 
                                 id="currentImage"
                                 style="max-width: 300px; max-height: 200px; border: 2px solid #e5e7eb; border-radius: 12px; padding: 0.5rem; background: #f9fafb; object-fit: contain;">
                        </div>
                    <?php endif; ?>
                    <div class="admin-form__image-preview" id="imagePreview" onclick="document.getElementById('image').click()">
                        <div class="admin-form__image-placeholder">
                            <div class="admin-form__image-placeholder-icon">📷</div>
                            <div class="admin-form__image-placeholder-text"><?php echo !empty($categoryData['image']) ? 'Click to upload new image or drag and drop' : 'Click to upload or drag and drop'; ?></div>
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
                        <small style="color: #6b7280; font-size: 0.875rem; display: block; margin-top: 0.5rem;">
                            Leave empty to keep current image. Allowed formats: JPG, PNG, GIF, WebP. Max size: 5MB
                        </small>
                    </div>
                </div>

                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label for="status" class="admin-form__label">Status</label>
                        <select id="status" name="status" class="admin-form__select">
                            <option value="active" <?php echo ($categoryData['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($categoryData['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="admin-form__group">
                        <label for="sort_order" class="admin-form__label">Sort Order</label>
                        <input 
                            type="number" 
                            id="sort_order" 
                            name="sort_order" 
                            value="<?php echo e($categoryData['sort_order'] ?? 0); ?>" 
                            min="0" 
                            class="admin-form__input"
                            placeholder="0"
                        >
                    </div>
                </div>
            </div>

            <div class="admin-form__actions">
                <button type="submit" class="admin-btn admin-btn--primary" id="submitBtn">
                    <span>Update Category</span>
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
    const currentImage = document.getElementById('currentImage');
    const form = document.getElementById('categoryForm');
    const submitBtn = document.getElementById('submitBtn');

    // Show current image in preview if exists
    if (currentImage) {
        previewImg.src = currentImage.src;
        imagePreview.classList.add('has-image');
    }

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
                    if (currentImage) {
                        currentImage.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            } else {
                if (!currentImage) {
                    previewImg.src = '';
                    imagePreview.classList.remove('has-image');
                }
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
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
