<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Register a new user
     */
    public function register($name, $email, $password, $phone = null, $address = null) {
        try {
            // Check if email already exists
            if ($this->emailExists($email)) {
                return ['success' => false, 'error' => 'Email already registered'];
            }
            
            // Validate email
            if (!validateEmail($email)) {
                return ['success' => false, 'error' => 'Invalid email address'];
            }
            
            // Validate password
            if (strlen($password) < PASSWORD_MIN_LENGTH) {
                return ['success' => false, 'error' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
            }
            
            // Hash password
            $hashedPassword = hashPassword($password);
            
            // Insert user
            $stmt = $this->db->prepare("
                INSERT INTO users (name, email, password, phone, address, role) 
                VALUES (?, ?, ?, ?, ?, 'customer')
            ");
            
            $stmt->execute([$name, $email, $hashedPassword, $phone, $address]);
            
            $userId = $this->db->lastInsertId();
            
            return ['success' => true, 'user_id' => $userId];
        } catch (PDOException $e) {
            error_log("User registration error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Registration failed. Please try again.'];
        }
    }
    
    /**
     * Login user
     */
    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, name, email, password, role 
                FROM users 
                WHERE email = ?
            ");
            
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'error' => 'Invalid email or password'];
            }
            
            if (!verifyPassword($password, $user['password'])) {
                return ['success' => false, 'error' => 'Invalid email or password'];
            }
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            return ['success' => true, 'user' => $user];
        } catch (PDOException $e) {
            error_log("User login error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Login failed. Please try again.'];
        }
    }
    
    /**
     * Get user by ID
     */
    public function getById($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, name, email, phone, address, city, state, zip, country, role, created_at 
                FROM users 
                WHERE id = ?
            ");
            
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get user error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        try {
            $allowedFields = ['name', 'phone', 'address', 'city', 'state', 'zip', 'country'];
            $updateFields = [];
            $values = [];
            
            foreach ($allowedFields as $field) {
                // Use array_key_exists to check if key exists, even if value is empty
                if (array_key_exists($field, $data)) {
                    $updateFields[] = "$field = ?";
                    $values[] = $data[$field] ?? null;
                }
            }
            
            if (empty($updateFields)) {
                return ['success' => false, 'error' => 'No fields to update'];
            }
            
            $values[] = $userId;
            
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($values);
            
            if (!$result) {
                return ['success' => false, 'error' => 'Failed to update profile'];
            }
            
            // Update session if name changed
            if (isset($data['name']) && !empty($data['name'])) {
                $_SESSION['user_name'] = $data['name'];
            }
            
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Update profile error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to update profile: ' . $e->getMessage()];
        }
    }
    
    /**
     * Change password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Get current password
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user || !verifyPassword($currentPassword, $user['password'])) {
                return ['success' => false, 'error' => 'Current password is incorrect'];
            }
            
            // Validate new password
            if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
                return ['success' => false, 'error' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
            }
            
            // Update password
            $hashedPassword = hashPassword($newPassword);
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);
            
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Change password error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to change password'];
        }
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get all users (for admin)
     */
    public function getAll($page = 1, $perPage = ITEMS_PER_PAGE) {
        try {
            $offset = getPaginationOffset($page, $perPage);
            
            $stmt = $this->db->prepare("
                SELECT id, name, email, phone, role, created_at 
                FROM users 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ");
            
            $stmt->execute([$perPage, $offset]);
            $users = $stmt->fetchAll();
            
            // Get total count
            $countStmt = $this->db->query("SELECT COUNT(*) as total FROM users");
            $total = $countStmt->fetch()['total'];
            
            return [
                'users' => $users,
                'total' => $total,
                'pages' => ceil($total / $perPage)
            ];
        } catch (PDOException $e) {
            error_log("Get all users error: " . $e->getMessage());
            return ['users' => [], 'total' => 0, 'pages' => 0];
        }
    }
    
    /**
     * Delete user
     */
    public function delete($userId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Delete user error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to delete user'];
        }
    }
}

