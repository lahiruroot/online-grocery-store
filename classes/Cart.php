<?php
/**
 * Cart Model
 * Handles shopping cart operations
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/Product.php';

class Cart {
    private $db;
    private $product;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->product = new Product();
    }
    
    /**
     * Add item to cart
     */
    public function addItem($userId, $productId, $quantity = 1) {
        try {
            // Check if product exists and is in stock
            $product = $this->product->getById($productId);
            if (!$product || $product['status'] !== 'active') {
                return ['success' => false, 'error' => 'Product not available'];
            }
            
            if (!$this->product->isInStock($productId, $quantity)) {
                return ['success' => false, 'error' => 'Insufficient stock'];
            }
            
            // Check if item already in cart
            $stmt = $this->db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update quantity
                $newQuantity = $existing['quantity'] + $quantity;
                if (!$this->product->isInStock($productId, $newQuantity)) {
                    return ['success' => false, 'error' => 'Insufficient stock'];
                }
                
                $stmt = $this->db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$newQuantity, $existing['id']]);
            } else {
                // Insert new item
                $stmt = $this->db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$userId, $productId, $quantity]);
            }
            
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Add to cart error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to add item to cart'];
        }
    }
    
    /**
     * Update cart item quantity
     */
    public function updateQuantity($userId, $productId, $quantity) {
        try {
            if ($quantity <= 0) {
                return $this->removeItem($userId, $productId);
            }
            
            // Check stock
            if (!$this->product->isInStock($productId, $quantity)) {
                return ['success' => false, 'error' => 'Insufficient stock'];
            }
            
            $stmt = $this->db->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$quantity, $userId, $productId]);
            
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Update cart quantity error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to update cart'];
        }
    }
    
    /**
     * Remove item from cart
     */
    public function removeItem($userId, $productId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Remove from cart error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to remove item'];
        }
    }
    
    /**
     * Clear cart
     */
    public function clear($userId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$userId]);
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Clear cart error: " . $e->getMessage());
            return ['success' => false];
        }
    }
    
    /**
     * Get cart items
     */
    public function getItems($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, p.name, p.id as product_id,
                       p.image, p.stock_quantity, p.status
                FROM cart c
                INNER JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ?
                ORDER BY c.created_at DESC
            ");
            
            $stmt->execute([$userId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get prices separately for each item to avoid PDO corruption
            foreach ($items as &$item) {
                $priceStmt = $this->db->prepare("SELECT FORMAT(price, 2) as price_str, FORMAT(COALESCE(discount_price, 0), 2) as discount_str, discount_price IS NULL as discount_null FROM products WHERE id = ?");
                $priceStmt->execute([$item['product_id']]);
                $priceData = $priceStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($priceData) {
                    $priceStr = str_replace(',', '', $priceData['price_str']);
                    $item['price'] = $priceStr; // Keep as string
                    
                    if ($priceData['discount_null'] == 0) {
                        $discountStr = str_replace(',', '', $priceData['discount_str']);
                        $item['discount_price'] = $discountStr; // Keep as string
                    } else {
                        $item['discount_price'] = null;
                    }
                } else {
                    $item['price'] = '0.00';
                    $item['discount_price'] = null;
                }
            }
            unset($item); // Break reference
            
            return $items;
        } catch (PDOException $e) {
            error_log("Get cart items error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get cart count
     */
    public function getCount($userId) {
        try {
            $stmt = $this->db->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * Get cart total
     */
    public function getTotal($userId) {
        try {
            $items = $this->getItems($userId);
            $total = 0;
            
            foreach ($items as $item) {
                $price = $this->product->getPrice($item);
                $total += $price * $item['quantity'];
            }
            
            return $total;
        } catch (Exception $e) {
            error_log("Get cart total error: " . $e->getMessage());
            return 0;
        }
    }
}

