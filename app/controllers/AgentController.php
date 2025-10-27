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
        
        // Get assigned tickets
        $db = getDBConnection();
        $query = "SELECT t.id, t.title, t.status, t.created_at, u.username as user_name FROM tickets t LEFT JOIN users u ON t.user_id = u.id WHERE t.assignee_id = ? ORDER BY t.created_at DESC";
        $params = array($agent_id);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($db, $query, $params_ref);
        if ($stmt === false || sqlsrv_execute($stmt) === false) {
            $tickets = array();
        } else {
            $tickets = array();
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $tickets[] = $row;
            }
            sqlsrv_free_stmt($stmt);
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