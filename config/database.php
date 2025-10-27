<?php
// Database configuration for tickets system
// PHP 5.5 compatible with sqlsrv (SQL Server)

function getDBConnection() {
    $serverName = 'DESKTOP-8S7B0KM\SQLEXPRESS';
    $connectionInfo = array(
        "Database" => "tickets_db",
        "UID" => "",
        "PWD" => "",
        "CharacterSet" => "UTF-8"
    );
    
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    
    if ($conn === false) {
        die('Error de conexión a la base de datos: ' . print_r(sqlsrv_errors(), true));
    }
    
    return $conn;
}

// Close connection when done (call in models or at end)
function closeDBConnection($conn) {
    if ($conn) {
        sqlsrv_close($conn);
    }
}
?>