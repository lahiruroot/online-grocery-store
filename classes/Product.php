<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

class Product {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get product by ID
     */
    public function getById($productId) {
        try {
            // Use separate query to get prices as strings to avoid PDO corruption
            $stmt = $this->db->prepare("
                SELECT p.id, p.name, p.category_id, p.description, p.short_description, 
                       p.stock_quantity, p.sku, p.image, p.images,
                       p.slug, p.status, p.featured, p.created_at, p.updated_at,
                       c.name as category_name, c.slug as category_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id = ?
            ");
            
            $stmt->execute([$productId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get prices separately using FORMAT to force string return
            if ($result) {
                $priceStmt = $this->db->prepare("SELECT FORMAT(price, 2) as price_str, FORMAT(COALESCE(discount_price, 0), 2) as discount_str, discount_price IS NULL as discount_null FROM products WHERE id = ?");
                $priceStmt->execute([$productId]);
                $priceData = $priceStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($priceData) {
                    // Keep prices as strings to avoid float corruption
                    $priceStr = str_replace(',', '', $priceData['price_str']);
                    $result['price'] = $priceStr; // Keep as string
                    
                    if ($priceData['discount_null'] == 0) {
                        $discountStr = str_replace(',', '', $priceData['discount_str']);
                        $result['discount_price'] = $discountStr; // Keep as string
                    } else {
                        $result['discount_price'] = null;
                    }
                } else {
                    $result['price'] = '0.00';
                    $result['discount_price'] = null;
                }
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Get product error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get product by slug
     */
    public function getBySlug($slug) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.id, p.name, p.category_id, p.description, p.short_description,
                       p.stock_quantity, p.sku, p.image, p.images,
                       p.slug, p.status, p.featured, p.created_at, p.updated_at,
                       c.name as category_name, c.slug as category_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.slug = ?
            ");
            
            $stmt->execute([$slug]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Safely convert string prices to float
            if ($result) {
                if (isset($result['price'])) {
                    $priceStr = (string)$result['price'];
                    $priceStr = trim($priceStr);
                    $priceStr = preg_replace('/[^0-9.]/', '', $priceStr);
                    if (preg_match('/^\d+\.?\d*$/', $priceStr)) {
                        $result['price'] = (float)$priceStr;
                    } else {
                        $result['price'] = 0;
                    }
                } else {
                    $result['price'] = 0;
                }
                
                if (isset($result['discount_price']) && $result['discount_price'] !== null && $result['discount_price'] !== '') {
                    $discountStr = (string)$result['discount_price'];
                    $discountStr = trim($discountStr);
                    $discountStr = preg_replace('/[^0-9.]/', '', $discountStr);
                    if (preg_match('/^\d+\.?\d*$/', $discountStr)) {
                        $result['discount_price'] = (float)$discountStr;
                    } else {
                        $result['discount_price'] = null;
                    }
                } else {
                    $result['discount_price'] = null;
                }
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Get product by slug error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all products with filters
     */
    public function getAll($filters = [], $page = 1, $perPage = ITEMS_PER_PAGE) {
        try {
            $where = ["p.status = 'active'"];
            $params = [];
            
            if (isset($filters['category_id']) && $filters['category_id'] > 0) {
                $where[] = "p.category_id = ?";
                $params[] = $filters['category_id'];
            }
            
            if (isset($filters['search']) && !empty($filters['search'])) {
                $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (isset($filters['featured']) && $filters['featured']) {
                $where[] = "p.featured = 1";
            }
            
            $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
            
            $offset = getPaginationOffset($page, $perPage);
            
            $sql = "
                SELECT p.id, p.name, p.category_id, p.description, p.short_description,
                       p.stock_quantity, p.sku, p.image, p.images,
                       p.slug, p.status, p.featured, p.created_at, p.updated_at,
                       c.name as category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                $whereClause
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get prices separately for all products to avoid PDO corruption
            foreach ($products as &$product) {
                $priceStmt = $this->db->prepare("SELECT FORMAT(price, 2) as price_str, FORMAT(COALESCE(discount_price, 0), 2) as discount_str, discount_price IS NULL as discount_null FROM products WHERE id = ?");
                $priceStmt->execute([$product['id']]);
                $priceData = $priceStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($priceData) {
                    $priceStr = str_replace(',', '', $priceData['price_str']);
                    $product['price'] = $priceStr; // Keep as string
                    
                    if ($priceData['discount_null'] == 0) {
                        $discountStr = str_replace(',', '', $priceData['discount_str']);
                        $product['discount_price'] = $discountStr; // Keep as string
                    } else {
                        $product['discount_price'] = null;
                    }
                } else {
                    $product['price'] = '0.00';
                    $product['discount_price'] = null;
                }
            }
            unset($product); // Break reference
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM products p $whereClause";
            $countStmt = $this->db->prepare($countSql);
            $countParams = array_slice($params, 0, -2); // Remove limit and offset
            $countStmt->execute($countParams);
            $total = $countStmt->fetch()['total'];
            
            return [
                'products' => $products,
                'total' => $total,
                'pages' => ceil($total / $perPage)
            ];
        } catch (PDOException $e) {
            error_log("Get all products error: " . $e->getMessage());
            return ['products' => [], 'total' => 0, 'pages' => 0];
        }
    }
    
    /**
     * Get featured products
     */
    public function getFeatured($limit = 8) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.id, p.name, p.category_id, p.description, p.short_description,
                       p.stock_quantity, p.sku, p.image, p.images,
                       p.slug, p.status, p.featured, p.created_at, p.updated_at,
                       c.name as category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'active' AND p.featured = 1
                ORDER BY p.created_at DESC
                LIMIT ?
            ");
            
            $stmt->execute([$limit]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get prices separately for all products to avoid PDO corruption
            foreach ($products as &$product) {
                $priceStmt = $this->db->prepare("SELECT FORMAT(price, 2) as price_str, FORMAT(COALESCE(discount_price, 0), 2) as discount_str, discount_price IS NULL as discount_null FROM products WHERE id = ?");
                $priceStmt->execute([$product['id']]);
                $priceData = $priceStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($priceData) {
                    $priceStr = str_replace(',', '', $priceData['price_str']);
                    $product['price'] = $priceStr; // Keep as string
                    
                    if ($priceData['discount_null'] == 0) {
                        $discountStr = str_replace(',', '', $priceData['discount_str']);
                        $product['discount_price'] = $discountStr; // Keep as string
                    } else {
                        $product['discount_price'] = null;
                    }
                } else {
                    $product['price'] = '0.00';
                    $product['discount_price'] = null;
                }
            }
            unset($product); // Break reference
            
            return $products;
        } catch (PDOException $e) {
            error_log("Get featured products error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create product
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
                INSERT INTO products (name, category_id, description, short_description, price, discount_price, stock_quantity, sku, image, slug, status, featured)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['name'],
                $data['category_id'],
                $data['description'] ?? null,
                $data['short_description'] ?? null,
                $data['price'],
                $data['discount_price'] ?? null,
                $data['stock_quantity'] ?? 0,
                $data['sku'] ?? null,
                $data['image'] ?? null,
                $slug,
                $data['status'] ?? 'active',
                $data['featured'] ?? 0
            ]);
            
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Create product error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to create product'];
        }
    }
    
    /**
     * Update product
     */
    public function update($productId, $data) {
        try {
            $allowedFields = ['name', 'category_id', 'description', 'short_description', 'price', 'discount_price', 'stock_quantity', 'sku', 'image', 'status', 'featured'];
            $updateFields = [];
            $values = [];
            
            // If name is being updated, regenerate slug
            if (isset($data['name'])) {
                $slug = generateSlug($data['name']);
                $originalSlug = $slug;
                $counter = 1;
                while ($this->slugExists($slug, $productId)) {
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
            
            $values[] = $productId;
            
            $sql = "UPDATE products SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Update product error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to update product'];
        }
    }
    
    /**
     * Delete product
     */
    public function delete($productId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Delete product error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to delete product'];
        }
    }
    
    /**
     * Check if slug exists
     */
    private function slugExists($slug, $excludeId = null) {
        try {
            if ($excludeId) {
                $stmt = $this->db->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
                $stmt->execute([$slug, $excludeId]);
            } else {
                $stmt = $this->db->prepare("SELECT id FROM products WHERE slug = ?");
                $stmt->execute([$slug]);
            }
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get product price (with discount if available)
     * Returns price as string to avoid float corruption
     */
    public function getPrice($product) {
        // Prices are already strings from database
        $price = '0.00';
        $discountPrice = null;
        
        // Handle price
        if (isset($product['price'])) {
            $priceStr = (string)$product['price'];
            $priceStr = trim($priceStr);
            $priceStr = preg_replace('/[^0-9.]/', '', $priceStr);
            
            if (preg_match('/^\d+\.?\d*$/', $priceStr)) {
                $price = $priceStr;
            }
        }
        
        // Handle discount_price
        if (isset($product['discount_price']) && $product['discount_price'] !== null && $product['discount_price'] !== '') {
            $discountStr = (string)$product['discount_price'];
            $discountStr = trim($discountStr);
            $discountStr = preg_replace('/[^0-9.]/', '', $discountStr);
            
            if (preg_match('/^\d+\.?\d*$/', $discountStr)) {
                $discountPrice = $discountStr;
            }
        }
        
        // Validate prices are reasonable (not corrupted)
        $priceFloat = (float)$price;
        $maxPrice = 100000;
        
        // If price is corrupted or invalid, return '0.00'
        if ($priceFloat <= 0 || $priceFloat > $maxPrice) {
            return '0.00';
        }
        
        // Check discount price - must be valid and less than regular price
        if ($discountPrice !== null) {
            $discountFloat = (float)$discountPrice;
            if ($discountFloat > 0 && $discountFloat < $priceFloat && $discountFloat <= $maxPrice) {
                return $discountPrice; // Return string
            }
        }
        
        return $price; // Return string
    }
    
    /**
     * Check if product is in stock
     */
    public function isInStock($productId, $quantity = 1) {
        try {
            $stmt = $this->db->prepare("SELECT stock_quantity FROM products WHERE id = ? AND status = 'active'");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if (!$product) {
                return false;
            }
            
            return $product['stock_quantity'] >= $quantity;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Update stock quantity
     */
    public function updateStock($productId, $quantity) {
        try {
            $stmt = $this->db->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
            $stmt->execute([$quantity, $productId]);
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Update stock error: " . $e->getMessage());
            return ['success' => false];
        }
    }
}

