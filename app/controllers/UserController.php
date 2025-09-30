<?php
// UserController for authentication and user management
// PHP 5.5 compatible with sqlsrv

class UserController {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = md5($_POST['password']); // Simple hash for PHP 5.5 compat
            
            $query = "SELECT id, username, role FROM users WHERE username = ? AND password = ?";
            $params = array($username, $password);
            $stmt = sqlsrv_prepare($this->db, $query, $params);
            if ($stmt === false) {
                $error = 'Error preparing query: ' . print_r(sqlsrv_errors(), true);
            } else {
                if (sqlsrv_execute($stmt) === false) {
                    $error = 'Error executing query: ' . print_r(sqlsrv_errors(), true);
                } else {
                    $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                    if ($user) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role'];
                        
                        // Redirect based on role
                        $redirect = ($user['role'] === 'admin') ? '?controller=admin&action=dashboard' :
                                    (($user['role'] === 'agent') ? '?controller=agent&action=dashboard' : '?controller=user&action=dashboard');
                        header('Location: ' . $redirect);
                        exit;
                    } else {
                        $error = 'Credenciales inválidas';
                    }
                }
                sqlsrv_free_stmt($stmt);
            }
        }
        
        // Show login form
        include '../app/views/user/login.php';
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $plain_password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $password = md5($plain_password); // Hash input
            $role = isset($_POST['role']) ? $_POST['role'] : 'user';
            $department_id = null;
            if ($role !== 'user') {
                // Default to TI dept (id 1) for agents/admins; in full system, select from form
                $department_id = 1;
            }
            
            if ($plain_password !== $confirm_password) {
                $error = 'Las contraseñas no coinciden';
            } else {
                // Check if user exists
                $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
                $check_params = array($username, $email);
                $check_stmt = sqlsrv_prepare($this->db, $check_query, $check_params);
                if ($check_stmt === false || sqlsrv_execute($check_stmt) === false) {
                    $error = 'Error checking user: ' . print_r(sqlsrv_errors(), true);
                } else {
                    $existing = sqlsrv_fetch_array($check_stmt, SQLSRV_FETCH_ASSOC);
                    sqlsrv_free_stmt($check_stmt);
                    if ($existing) {
                        $error = 'Usuario o email ya existe';
                    } else {
                        $query = "INSERT INTO users (username, email, password, role, department_id) VALUES (?, ?, ?, ?, ?)";
                        $params = array($username, $email, $password, $role, $department_id);
                        $stmt = sqlsrv_prepare($this->db, $query, $params);
                        if ($stmt === false || sqlsrv_execute($stmt) === false) {
                            $error = 'Error al registrar: ' . print_r(sqlsrv_errors(), true);
                        } else {
                            $success = 'Registro exitoso. Puede iniciar sesión.';
                            // Optionally log in automatically
                        }
                        sqlsrv_free_stmt($stmt);
                    }
                }
            }
        }
        
        // Show register form
        include '../app/views/user/register.php';
    }
    
    public function logout() {
        session_destroy();
        header('Location: ?controller=user&action=login');
        exit;
    }
    
    public function dashboard() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?controller=user&action=login');
            exit;
        }
        
        $role = $_SESSION['role'];
        $userId = $_SESSION['user_id'];
        
        // Load user-specific data (e.g., tickets for user)
        if ($role === 'user') {
            // Query user's tickets
            $query = "SELECT t.id, t.title, t.status, t.created_at FROM tickets t WHERE t.user_id = ? ORDER BY t.created_at DESC";
            $params = array($userId);
            $stmt = sqlsrv_prepare($this->db, $query, $params);
            if ($stmt === false || sqlsrv_execute($stmt) === false) {
                $tickets = array();
            } else {
                $tickets = array();
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $tickets[] = $row;
                }
                sqlsrv_free_stmt($stmt);
            }
        } // For agent/admin, handle in their controllers
        
        include '../app/views/user/dashboard.php';
    }
    
    // Destructor to close DB
    public function __destruct() {
        closeDBConnection($this->db);
    }
}
?>