<?php
// AgentController for agent-specific functionality
// PHP 5.5 compatible with sqlsrv

class AgentController {
    private $model;
    
    public function __construct() {
        if ($_SESSION['role'] !== 'agente') {
            header('Location: ?controller=user&action=dashboard');
            exit;
        }
        require_once '../app/models/TicketModel.php';
        $this->model = new TicketModel();
    }
    
    public function dashboard() {
        $agent_id = $_SESSION['user_id'];
        $tickets = array();
        
        $db = getDBConnection();
        
        // Get agent's department_id using stored procedure
        $dept_params = array($agent_id);
        $dept_params_ref = &$dept_params;
        $dept_stmt = sqlsrv_prepare($db, "EXEC Usp_Tik_S_ObtenerDepartamentoAgente @agente_id = ?", $dept_params_ref);
        if ($dept_stmt === false || sqlsrv_execute($dept_stmt) === false) {
            $department_id = null;
        } else {
            $dept_row = sqlsrv_fetch_array($dept_stmt, SQLSRV_FETCH_ASSOC);
            $department_id = $dept_row['departamento_id'];
            sqlsrv_free_stmt($dept_stmt);
        }
        
        // Get department tickets (tickets available for assignment)
        if ($department_id) {
            $dept_params = array($department_id);
            $dept_params_ref = &$dept_params;
            $dept_stmt = sqlsrv_prepare($db, "EXEC Usp_Tik_S_ObtenerTicketsDepartamentoAgente @departamento_id = ?", $dept_params_ref);
            if ($dept_stmt !== false && sqlsrv_execute($dept_stmt) !== false) {
                while ($row = sqlsrv_fetch_array($dept_stmt, SQLSRV_FETCH_ASSOC)) {
                    $tickets[] = $row;
                }
                sqlsrv_free_stmt($dept_stmt);
            }
        }
        
        // Get tickets specifically assigned to this agent (regardless of department)
        $agent_params = array($agent_id);
        $agent_params_ref = &$agent_params;
        $agent_stmt = sqlsrv_prepare($db, "EXEC Usp_Tik_S_ObtenerTicketsAgente @agente_id = ?", $agent_params_ref);
        if ($agent_stmt !== false && sqlsrv_execute($agent_stmt) !== false) {
            while ($row = sqlsrv_fetch_array($agent_stmt, SQLSRV_FETCH_ASSOC)) {
                $tickets[] = $row;
            }
            sqlsrv_free_stmt($agent_stmt);
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