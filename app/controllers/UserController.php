<?php
// UserController for authentication and user management
// PHP 5.5 compatible with sqlsrv

require_once '../app/models/TicketModel.php';

class UserController {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = md5($_POST['password']); // Simple hash for PHP 5.5 compat
            
            // Use stored procedure for authentication
            $params = array($username, $password);
            $params_ref = &$params;
            $stmt = sqlsrv_prepare($this->db, "EXEC Usp_Tik_S_AutenticarUsuario @nombre_usuario = ?, @contrasena = ?", $params_ref);
            if ($stmt === false) {
                $error = 'Error preparing query: ' . print_r(sqlsrv_errors(), true);
            } else {
                if (sqlsrv_execute($stmt) === false) {
                    $error = 'Error executing query: ' . print_r(sqlsrv_errors(), true);
                } else {
                    $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                    if ($user) {                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['nombre_usuario'];
                        $_SESSION['role'] = $user['rol'];
                        
                        // Redirect based on role
                        $role = strtolower(trim($user['rol']));
                        $redirect = ($role === 'admin') ? '?controller=admin&action=dashboard' :
                                    (($role === 'agente') ? '?controller=agent&action=dashboard' : '?controller=user&action=dashboard');
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
                // Check if user exists using stored procedure
                $check_params = array($username, $email);
                $check_params_ref = &$check_params;
                $check_stmt = sqlsrv_prepare($this->db, "EXEC Usp_Tik_S_VerificarUsuarioExiste @nombre_usuario = ?, @correo = ?", $check_params_ref);
                if ($check_stmt === false || sqlsrv_execute($check_stmt) === false) {
                    $error = 'Error checking user: ' . print_r(sqlsrv_errors(), true);
                } else {
                    $existing = sqlsrv_fetch_array($check_stmt, SQLSRV_FETCH_ASSOC);
                    sqlsrv_free_stmt($check_stmt);
                    if ($existing) {
                        $error = 'Usuario o email ya existe';
                    } else {
                        // Create user using stored procedure
                        $params = array($username, $email, $password, $role, $department_id);
                        $params_ref = &$params;
                        $stmt = sqlsrv_prepare($this->db, "EXEC Usp_Tik_U_Usuario @nombre_usuario = ?, @correo = ?, @contrasena = ?, @rol = ?, @departamento_id = ?", $params_ref);
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
        $tickets = array();
        $recentUpdates = array();
        
        // Load user-specific data (e.g., tickets for user)
        if ($role === 'user') {
            // Get user's tickets using stored procedure
            $params = array($userId);
            $params_ref = &$params;
            $stmt = sqlsrv_prepare($this->db, "EXEC Usp_Tik_S_ObtenerTicketsUsuario @usuario_id = ?", $params_ref);
            if ($stmt !== false && sqlsrv_execute($stmt) !== false) {
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $tickets[] = $row;
                }
                sqlsrv_free_stmt($stmt);
            }
            
            // Get recent ticket updates for notifications
            $ticketModel = new TicketModel();
            $recentUpdates = $ticketModel->getRecentTicketUpdates($userId, 5);
        } // For agent/admin, handle in their controllers
        
        // Pass data to the view
        include '../app/views/user/dashboard.php';
    }
    
    // Destructor to close DB
    public function __destruct() {
        closeDBConnection($this->db);
    }
}
?>