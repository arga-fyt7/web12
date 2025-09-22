<?php
require_once 'main.php';

// Authentication Functions
class Auth {
    private $pdo;
    private $user;

    public function __construct() {
        $this->pdo = getDBConnection();
    }

    // Register new user
    public function register($username, $email, $password, $fullName = '') {
        try {
            // Validate input
            if (empty($username) || empty($email) || empty($password)) {
                return ['success' => false, 'message' => 'All fields are required'];
            }

            if (strlen($password) < PASSWORD_MIN_LENGTH) {
                return ['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long'];
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }

            if (strlen($username) < 3) {
                return ['success' => false, 'message' => 'Username must be at least 3 characters long'];
            }

            // Check if user already exists
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword, $fullName]);

            $userId = $this->pdo->lastInsertId();

            // Log activity
            logActivity($userId, 'user_registered', 'New user registration');

            return ['success' => true, 'message' => 'Registration successful! You can now login.'];

        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }

    // Login user
    public function login($username, $password) {
        try {
            // Get user by username or email
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['success' => false, 'message' => 'Invalid username or email'];
            }

            // Check if account is active
            if ($user['status'] !== 'active') {
                return ['success' => false, 'message' => 'Account is ' . $user['status']];
            }

            // Check lockout
            if ($user['lockout_until'] && strtotime($user['lockout_until']) > time()) {
                $remaining = strtotime($user['lockout_until']) - time();
                $minutes = ceil($remaining / 60);
                return ['success' => false, 'message' => "Account locked. Try again in $minutes minutes"];
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                $this->incrementLoginAttempts($user['id']);
                return ['success' => false, 'message' => 'Invalid password'];
            }

            // Successful login
            $this->resetLoginAttempts($user['id']);
            $this->createSession($user);

            return ['success' => true, 'message' => 'Login successful!'];

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed. Please try again.'];
        }
    }

    // Create user session
    private function createSession($user) {
        try {
            // Generate session token
            $sessionToken = generateToken(64);

            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['session_token'] = $sessionToken;

            // Update last login
            $stmt = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);

            // Log activity
            logActivity($user['id'], 'user_login', 'User logged in');

        } catch (Exception $e) {
            error_log("Session creation error: " . $e->getMessage());
        }
    }

    // Increment login attempts
    private function incrementLoginAttempts($userId) {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?");
            $stmt->execute([$userId]);

            $stmt = $this->pdo->prepare("SELECT login_attempts FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $attempts = $stmt->fetch()['login_attempts'];

            if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                $lockoutUntil = date('Y-m-d H:i:s', time() + LOGIN_LOCKOUT_TIME);
                $stmt = $this->pdo->prepare("UPDATE users SET lockout_until = ? WHERE id = ?");
                $stmt->execute([$lockoutUntil, $userId]);

                logActivity($userId, 'account_locked', 'Account locked due to too many failed attempts');
            }
        } catch (Exception $e) {
            error_log("Login attempts error: " . $e->getMessage());
        }
    }

    // Reset login attempts
    private function resetLoginAttempts($userId) {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET login_attempts = 0, lockout_until = NULL WHERE id = ?");
            $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("Reset attempts error: " . $e->getMessage());
        }
    }

    // Get current user
    public function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        try {
            $stmt = $this->pdo->prepare("SELECT id, username, email, full_name, role, status, created_at, last_login FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Get user error: " . $e->getMessage());
            return null;
        }
    }

    // Update user profile
    public function updateProfile($userId, $data) {
        try {
            $allowedFields = ['full_name', 'email'];
            $updates = [];
            $values = [];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = ?";
                    $values[] = sanitizeInput($data[$field]);
                }
            }

            if (empty($updates)) {
                return ['success' => false, 'message' => 'No valid fields to update'];
            }

            $values[] = $userId;
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($values);

            logActivity($userId, 'profile_updated', 'User profile updated');

            return ['success' => true, 'message' => 'Profile updated successfully'];

        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Profile update failed'];
        }
    }

    // Change password
    public function changePassword($userId, $oldPassword, $newPassword) {
        try {
            // Verify old password
            $stmt = $this->pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!password_verify($oldPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }

            // Validate new password
            if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
                return ['success' => false, 'message' => 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long'];
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);

            logActivity($userId, 'password_changed', 'User password changed');

            return ['success' => true, 'message' => 'Password changed successfully'];

        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Password change failed'];
        }
    }

    // Logout
    public function logout() {
        try {
            if (isset($_SESSION['user_id'])) {
                logActivity($_SESSION['user_id'], 'user_logout', 'User logged out');
            }

            // Clear session
            session_unset();
            session_destroy();

            return ['success' => true, 'message' => 'Logged out successfully'];

        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Logout failed'];
        }
    }

    // Check if user is admin
    public function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    // Get all users (admin only)
    public function getAllUsers($page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;

            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM users");
            $stmt->execute();
            $total = $stmt->fetch()['total'];

            $stmt = $this->pdo->prepare("SELECT id, username, email, full_name, role, status, created_at, last_login FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
            $stmt->execute([$limit, $offset]);
            $users = $stmt->fetchAll();

            return [
                'success' => true,
                'users' => $users,
                'total' => $total,
                'page' => $page,
                'total_pages' => ceil($total / $limit)
            ];

        } catch (Exception $e) {
            error_log("Get users error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to fetch users'];
        }
    }
}

// Initialize Auth class
$auth = new Auth();

// Helper functions for templates
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['session_token']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function getCurrentUser() {
    global $auth;
    return $auth->getCurrentUser();
}
?>
