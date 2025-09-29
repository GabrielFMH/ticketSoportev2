<?php
// UserController for authentication and user management
// PHP 5.5 compatible

class UserController {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $this->db->real_escape_string($_POST['username']);
            $password = md5($_POST['password']); // Simple hash for PHP 5.5 compat
            
            $query = "SELECT id, username, role FROM users WHERE username = '$username' AND password = '$password'";
            $result = $this->db->query($query);
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
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
        
        // Show login form
        include '../app/views/user/login.php';
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $this->db->real_escape_string($_POST['username']);
            $email = $this->db->real_escape_string($_POST['email']);
            $plain_password = $this->db->real_escape_string($_POST['password']);
            $confirm_password = $this->db->real_escape_string($_POST['confirm_password']);
            $password = md5($plain_password); // Hash input
            $role = isset($_POST['role']) ? $this->db->real_escape_string($_POST['role']) : 'user';
            $department_id = null;
            if ($role !== 'user') {
                // Default to TI dept (id 1) for agents/admins; in full system, select from form
                $department_id = 1;
            }
            
            if ($plain_password !== $confirm_password) {
                $error = 'Las contraseñas no coinciden';
            } else {
                // Check if user exists
                $check = $this->db->query("SELECT id FROM users WHERE username = '$username' OR email = '$email'");
                if ($check && $check->num_rows > 0) {
                    $error = 'Usuario o email ya existe';
                } else {
                    $query = "INSERT INTO users (username, email, password, role, department_id) VALUES ('$username', '$email', '$password', '$role', " . ($department_id ? $department_id : 'NULL') . ")";
                    if ($this->db->query($query)) {
                        $success = 'Registro exitoso. Puede iniciar sesión.';
                        // Optionally log in automatically
                    } else {
                        $error = 'Error al registrar: ' . $this->db->error;
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
            $query = "SELECT t.id, t.title, t.status, t.created_at FROM tickets t WHERE t.user_id = $userId ORDER BY t.created_at DESC";
            $result = $this->db->query($query);
            $tickets = $result ? $result->fetch_all(MYSQLI_ASSOC) : array();
        } // For agent/admin, handle in their controllers
        
        include '../app/views/user/dashboard.php';
    }
    
    // Destructor to close DB
    public function __destruct() {
        closeDBConnection($this->db);
    }
}
?>