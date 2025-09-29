<?php
// Database configuration for tickets system
// PHP 5.5 compatible with mysqli

function getDBConnection() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'tickets_db';
    
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        die('Error de conexión a la base de datos: ' . $conn->connect_error);
    }
    
    $conn->set_charset('utf8');
    
    return $conn;
}

// Close connection when done (call in models or at end)
function closeDBConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}
?>