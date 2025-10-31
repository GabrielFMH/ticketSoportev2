<?php
// ReportModel for analytics and reports
// PHP 5.5 compatible with sqlsrv (SQL Server)

class ReportModel {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    public function getTicketsPerCategory() {
        $stmt = sqlsrv_query($this->db, "EXEC sp_GetTicketsPerCategory");
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
        $stmt = sqlsrv_query($this->db, "EXEC sp_GetTicketsPerAgent");
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
        $stmt = sqlsrv_query($this->db, "EXEC sp_GetTicketsPerDepartment");
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
        $stmt = sqlsrv_query($this->db, "EXEC sp_GetAverageResolutionTime");
        if ($stmt === false) {
            return 0;
        }
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($stmt);
        $avg_time = $row ? $row['avg_time'] : 0;
        return $avg_time ? round($avg_time, 2) : 0;
    }
    
    public function getTicketsByStatus() {
        $stmt = sqlsrv_query($this->db, "EXEC sp_GetTicketsByStatus");
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