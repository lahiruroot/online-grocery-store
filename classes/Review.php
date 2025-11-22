<?php
/**
 * Review Model
 * Handles product reviews
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

class Review {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Create review
     */
    public function create($userId, $productId, $rating, $comment, $title = null) {
        try {
            // Check if user already reviewed this product
            if ($this->hasReviewed($userId, $productId)) {
                return ['success' => false, 'error' => 'You have already reviewed this product'];
            }
            
            // Validate rating
            if ($rating < 1 || $rating > 5) {
                return ['success' => false, 'error' => 'Rating must be between 1 and 5'];
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO reviews (product_id, user_id, rating, title, comment, status)
                VALUES (?, ?, ?, ?, ?, 'pending')
            ");
            
            $stmt->execute([$productId, $userId, $rating, $title, $comment]);
            
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Create review error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to create review'];
        }
    }
    
    /**
     * Get reviews for product
     */
    public function getByProduct($productId, $page = 1, $perPage = REVIEWS_PER_PAGE, $status = 'approved') {
        try {
            $offset = getPaginationOffset($page, $perPage);
            
            $stmt = $this->db->prepare("
                SELECT r.*, u.name as user_name
                FROM reviews r
                INNER JOIN users u ON r.user_id = u.id
                WHERE r.product_id = ? AND r.status = ?
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $stmt->execute([$productId, $status, $perPage, $offset]);
            $reviews = $stmt->fetchAll();
            
            // Get total count
            $countStmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM reviews 
                WHERE product_id = ? AND status = ?
            ");
            $countStmt->execute([$productId, $status]);
            $total = $countStmt->fetch()['total'];
            
            return [
                'reviews' => $reviews,
                'total' => $total,
                'pages' => ceil($total / $perPage)
            ];
        } catch (PDOException $e) {
            error_log("Get reviews error: " . $e->getMessage());
            return ['reviews' => [], 'total' => 0, 'pages' => 0];
        }
    }
    
    /**
     * Get average rating for product
     */
    public function getAverageRating($productId) {
        try {
            $stmt = $this->db->prepare("
                SELECT AVG(rating) as average, COUNT(*) as count
                FROM reviews 
                WHERE product_id = ? AND status = 'approved'
            ");
            
            $stmt->execute([$productId]);
            $result = $stmt->fetch();
            
            return [
                'average' => round($result['average'] ?? 0, 1),
                'count' => (int)($result['count'] ?? 0)
            ];
        } catch (PDOException $e) {
            return ['average' => 0, 'count' => 0];
        }
    }
    
    /**
     * Check if user has reviewed product
     */
    public function hasReviewed($userId, $productId) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get all reviews (admin)
     */
    public function getAll($filters = [], $page = 1, $perPage = ITEMS_PER_PAGE) {
        try {
            $where = [];
            $params = [];
            
            if (isset($filters['status']) && !empty($filters['status'])) {
                $where[] = "r.status = ?";
                $params[] = $filters['status'];
            }
            
            if (isset($filters['product_id']) && $filters['product_id'] > 0) {
                $where[] = "r.product_id = ?";
                $params[] = $filters['product_id'];
            }
            
            $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
            
            $offset = getPaginationOffset($page, $perPage);
            
            $sql = "
                SELECT r.*, u.name as user_name, u.email as user_email, p.name as product_name
                FROM reviews r
                INNER JOIN users u ON r.user_id = u.id
                INNER JOIN products p ON r.product_id = p.id
                $whereClause
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $reviews = $stmt->fetchAll();
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM reviews r $whereClause";
            $countStmt = $this->db->prepare($countSql);
            $countParams = array_slice($params, 0, -2);
            $countStmt->execute($countParams);
            $total = $countStmt->fetch()['total'];
            
            return [
                'reviews' => $reviews,
                'total' => $total,
                'pages' => ceil($total / $perPage)
            ];
        } catch (PDOException $e) {
            error_log("Get all reviews error: " . $e->getMessage());
            return ['reviews' => [], 'total' => 0, 'pages' => 0];
        }
    }
    
    /**
     * Update review status
     */
    public function updateStatus($reviewId, $status) {
        try {
            $allowedStatuses = ['pending', 'approved', 'rejected'];
            if (!in_array($status, $allowedStatuses)) {
                return ['success' => false, 'error' => 'Invalid status'];
            }
            
            $stmt = $this->db->prepare("UPDATE reviews SET status = ? WHERE id = ?");
            $stmt->execute([$status, $reviewId]);
            
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Update review status error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to update review status'];
        }
    }
    
    /**
     * Delete review
     */
    public function delete($reviewId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->execute([$reviewId]);
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Delete review error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to delete review'];
        }
    }
}

