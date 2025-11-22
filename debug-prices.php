<?php
/**
 * Debug Product Prices
 * Check what's actually being returned from database
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/classes/Product.php';

header('Content-Type: text/html; charset=utf-8');

try {
    $db = Database::getInstance()->getConnection();
    $product = new Product();
    
    echo "<h2>Debug Product Prices</h2>";
    
    // Method 1: Direct PDO query
    echo "<h3>1. Direct PDO Query (FETCH_ASSOC):</h3>";
    $stmt = $db->query("SELECT id, name, price, discount_price FROM products WHERE id = 1");
    $raw1 = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    var_dump($raw1);
    echo "Price type: " . gettype($raw1['price']) . "\n";
    echo "Price value: " . $raw1['price'] . "\n";
    echo "</pre>";
    
    // Method 2: Direct PDO query with FETCH_NUM
    echo "<h3>2. Direct PDO Query (FETCH_NUM):</h3>";
    $stmt = $db->query("SELECT id, name, price, discount_price FROM products WHERE id = 1");
    $raw2 = $stmt->fetch(PDO::FETCH_NUM);
    echo "<pre>";
    var_dump($raw2);
    echo "</pre>";
    
    // Method 3: Using CAST
    echo "<h3>3. Direct PDO Query with CAST:</h3>";
    $stmt = $db->query("SELECT id, name, CAST(price AS CHAR) as price_char, CAST(price AS DECIMAL(10,2)) as price_decimal FROM products WHERE id = 1");
    $raw3 = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    var_dump($raw3);
    echo "</pre>";
    
    // Method 4: Using Product class
    echo "<h3>4. Product Class getById():</h3>";
    $prod = $product->getById(1);
    echo "<pre>";
    var_dump($prod);
    if ($prod) {
        echo "Price type: " . gettype($prod['price']) . "\n";
        echo "Price value: " . $prod['price'] . "\n";
    }
    echo "</pre>";
    
    // Method 5: Check getPrice method
    if ($prod) {
        $price = $product->getPrice($prod);
        echo "<h3>5. getPrice() result:</h3>";
        echo "<pre>";
        var_dump($price);
        echo "Type: " . gettype($price) . "\n";
        echo "</pre>";
    }
    
    // Method 6: Raw SQL with different fetch modes
    echo "<h3>6. Testing different fetch modes:</h3>";
    $stmt = $db->query("SELECT price FROM products WHERE id = 1");
    
    echo "<strong>FETCH_ASSOC:</strong><br>";
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    var_dump($row);
    echo "<br><br>";
    
    echo "<strong>FETCH_OBJ:</strong><br>";
    $stmt = $db->query("SELECT price FROM products WHERE id = 1");
    $row = $stmt->fetch(PDO::FETCH_OBJ);
    var_dump($row);
    echo "<br><br>";
    
    echo "<strong>FETCH_COLUMN:</strong><br>";
    $stmt = $db->query("SELECT price FROM products WHERE id = 1");
    $row = $stmt->fetchColumn();
    var_dump($row);
    echo "<br><br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

