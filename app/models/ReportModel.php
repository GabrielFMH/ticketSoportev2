<?php
// ReportModel for analytics and reports
// PHP 5.5 compatible with mysqli

class ReportModel {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    public function getTicketsPerCategory() {
        $query = "SELECT c.name as category, COUNT(t.id) as count FROM tickets t LEFT JOIN categories c ON t.category_id = c.id GROUP BY c.id, c.name ORDER BY count DESC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : array();
    }
    
    public function getTicketsPerAgent() {
        $query = "SELECT u.username as agent, COUNT(t.id) as count FROM tickets t LEFT JOIN users u ON t.assignee_id = u.id WHERE u.role = 'agent' GROUP BY u.id, u.username ORDER BY count DESC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : array();
    }
    
    public function getTicketsPerDepartment() {
        $query = "SELECT d.name as department, COUNT(t.id) as count FROM tickets t LEFT JOIN departments d ON t.department_id = d.id GROUP BY d.id, d.name ORDER BY count DESC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : array();
    }
    
    public function getAverageResolutionTime() {
        $query = "SELECT AVG(DATEDIFF(updated_at, created_at)) as avg_time FROM tickets WHERE status = 'Resuelto'";
        $result = $this->db->query($query);
        $row = $result ? $result->fetch_assoc() : array('avg_time' => 0);
        return $row['avg_time'] ? round($row['avg_time'], 2) : 0;
    }
    
    public function getTicketsByStatus() {
        $query = "SELECT status, COUNT(id) as count FROM tickets GROUP BY status";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : array();
    }
    
    public function __destruct() {
        closeDBConnection($this->db);
    }
}
?>