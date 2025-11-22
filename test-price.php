<?php
/**
 * Test Price Retrieval
 * Simple test to see what prices are being returned
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/classes/Product.php';

header('Content-Type: text/plain');

try {
    $db = Database::getInstance()->getConnection();
    $product = new Product();
    
    echo "=== Testing Price Retrieval ===\n\n";
    
    // Test 1: Direct SQL query
    echo "1. Direct SQL Query:\n";
    $stmt = $db->query("SELECT id, name, CONCAT('', ROUND(price, 2)) as price_str, CONCAT('', ROUND(discount_price, 2)) as discount_str FROM products WHERE id = 1");
    $raw = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Price (string): " . var_export($raw['price_str'], true) . " (type: " . gettype($raw['price_str']) . ")\n";
    echo "   Discount (string): " . var_export($raw['discount_str'], true) . " (type: " . gettype($raw['discount_str']) . ")\n\n";
    
    // Test 2: Using Product class
    echo "2. Product Class getById():\n";
    $prod = $product->getById(1);
    if ($prod) {
        echo "   Price: " . var_export($prod['price'], true) . " (type: " . gettype($prod['price']) . ")\n";
        echo "   Discount: " . var_export($prod['discount_price'], true) . " (type: " . gettype($prod['discount_price']) . ")\n\n";
    }
    
    // Test 3: Using getPrice method
    if ($prod) {
        echo "3. getPrice() method:\n";
        $price = $product->getPrice($prod);
        echo "   Result: " . var_export($price, true) . " (type: " . gettype($price) . ")\n\n";
    }
    
    // Test 4: Using formatPrice
    if ($prod) {
        echo "4. formatPrice():\n";
        echo "   Input (prod['price']): " . var_export($prod['price'], true) . " (type: " . gettype($prod['price']) . ")\n";
        $displayPrice = $product->getPrice($prod);
        echo "   Input (getPrice result): " . var_export($displayPrice, true) . " (type: " . gettype($displayPrice) . ")\n";
        $formatted = formatPrice($displayPrice);
        echo "   Result: " . var_export($formatted, true) . "\n\n";
    }
    
    // Test 5: Check for corruption
    echo "5. Corruption Check:\n";
    if ($prod && isset($prod['price'])) {
        $priceVal = $prod['price'];
        if ($priceVal > 100000) {
            echo "   WARNING: Price appears corrupted: {$priceVal}\n";
            echo "   Raw value: " . var_export($priceVal, true) . "\n";
            echo "   Binary: " . bin2hex(pack('d', $priceVal)) . "\n";
        } else {
            echo "   Price looks valid: {$priceVal}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>

