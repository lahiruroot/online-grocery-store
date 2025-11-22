<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

class Category {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get all categories
     */
    public function getAll($status = 'active') {
        try {
            if ($status) {
                $stmt = $this->db->prepare("
                    SELECT * FROM categories 
                    WHERE status = ? 
                    ORDER BY sort_order ASC, name ASC
                ");
                $stmt->execute([$status]);
            } else {
                $stmt = $this->db->query("
                    SELECT * FROM categories 
                    ORDER BY sort_order ASC, name ASC
                ");
            }
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get all categories error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get category by ID
     */
    public function getById($categoryId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$categoryId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get category error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get category by slug
     */
    public function getBySlug($slug) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM categories WHERE slug = ?");
            $stmt->execute([$slug]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get category by slug error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create category
     */
    public function create($data) {
        try {
            // Generate slug
            $slug = generateSlug($data['name']);
            
            // Ensure slug is unique
            $originalSlug = $slug;
            $counter = 1;
            while ($this->slugExists($slug)) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO categories (name, description, image, slug, status, sort_order)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $data['image'] ?? null,
                $slug,
                $data['status'] ?? 'active',
                $data['sort_order'] ?? 0
            ]);
            
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Create category error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to create category'];
        }
    }
    
    /**
     * Update category
     */
    public function update($categoryId, $data) {
        try {
            $allowedFields = ['name', 'description', 'image', 'status', 'sort_order'];
            $updateFields = [];
            $values = [];
            
            // If name is being updated, regenerate slug
            if (isset($data['name'])) {
                $slug = generateSlug($data['name']);
                $originalSlug = $slug;
                $counter = 1;
                while ($this->slugExists($slug, $categoryId)) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
                $updateFields[] = "slug = ?";
                $values[] = $slug;
            }
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = ?";
                    $values[] = $data[$field];
                }
            }
            
            if (empty($updateFields)) {
                return ['success' => false, 'error' => 'No fields to update'];
            }
            
            $values[] = $categoryId;
            
            $sql = "UPDATE categories SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Update category error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to update category'];
        }
    }
    
    /**
     * Delete category
     */
    public function delete($categoryId) {
        try {
            // Check if category has products
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
            $stmt->execute([$categoryId]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                return ['success' => false, 'error' => 'Cannot delete category with existing products'];
            }
            
            $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$categoryId]);
            
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Delete category error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to delete category'];
        }
    }
    
    /**
     * Get product count in category
     */
    public function getProductCount($categoryId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM products 
                WHERE category_id = ? AND status = 'active'
            ");
            $stmt->execute([$categoryId]);
            $result = $stmt->fetch();
            return (int)$result['count'];
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * Check if slug exists
     */
    private function slugExists($slug, $excludeId = null) {
        try {
            if ($excludeId) {
                $stmt = $this->db->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
                $stmt->execute([$slug, $excludeId]);
            } else {
                $stmt = $this->db->prepare("SELECT id FROM categories WHERE slug = ?");
                $stmt->execute([$slug]);
            }
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
}

