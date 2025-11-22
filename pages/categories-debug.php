<?php
// Enable error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Categories Page Debug</h1>";
echo "<hr>";

echo "<h2>Step 1: Loading Constants</h2>";
try {
    require_once __DIR__ . '/../config/constants.php';
    echo "<p style='color:green;'>✓ Constants loaded</p>";
    echo "<p>SITE_URL: " . (defined('SITE_URL') ? SITE_URL : 'NOT DEFINED') . "</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error loading constants: " . $e->getMessage() . "</p>";
    die();
}

echo "<h2>Step 2: Loading Functions</h2>";
try {
    require_once __DIR__ . '/../config/functions.php';
    echo "<p style='color:green;'>✓ Functions loaded</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error loading functions: " . $e->getMessage() . "</p>";
    die();
}

echo "<h2>Step 3: Loading Database</h2>";
try {
    require_once __DIR__ . '/../config/db.php';
    echo "<p style='color:green;'>✓ Database class loaded</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error loading database: " . $e->getMessage() . "</p>";
    die();
}

echo "<h2>Step 4: Database Connection</h2>";
try {
    $db = Database::getInstance()->getConnection();
    echo "<p style='color:green;'>✓ Database connected</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p><strong>Common issues:</strong></p>";
    echo "<ul>";
    echo "<li>MySQL is not running in XAMPP</li>";
    echo "<li>Database 'grocery_store' doesn't exist</li>";
    echo "<li>Wrong username/password</li>";
    echo "</ul>";
    die();
}

echo "<h2>Step 5: Loading Category Class</h2>";
try {
    require_once __DIR__ . '/../classes/Category.php';
    echo "<p style='color:green;'>✓ Category class loaded</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error loading Category class: " . $e->getMessage() . "</p>";
    die();
}

echo "<h2>Step 6: Getting Categories</h2>";
try {
    $category = new Category();
    $categories = $category->getAll('active');
    echo "<p style='color:green;'>✓ Categories retrieved</p>";
    echo "<p>Number of categories: " . count($categories) . "</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error getting categories: " . $e->getMessage() . "</p>";
    die();
}

echo "<h2>Step 7: Loading Header</h2>";
try {
    $page_title = 'Categories';
    $extra_css = 'categories-section.css';
    require_once __DIR__ . '/../includes/header.php';
    echo "<p style='color:green;'>✓ Header loaded</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error loading header: " . $e->getMessage() . "</p>";
    die();
}

echo "<h2>Step 8: Displaying Content</h2>";
echo "<div class='container mt-4'>";
echo "<div class='categories-header'>";
echo "<h1>Shop by Category</h1>";
echo "<p>Browse our wide selection of fresh categories</p>";
echo "</div>";

if (empty($categories)) {
    echo "<div class='alert alert-info mt-4'>";
    echo "<p>No categories available.</p>";
    echo "</div>";
} else {
    echo "<div class='grid grid-cols-4 mt-4'>";
    foreach ($categories as $cat) {
        echo "<a href='" . SITE_URL . "pages/category-products.php?id=" . $cat['id'] . "' class='category-card'>";
        echo "<div class='category-card-image-wrapper'>";
        if (!empty($cat['image'])) {
            echo "<img src='" . SITE_URL . "public/uploads/" . htmlspecialchars($cat['image']) . "' alt='" . htmlspecialchars($cat['name']) . "' class='category-card-image' loading='lazy'>";
        } else {
            echo "<div class='category-card-placeholder'><span></span></div>";
        }
        echo "</div>";
        echo "<div class='category-card-body'>";
        echo "<h3 class='category-card-title'>" . htmlspecialchars($cat['name']) . "</h3>";
        if (!empty($cat['description'])) {
            echo "<p class='category-card-description'>" . htmlspecialchars($cat['description']) . "</p>";
        }
        echo "</div>";
        echo "</a>";
    }
    echo "</div>";
}
echo "</div>";

echo "<h2>Step 9: Loading Footer</h2>";
try {
    require_once __DIR__ . '/../includes/footer.php';
    echo "<p style='color:green;'>✓ Footer loaded</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error loading footer: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Debug Complete!</h2>";
echo "<p>If you see this message, all components loaded successfully.</p>";
echo "<p>If the page looks broken, it's likely a CSS issue (which we've already fixed).</p>";
?>

