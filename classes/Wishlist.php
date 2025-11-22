<?php
/**
 * Wishlist Model
 * Handles wishlist operations
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

class Wishlist {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Add to wishlist
     */
    public function add($userId, $productId) {
        try {
            // Check if already in wishlist
            if ($this->isInWishlist($userId, $productId)) {
                return ['success' => false, 'error' => 'Product already in wishlist'];
            }
            
            $stmt = $this->db->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
            $stmt->execute([$userId, $productId]);
            
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Add to wishlist error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to add to wishlist'];
        }
    }
    
    /**
     * Remove from wishlist
     */
    public function remove($userId, $productId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Remove from wishlist error: " . $e->getMessage());
            return ['success' => false];
        }
    }
    
    /**
     * Check if product is in wishlist
     */
    public function isInWishlist($userId, $productId) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get wishlist items
     */
    public function getItems($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT w.*, p.name, p.price, p.discount_price, p.image, p.slug, p.status
                FROM wishlist w
                INNER JOIN products p ON w.product_id = p.id
                WHERE w.user_id = ?
                ORDER BY w.created_at DESC
            ");
            
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get wishlist items error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get wishlist count
     */
    public function getCount($userId) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }
}

