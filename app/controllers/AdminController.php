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
    
    public function __destruct() {
        // Model destruct
    }
}
?>