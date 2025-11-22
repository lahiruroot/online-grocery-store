<?php
/**
 * Blog Model
 * Handles blog operations
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

class Blog {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get all blogs
     */
    public function getAll($status = 'published', $page = 1, $perPage = BLOGS_PER_PAGE) {
        try {
            $offset = getPaginationOffset($page, $perPage);
            
            if ($status) {
                $stmt = $this->db->prepare("
                    SELECT b.*, u.name as author_name
                    FROM blogs b
                    INNER JOIN users u ON b.author_id = u.id
                    WHERE b.status = ?
                    ORDER BY b.created_at DESC
                    LIMIT ? OFFSET ?
                ");
                $stmt->execute([$status, $perPage, $offset]);
            } else {
                $stmt = $this->db->prepare("
                    SELECT b.*, u.name as author_name
                    FROM blogs b
                    INNER JOIN users u ON b.author_id = u.id
                    ORDER BY b.created_at DESC
                    LIMIT ? OFFSET ?
                ");
                $stmt->execute([$perPage, $offset]);
            }
            
            $blogs = $stmt->fetchAll();
            
            // Get total count
            if ($status) {
                $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM blogs WHERE status = ?");
                $countStmt->execute([$status]);
            } else {
                $countStmt = $this->db->query("SELECT COUNT(*) as total FROM blogs");
            }
            $total = $countStmt->fetch()['total'];
            
            return [
                'blogs' => $blogs,
                'total' => $total,
                'pages' => ceil($total / $perPage)
            ];
        } catch (PDOException $e) {
            error_log("Get all blogs error: " . $e->getMessage());
            return ['blogs' => [], 'total' => 0, 'pages' => 0];
        }
    }
    
    /**
     * Get blog by ID
     */
    public function getById($blogId) {
        try {
            $stmt = $this->db->prepare("
                SELECT b.*, u.name as author_name, u.email as author_email
                FROM blogs b
                INNER JOIN users u ON b.author_id = u.id
                WHERE b.id = ?
            ");
            
            $stmt->execute([$blogId]);
            $blog = $stmt->fetch();
            
            // Increment views
            if ($blog) {
                $this->incrementViews($blogId);
            }
            
            return $blog;
        } catch (PDOException $e) {
            error_log("Get blog error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get blog by slug
     */
    public function getBySlug($slug) {
        try {
            $stmt = $this->db->prepare("
                SELECT b.*, u.name as author_name, u.email as author_email
                FROM blogs b
                INNER JOIN users u ON b.author_id = u.id
                WHERE b.slug = ?
            ");
            
            $stmt->execute([$slug]);
            $blog = $stmt->fetch();
            
            // Increment views
            if ($blog) {
                $this->incrementViews($blog['id']);
            }
            
            return $blog;
        } catch (PDOException $e) {
            error_log("Get blog by slug error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create blog
     */
    public function create($data) {
        try {
            // Generate slug
            $slug = generateSlug($data['title']);
            
            // Ensure slug is unique
            $originalSlug = $slug;
            $counter = 1;
            while ($this->slugExists($slug)) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            // Generate excerpt if not provided
            $excerpt = $data['excerpt'] ?? substr(strip_tags($data['content']), 0, 200);
            
            $stmt = $this->db->prepare("
                INSERT INTO blogs (title, content, excerpt, author_id, image, slug, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['title'],
                $data['content'],
                $excerpt,
                $data['author_id'],
                $data['image'] ?? null,
                $slug,
                $data['status'] ?? 'draft'
            ]);
            
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Create blog error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to create blog'];
        }
    }
    
    /**
     * Update blog
     */
    public function update($blogId, $data) {
        try {
            $allowedFields = ['title', 'content', 'excerpt', 'image', 'status'];
            $updateFields = [];
            $values = [];
            
            // If title is being updated, regenerate slug
            if (isset($data['title'])) {
                $slug = generateSlug($data['title']);
                $originalSlug = $slug;
                $counter = 1;
                while ($this->slugExists($slug, $blogId)) {
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
            
            $values[] = $blogId;
            
            $sql = "UPDATE blogs SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Update blog error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to update blog'];
        }
    }
    
    /**
     * Delete blog
     */
    public function delete($blogId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM blogs WHERE id = ?");
            $stmt->execute([$blogId]);
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Delete blog error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to delete blog'];
        }
    }
    
    /**
     * Increment views
     */
    private function incrementViews($blogId) {
        try {
            $stmt = $this->db->prepare("UPDATE blogs SET views = views + 1 WHERE id = ?");
            $stmt->execute([$blogId]);
        } catch (PDOException $e) {
            // Silently fail
        }
    }
    
    /**
     * Check if slug exists
     */
    private function slugExists($slug, $excludeId = null) {
        try {
            if ($excludeId) {
                $stmt = $this->db->prepare("SELECT id FROM blogs WHERE slug = ? AND id != ?");
                $stmt->execute([$slug, $excludeId]);
            } else {
                $stmt = $this->db->prepare("SELECT id FROM blogs WHERE slug = ?");
                $stmt->execute([$slug]);
            }
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
}

