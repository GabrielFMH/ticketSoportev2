<?php
// ReportModel for analytics and reports
// PHP 5.5 compatible with sqlsrv (SQL Server)

class ReportModel {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    public function getTicketsPerAgent() {
        $stmt = sqlsrv_query($this->db, "EXEC Usp_Tik_S_ObtenerTicketsPorAgente");
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
        $stmt = sqlsrv_query($this->db, "EXEC Usp_Tik_S_ObtenerTicketsPorDepartamento");
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
        $stmt = sqlsrv_query($this->db, "EXEC Usp_Tik_S_ObtenerTiempoPromedioResolucion");
        if ($stmt === false) {
            return 0;
        }
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($stmt);
        $tiempo_promedio = $row ? $row['tiempo_promedio'] : 0;
        return $tiempo_promedio ? round($tiempo_promedio, 2) : 0;
    }
    
    public function getTicketsByStatus() {
        $stmt = sqlsrv_query($this->db, "EXEC Usp_Tik_S_ObtenerTicketsPorEstado");
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