<?php
// TicketController for ticket management
// PHP 5.5 compatible with sqlsrv

class TicketController {
    private $model;
    
    public function __construct() {
        require_once '../app/models/TicketModel.php';
        $this->model = new TicketModel();
    }
    
    public function create() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?controller=user&action=login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = array(
                'user_id' => $_SESSION['user_id'],
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'contact_info' => $_POST['contact_info'],
                'category_id' => (int)$_POST['category_id'],
                'priority_id' => (int)$_POST['priority_id'],
                'impact' => $_POST['impact'],
                'urgency' => $_POST['urgency']
            );
            
            $ticket_id = $this->model->createTicket($data);
            if ($ticket_id) {
                $success = 'Ticket creado exitosamente. ID: ' . $ticket_id;
                header('Location: ?controller=user&action=dashboard');
                exit;
            } else {
                $error = 'Error al crear el ticket';
            }
        }
        
        // Load options for form
        $categories = $this->model->getCategories();
        $priorities = $this->model->getPriorities();
        $departments = $this->model->getDepartments();
        
        include '../app/views/ticket/create.php';
    }
    
    public function view() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?controller=user&action=login');
            exit;
        }
        
        $ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$ticket_id) {
            header('Location: ?controller=user&action=dashboard');
            exit;
        }
        
        $ticket = $this->model->getTicketById($ticket_id);
        
        // Check if user owns or is agent/admin
        $role = $_SESSION['role'];
        $user_id = $_SESSION['user_id'];
        if ($role === 'user' && $ticket['user_id'] != $user_id) {
            $error = 'No tienes permiso para ver este ticket';
            include '../app/views/errors/error.php';
            exit;
        }
        
        include '../app/views/ticket/view.php';
    }
    
    public function update() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'user') {
            header('Location: ?controller=user&action=dashboard');
            exit;
        }
        
        $ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
        $status = $_POST['status'];
        $notes = $_POST['notes'];
        $user_id = $_SESSION['user_id'];
        
        if ($ticket_id && in_array($status, ['Abierto', 'En Progreso', 'Resuelto', 'Cerrado'])) {
            $success = $this->model->updateTicketStatus($ticket_id, $status, $notes, $user_id);
            if ($success) {
                $message = 'Ticket actualizado exitosamente';
            } else {
                $error = 'Error al actualizar el ticket';
            }
        } else {
            $error = 'Datos inválidos';
        }
        
        // Redirect back to view
        header("Location: ?controller=ticket&action=view&id=$ticket_id");
        exit;
    }
    
    // For escalation (simple: reassign to admin if not resolved)
    public function escalate() {
        if ($_SESSION['role'] !== 'agent') {
            header('Location: ?controller=agent&action=dashboard');
            exit;
        }
        
        $ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
        if ($ticket_id) {
            // Find admin (role 'admin', any dept)
            $db = getDBConnection();
            $admin_query = "SELECT id FROM users WHERE role = 'admin'";
            $admin_stmt = sqlsrv_query($db, $admin_query);
            if ($admin_stmt === false) {
                closeDBConnection($db);
                header("Location: ?controller=ticket&action=view&id=$ticket_id");
                exit;
            }
            $admin = sqlsrv_fetch_array($admin_stmt, SQLSRV_FETCH_ASSOC);
            sqlsrv_free_stmt($admin_stmt);
            closeDBConnection($db);
            
            if ($admin) {
                $this->model->assignTicket($ticket_id, $admin['id']);
                $this->model->addHistory($ticket_id, 'Ticket escalado a administrador', '', $_SESSION['user_id']);
            }
        }
        
        header("Location: ?controller=ticket&action=view&id=$ticket_id");
        exit;
    }
    
    public function __destruct() {
        // Model destruct called automatically
    }
}
?>