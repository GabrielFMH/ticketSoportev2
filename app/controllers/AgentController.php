<?php
// AgentController for agent-specific functionality
// PHP 5.5 compatible with sqlsrv

class AgentController {
    private $model;
    
    public function __construct() {
        if ($_SESSION['role'] !== 'agent') {
            header('Location: ?controller=user&action=dashboard');
            exit;
        }
        require_once '../app/models/TicketModel.php';
        $this->model = new TicketModel();
    }
    
    public function dashboard() {
        $agent_id = $_SESSION['user_id'];
        
        $db = getDBConnection();
        
        // Get agent's department_id using stored procedure
        $dept_params = array($agent_id);
        $dept_params_ref = &$dept_params;
        $dept_stmt = sqlsrv_prepare($db, "EXEC sp_GetAgentDepartment @agent_id = ?", $dept_params_ref);
        if ($dept_stmt === false || sqlsrv_execute($dept_stmt) === false) {
            $department_id = null;
        } else {
            $dept_row = sqlsrv_fetch_array($dept_stmt, SQLSRV_FETCH_ASSOC);
            $department_id = $dept_row['department_id'];
            sqlsrv_free_stmt($dept_stmt);
        }
        
        if ($department_id) {
            // Get tickets from agent's department using stored procedure
            $params = array($department_id);
            $params_ref = &$params;
            $stmt = sqlsrv_prepare($db, "EXEC sp_GetAgentDepartmentTickets @department_id = ?", $params_ref);
            if ($stmt === false || sqlsrv_execute($stmt) === false) {
                $tickets = array();
            } else {
                $tickets = array();
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $tickets[] = $row;
                }
                sqlsrv_free_stmt($stmt);
            }
        } else {
            $tickets = array();
        }
        
        closeDBConnection($db);
        
        include '../app/views/agent/dashboard.php';
    }
    
    // Update and escalate handled in TicketController, but can add agent-specific if needed
    
    public function __destruct() {
        // Model destruct
    }
}
?>