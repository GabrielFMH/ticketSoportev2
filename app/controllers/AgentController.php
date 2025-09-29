<?php
// AgentController for agent-specific functionality
// PHP 5.5 compatible

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
        $query = "SELECT t.id, t.title, t.status, t.created_at, u.username as user_name FROM tickets t LEFT JOIN users u ON t.user_id = u.id WHERE t.assignee_id = ? ORDER BY t.created_at DESC";
        $stmt = $this->model->db->prepare($query); // Access db from model, but since private, use getDBConnection
        $db = getDBConnection();
        $stmt = $db->prepare($query);
        $stmt->bind_param("i", $agent_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tickets = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        closeDBConnection($db);
        
        include '../app/views/agent/dashboard.php';
    }
    
    // Update and escalate handled in TicketController, but can add agent-specific if needed
    
    public function __destruct() {
        // Model destruct
    }
}
?>