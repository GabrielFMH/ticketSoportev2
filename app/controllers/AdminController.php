<?php
// AdminController for admin-specific functionality: reports and customization
// PHP 5.5 compatible with sqlsrv

class AdminController {
    private $reportModel;
    
    public function __construct() {
        if ($_SESSION['role'] !== 'admin') {
            header('Location: ?controller=user&action=dashboard');
            exit;
        }
        require_once '../app/models/ReportModel.php';
        $this->reportModel = new ReportModel();
    }
    
    public function dashboard() {
        $ticketsPerCategory = $this->reportModel->getTicketsPerCategory();
        $ticketsPerAgent = $this->reportModel->getTicketsPerAgent();
        $ticketsPerDepartment = $this->reportModel->getTicketsPerDepartment();
        $avgResolutionTime = $this->reportModel->getAverageResolutionTime();
        $ticketsByStatus = $this->reportModel->getTicketsByStatus();
        
        include '../app/views/admin/dashboard.php';
    }
    
    // Customization: Manage categories
    public function manageCategories() {
        $db = getDBConnection();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add'])) {
                $name = $_POST['name'];
                $description = $_POST['description'];
                $dept_id = isset($_POST['department_id']) ? (int)$_POST['department_id'] : null;
                $params = array($name, $description, $dept_id);
                $params_ref = &$params;
                $stmt = sqlsrv_prepare($db, "EXEC sp_CreateCategory @name = ?, @description = ?, @department_id = ?", $params_ref);
                if ($stmt === false || sqlsrv_execute($stmt) === false) {
                    $error = 'Error al agregar categoría: ' . print_r(sqlsrv_errors(), true);
                }
                sqlsrv_free_stmt($stmt);
            } elseif (isset($_POST['edit'])) {
                $id = (int)$_POST['id'];
                $name = $_POST['name'];
                $description = $_POST['description'];
                $dept_id = isset($_POST['department_id']) ? (int)$_POST['department_id'] : null;
                $params = array($id, $name, $description, $dept_id);
                $params_ref = &$params;
                $stmt = sqlsrv_prepare($db, "EXEC sp_UpdateCategory @id = ?, @name = ?, @description = ?, @department_id = ?", $params_ref);
                if ($stmt === false || sqlsrv_execute($stmt) === false) {
                    $error = 'Error al editar categoría: ' . print_r(sqlsrv_errors(), true);
                }
                sqlsrv_free_stmt($stmt);
            } elseif (isset($_POST['delete'])) {
                $id = (int)$_POST['id'];
                $params = array($id);
                $params_ref = &$params;
                $stmt = sqlsrv_prepare($db, "EXEC sp_DeleteCategory @id = ?", $params_ref);
                if ($stmt === false || sqlsrv_execute($stmt) === false) {
                    $error = 'Error al eliminar categoría: ' . print_r(sqlsrv_errors(), true);
                }
                sqlsrv_free_stmt($stmt);
            }
        }
        
        // Get all categories using stored procedure
        $categories_stmt = sqlsrv_query($db, "EXEC sp_GetAllCategories");
        if ($categories_stmt === false) {
            $categories = array();
        } else {
            $categories = array();
            while ($row = sqlsrv_fetch_array($categories_stmt, SQLSRV_FETCH_ASSOC)) {
                $categories[] = $row;
            }
            sqlsrv_free_stmt($categories_stmt);
        }
        
        // Get departments using stored procedure
        $depts_stmt = sqlsrv_query($db, "EXEC sp_GetAllDepartments");
        if ($depts_stmt === false) {
            $departments = array();
        } else {
            $departments = array();
            while ($row = sqlsrv_fetch_array($depts_stmt, SQLSRV_FETCH_ASSOC)) {
                $departments[] = $row;
            }
            sqlsrv_free_stmt($depts_stmt);
        }
        
        closeDBConnection($db);
        include '../app/views/admin/manage_categories.php';
    }
    
    // Manage Agents
    public function manageAgents() {
        require_once '../app/models/TicketModel.php';
        $ticketModel = new TicketModel();
        $db = getDBConnection();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['edit_department'])) {
                $agent_id = (int)$_POST['agent_id'];
                $department_id = isset($_POST['department_id']) && $_POST['department_id'] !== '' ? (int)$_POST['department_id'] : null;
                
                if ($ticketModel->updateAgentDepartment($agent_id, $department_id)) {
                    $success = 'Departamento actualizado correctamente.';
                } else {
                    $error = 'Error al actualizar el departamento.';
                }
            }
        }
        
        // Get all agents
        $agents = $ticketModel->getAllAgents();
        
        // Get departments for dropdown
        $departments = $ticketModel->getDepartments();
        
        closeDBConnection($db);
        include '../app/views/admin/manage_agents.php';
    }
    
    // Manage Escalated Tickets
    public function manageEscalatedTickets() {
        require_once '../app/models/TicketModel.php';
        $ticketModel = new TicketModel();
        $db = getDBConnection();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['reassign_ticket'])) {
                $ticket_id = (int)$_POST['ticket_id'];
                $new_agent_id = isset($_POST['new_agent_id']) && $_POST['new_agent_id'] !== '' ? (int)$_POST['new_agent_id'] : null;
                $new_department_id = isset($_POST['new_department_id']) && $_POST['new_department_id'] !== '' ? (int)$_POST['new_department_id'] : null;
                $notes = isset($_POST['reassignment_notes']) ? $_POST['reassignment_notes'] : '';
                
                // First reassign department if specified
                if ($new_department_id) {
                    $ticketModel->assignTicket($ticket_id, null, $new_department_id);
                }
                
                // Then reassign to new agent if specified
                if ($new_agent_id) {
                    $current_ticket = $ticketModel->getTicketById($ticket_id);
                    $department_id = $new_department_id ? $new_department_id : $current_ticket['department_id'];
                    $ticketModel->assignTicket($ticket_id, $new_agent_id, $department_id);
                }
                
                // Add history entry
                $ticketModel->addHistory($ticket_id, 'Ticket reasignado por administrador', $notes, $_SESSION['user_id']);
                
                // Clear escalation status by adding resolution entry
                $clear_stmt = sqlsrv_prepare($db, "EXEC sp_ClearEscalationStatus @ticket_id = ?", array($ticket_id));
                if ($clear_stmt !== false) {
                    sqlsrv_execute($clear_stmt);
                    sqlsrv_free_stmt($clear_stmt);
                }
                
                $success = 'Ticket reasignado correctamente.';
            }
        }
        
        // Get escalated tickets (tickets in 'En Progreso' status that have been escalated)
        $escalated_stmt = sqlsrv_query($db, "EXEC sp_GetEscalatedTickets");
        if ($escalated_stmt === false) {
            $escalatedTickets = array();
        } else {
            $escalatedTickets = array();
            while ($row = sqlsrv_fetch_array($escalated_stmt, SQLSRV_FETCH_ASSOC)) {
                $escalatedTickets[] = $row;
            }
            sqlsrv_free_stmt($escalated_stmt);
        }
        
        // Get all agents for reassignment
        $agents = $ticketModel->getAllAgents();
        
        // Get departments for reassignment
        $departments = $ticketModel->getDepartments();
        
        closeDBConnection($db);
        include '../app/views/admin/manage_escalated_tickets.php';
    }
    
    public function __destruct() {
        // Model destruct
    }
}
?>