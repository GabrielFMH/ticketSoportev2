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
                'department_id' => !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null
            );
            
            $ticket_id = $this->model->createTicket($data);
            if ($ticket_id) {
                $success = 'Ticket creado exitosamente. ID: ' . $ticket_id;
                header("Location: ?controller=user&action=dashboard&success=" . urlencode($success));
                exit;
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
        
        // Load options for form (only needed for agents/admins)
        if (in_array($role, ['agent', 'admin'])) {
            $categories = $this->model->getCategories();
            $priorities = $this->model->getPriorities();
        } else {
            // Initialize empty arrays to avoid undefined variable errors
            $categories = array();
            $priorities = array();
        }
        
        include '../app/views/ticket/view.php';
    }
    
    public function update() {
        try {
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'user') {
                header('Location: ?controller=user&action=dashboard');
                exit;
            }
            
            // Check if this is an escalation
            if (isset($_POST['escalate_ticket']) && $_SESSION['role'] === 'agent') {
                $escalate_ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
                $this->escalate($escalate_ticket_id);
                exit;
            }
            
            $ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
            $status = $_POST['status'];
            $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
            $user_id = $_SESSION['user_id'];
            
            // Only agents/admins can update these fields
            $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $priority_id = isset($_POST['priority_id']) ? (int)$_POST['priority_id'] : null;
            $impact = isset($_POST['impact']) ? $_POST['impact'] : null;
            $urgency = isset($_POST['urgency']) ? $_POST['urgency'] : null;
            
            if ($ticket_id && in_array($status, ['Abierto', 'En Progreso', 'Resuelto', 'Cerrado'])) {
                // Update basic status
                $success = $this->model->updateTicketStatus($ticket_id, $status, $notes, $user_id);
                
                // Update additional fields if provided (for agents/admins only)
                if (in_array($_SESSION['role'], ['agent', 'admin']) && ($category_id || $priority_id || $impact || $urgency)) {
                    try {
                        $this->model->updateTicketDetails($ticket_id, $category_id, $priority_id, $impact, $urgency);
                    } catch (Exception $e) {
                        // Log error but don't fail the whole update
                        error_log("Error updating ticket details: " . $e->getMessage());
                    }
                }
                
                if ($success) {
                    $message = 'Ticket actualizado exitosamente';
                } else {
                    $error = 'Error al actualizar el estado del ticket';
                }
            } else {
                $error = 'Datos inválidos';
            }
        } catch (Exception $e) {
            error_log("Error in ticket update: " . $e->getMessage());
            $error = 'Error interno del servidor al actualizar el ticket';
        }
        
        // Redirect back to view
        header("Location: ?controller=ticket&action=view&id=$ticket_id");
        exit;
    }
    
    public function accept() {
        if ($_SESSION['role'] !== 'agent') {
            header('Location: ?controller=agent&action=dashboard');
            exit;
        }
        
        $ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
        if ($ticket_id) {
            // First assign the ticket to the agent
            $this->model->assignTicket($ticket_id, $_SESSION['user_id']);
            
            // Then update status to "En Progreso"
            $this->model->updateTicketStatus($ticket_id, 'En Progreso', 'Ticket aceptado por agente', $_SESSION['user_id']);
            
            header("Location: ?controller=ticket&action=view&id=$ticket_id");
            exit;
        }
        
        header('Location: ?controller=agent&action=dashboard');
        exit;
    }
    
    public function cancel() {
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
        
        // Check if user owns the ticket and it's in "Abierto" status
        $role = $_SESSION['role'];
        $user_id = $_SESSION['user_id'];
        if ($role === 'user' && ($ticket['user_id'] != $user_id || $ticket['status'] !== 'Abierto')) {
            $error = 'No tienes permiso para cancelar este ticket';
            include '../app/views/errors/error.php';
            exit;
        }
        
        // Cancel the ticket by setting status to "Cerrado"
        $success = $this->model->updateTicketStatus($ticket_id, 'Cerrado', 'Ticket cancelado por el usuario', $user_id);
        
        if ($success) {
            $message = 'Ticket cancelado exitosamente';
        } else {
            $error = 'Error al cancelar el ticket';
        }
        
        // Redirect back to dashboard
        header('Location: ?controller=user&action=dashboard');
        exit;
    }
    
    // For escalation (simple: reassign to admin if not resolved)
    public function escalate($ticket_id = null) {
        if ($_SESSION['role'] !== 'agent') {
            header('Location: ?controller=agent&action=dashboard');
            exit;
        }
        
        if ($ticket_id === null) {
            $ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
        }
        
        if ($ticket_id) {
            // Find admin (role 'admin', any dept)
            $db = getDBConnection();
            $admin_stmt = sqlsrv_query($db, "EXEC sp_GetAdmin");
            if ($admin_stmt === false) {
                closeDBConnection($db);
                header("Location: ?controller=ticket&action=view&id=$ticket_id");
                exit;
            }
            $admin = sqlsrv_fetch_array($admin_stmt, SQLSRV_FETCH_ASSOC);
            sqlsrv_free_stmt($admin_stmt);
            closeDBConnection($db);
            
            if ($admin) {
                // First reassign to admin
                $this->model->assignTicket($ticket_id, $admin['id']);
                
                // Then add explicit escalation history entry
                $this->model->addHistory($ticket_id, 'Ticket escalado por agente', 'Escalado por el agente debido a complejidad o necesidad de supervisión', $_SESSION['user_id']);
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