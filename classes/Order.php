<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/Cart.php';
require_once __DIR__ . '/Product.php';

class Order {
    private $db;
    private $cart;
    private $product;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->cart = new Cart();
        $this->product = new Product();
    }
    
    /**
     * Create order from cart
     */
    public function create($userId, $shippingAddress, $paymentMethod = 'cash_on_delivery', $billingAddress = null) {
        try {
            $this->db->beginTransaction();
            
            // Get cart items
            $cartItems = $this->cart->getItems($userId);
            
            if (empty($cartItems)) {
                return ['success' => false, 'error' => 'Cart is empty'];
            }
            
            // Calculate totals
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $price = $this->product->getPrice($item);
                // Convert price string to float for calculation
                $priceFloat = (float)$price;
                if ($priceFloat <= 0) {
                    $this->db->rollBack();
                    return ['success' => false, 'error' => 'Invalid price for product: ' . $item['name']];
                }
                $subtotal += $priceFloat * (int)$item['quantity'];
                
                // Check stock
                if (!$this->product->isInStock($item['product_id'], (int)$item['quantity'])) {
                    $this->db->rollBack();
                    return ['success' => false, 'error' => 'Product ' . $item['name'] . ' is out of stock'];
                }
            }
            
            if ($subtotal <= 0) {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Invalid order total'];
            }
            
            $taxAmount = $subtotal * 0.10; // 10% tax
            $shippingAmount = $subtotal > 100 ? 0 : 10; // Free shipping over $100
            $totalAmount = $subtotal + $taxAmount + $shippingAmount;
            
            // Generate order number
            $orderNumber = generateOrderNumber();
            
            // Create order
            $stmt = $this->db->prepare("
                INSERT INTO orders (user_id, order_number, total_amount, subtotal, tax_amount, shipping_amount, shipping_address, billing_address, payment_method, status, payment_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')
            ");
            
            $stmt->execute([
                (int)$userId,
                $orderNumber,
                (float)$totalAmount,
                (float)$subtotal,
                (float)$taxAmount,
                (float)$shippingAmount,
                $shippingAddress,
                $billingAddress ?? $shippingAddress,
                $paymentMethod
            ]);
            
            $orderId = $this->db->lastInsertId();
            
            if (!$orderId || $orderId <= 0) {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Failed to create order - no order ID returned'];
            }
            
            // Create order items and update stock
            foreach ($cartItems as $item) {
                $price = $this->product->getPrice($item);
                // Convert price string to float for calculation
                $priceFloat = (float)$price;
                $quantity = (int)$item['quantity'];
                $subtotalItem = $priceFloat * $quantity;
                
                if ($priceFloat <= 0 || $quantity <= 0) {
                    $this->db->rollBack();
                    return ['success' => false, 'error' => 'Invalid item data for: ' . $item['name']];
                }
                
                $stmt = $this->db->prepare("
                    INSERT INTO order_items (order_id, product_id, product_name, product_image, quantity, price, subtotal)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $orderId,
                    (int)$item['product_id'],
                    $item['name'],
                    $item['image'] ?? null,
                    $quantity,
                    $priceFloat,
                    $subtotalItem
                ]);
                
                // Update stock
                $this->product->updateStock((int)$item['product_id'], -$quantity);
            }
            
            // Clear cart
            $this->cart->clear($userId);
            
            $this->db->commit();
            
            return ['success' => true, 'order_id' => $orderId, 'order_number' => $orderNumber];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Create order error: " . $e->getMessage());
            $errorMsg = 'Failed to create order';
            
            // Provide more specific error messages
            if (strpos($e->getMessage(), 'foreign key') !== false) {
                $errorMsg = 'Invalid user or product data';
            } elseif (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $errorMsg = 'Order number already exists. Please try again.';
            } elseif (strpos($e->getMessage(), 'CHECK constraint') !== false) {
                $errorMsg = 'Invalid order data. Please check your cart items.';
            }
            
            return ['success' => false, 'error' => $errorMsg . ' (Error: ' . $e->getMessage() . ')'];
        }
    }
    
    /**
     * Get order by ID
     */
    public function getById($orderId, $userId = null) {
        try {
            if ($userId) {
                $stmt = $this->db->prepare("
                    SELECT * FROM orders 
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$orderId, $userId]);
            } else {
                $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
                $stmt->execute([$orderId]);
            }
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get order error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get order by order number
     */
    public function getByOrderNumber($orderNumber, $userId = null) {
        try {
            if ($userId) {
                $stmt = $this->db->prepare("
                    SELECT * FROM orders 
                    WHERE order_number = ? AND user_id = ?
                ");
                $stmt->execute([$orderNumber, $userId]);
            } else {
                $stmt = $this->db->prepare("SELECT * FROM orders WHERE order_number = ?");
                $stmt->execute([$orderNumber]);
            }
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get order by number error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get order items
     */
    public function getItems($orderId) {
        try {
            $stmt = $this->db->prepare("
                SELECT oi.*, p.slug as product_slug
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
                ORDER BY oi.id ASC
            ");
            
            $stmt->execute([$orderId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get order items error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user orders
     */
    public function getUserOrders($userId, $page = 1, $perPage = ITEMS_PER_PAGE) {
        try {
            $offset = getPaginationOffset($page, $perPage);
            
            $stmt = $this->db->prepare("
                SELECT * FROM orders 
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $stmt->execute([$userId, $perPage, $offset]);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count
            $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
            $countStmt->execute([$userId]);
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            return [
                'orders' => $orders,
                'total' => $total,
                'pages' => ceil($total / $perPage)
            ];
        } catch (PDOException $e) {
            error_log("Get user orders error: " . $e->getMessage());
            return ['orders' => [], 'total' => 0, 'pages' => 0];
        }
    }
    
    /**
     * Get all orders (admin)
     */
    public function getAll($filters = [], $page = 1, $perPage = ITEMS_PER_PAGE) {
        try {
            $where = [];
            $params = [];
            
            if (isset($filters['status']) && !empty($filters['status'])) {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }
            
            if (isset($filters['payment_status']) && !empty($filters['payment_status'])) {
                $where[] = "payment_status = ?";
                $params[] = $filters['payment_status'];
            }
            
            $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
            
            $offset = getPaginationOffset($page, $perPage);
            
            $sql = "
                SELECT o.*, u.name as user_name, u.email as user_email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                $whereClause
                ORDER BY o.created_at DESC
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM orders o $whereClause";
            $countStmt = $this->db->prepare($countSql);
            $countParams = array_slice($params, 0, -2);
            $countStmt->execute($countParams);
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            return [
                'orders' => $orders,
                'total' => $total,
                'pages' => ceil($total / $perPage)
            ];
        } catch (PDOException $e) {
            error_log("Get all orders error: " . $e->getMessage());
            return ['orders' => [], 'total' => 0, 'pages' => 0];
        }
    }
    
    /**
     * Update order status
     */
    public function updateStatus($orderId, $status) {
        try {
            $allowedStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
            if (!in_array($status, $allowedStatuses)) {
                return ['success' => false, 'error' => 'Invalid status'];
            }
            
            // If status is changed to 'delivered', automatically set payment_status to 'paid'
            if ($status === 'delivered') {
                $stmt = $this->db->prepare("UPDATE orders SET status = ?, payment_status = 'paid' WHERE id = ?");
                $stmt->execute([$status, $orderId]);
            } else {
                $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$status, $orderId]);
            }
            
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Update order status error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to update order status'];
        }
    }
    
    /**
     * Update payment status
     */
    public function updatePaymentStatus($orderId, $paymentStatus) {
        try {
            $allowedStatuses = ['pending', 'paid', 'failed', 'refunded'];
            if (!in_array($paymentStatus, $allowedStatuses)) {
                return ['success' => false, 'error' => 'Invalid payment status'];
            }
            
            $stmt = $this->db->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
            $stmt->execute([$paymentStatus, $orderId]);
            
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Update payment status error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to update payment status'];
        }
    }
}

