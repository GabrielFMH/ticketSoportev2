<?php
// ReportModel for analytics and reports
// PHP 5.5 compatible with sqlsrv (SQL Server)

class ReportModel {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    public function getTicketsPerCategory() {
        $query = "SELECT c.name as category, COUNT(t.id) as count FROM tickets t LEFT JOIN categories c ON t.category_id = c.id GROUP BY c.id, c.name ORDER BY count DESC";
        $stmt = sqlsrv_query($this->db, $query);
        if ($stmt === false) {
            return array();
        }
        $results = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $results[] = $row;
        }
        sqlsrv_free_stmt($stmt);
        return $results;
    }
    
    public function getTicketsPerAgent() {
        $query = "SELECT u.username as agent, COUNT(t.id) as count FROM tickets t LEFT JOIN users u ON t.assignee_id = u.id WHERE u.role = 'agent' GROUP BY u.id, u.username ORDER BY count DESC";
        $stmt = sqlsrv_query($this->db, $query);
        if ($stmt === false) {
            return array();
        }
        $results = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $results[] = $row;
        }
        sqlsrv_free_stmt($stmt);
        return $results;
    }
    
    public function getTicketsPerDepartment() {
        $query = "SELECT d.name as department, COUNT(t.id) as count FROM tickets t LEFT JOIN departments d ON t.department_id = d.id GROUP BY d.id, d.name ORDER BY count DESC";
        $stmt = sqlsrv_query($this->db, $query);
        if ($stmt === false) {
            return array();
        }
        $results = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $results[] = $row;
        }
        sqlsrv_free_stmt($stmt);
        return $results;
    }
    
    public function getAverageResolutionTime() {
        $query = "SELECT AVG(DATEDIFF(DAY, created_at, updated_at)) as avg_time FROM tickets WHERE status = 'Resuelto'";
        $stmt = sqlsrv_query($this->db, $query);
        if ($stmt === false) {
            return 0;
        }
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($stmt);
        $avg_time = $row ? $row['avg_time'] : 0;
        return $avg_time ? round($avg_time, 2) : 0;
    }
    
    public function getTicketsByStatus() {
        $query = "SELECT status, COUNT(id) as count FROM tickets GROUP BY status";
        $stmt = sqlsrv_query($this->db, $query);
        if ($stmt === false) {
            return array();
        }
        $results = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $results[] = $row;
        }
        sqlsrv_free_stmt($stmt);
        return $results;
    }
    
    public function __destruct() {
        closeDBConnection($this->db);
    }
}
?>