<?php
/**
 * Blogs Page
 * Display all blog posts
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Blog.php';

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed.");
}

$blog = new Blog();
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$result = $blog->getAll('published', $page, BLOGS_PER_PAGE);
$blogs = $result['blogs'];
$totalPages = $result['pages'];

$page_title = 'Blog';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <h1>Blog</h1>

    <?php if (empty($blogs)): ?>
        <div class="alert alert-info mt-4">
            <p>No blog posts available.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-3 mt-4">
            <?php foreach ($blogs as $post): ?>
            <div class="card">
                    <?php if (!empty($post['image'])): ?>
                        <img src="<?php echo SITE_URL; ?>public/uploads/<?php echo e($post['image']); ?>" 
                             alt="<?php echo e($post['title']); ?>" 
                             class="card-img">
                <?php endif; ?>
                <div class="card-body">
                        <h3 class="card-title"><?php echo e($post['title']); ?></h3>
                        <p class="card-text"><?php echo e($post['excerpt'] ?? substr(strip_tags($post['content']), 0, 150)); ?>...</p>
                        <div style="margin-top: 1rem; color: #6b7280; font-size: 0.875rem;">
                            <p>By <?php echo e($post['author_name']); ?> • <?php echo formatDate($post['created_at']); ?></p>
                        </div>
                        <a href="<?php echo SITE_URL; ?>pages/blog-detail.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-small">Read More</a>
                    </div>
                </div>
            <?php endforeach; ?>
    </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination mt-4">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="pagination-link">Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="pagination-link active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>" class="pagination-link"><?php echo $i; ?></a>
                    <?php endif; ?>
            <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="pagination-link">Next</a>
                <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
