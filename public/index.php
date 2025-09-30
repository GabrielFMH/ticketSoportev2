<?php
// Front controller for tickets system
// PHP 5.5 compatible

session_start();

// Suppress warnings from displaying (keep errors)
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

// Include config
require_once '../config/database.php';

// Simple autoloader for classes (basic for PHP 5.5)
function autoload($class) {
    $paths = array(
        '../app/controllers/',
        '../app/models/',
        '../app/'
    );
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
}
spl_autoload_register('autoload');

// Get route parameters
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'user';
$action = isset($_GET['action']) ? $_GET['action'] : 'login';

// Sanitize inputs
$controller = basename($controller); // Prevent directory traversal
$action = basename($action);

// Check authentication for protected routes
$publicActions = array('login', 'register', 'home'); // Add more as needed
$isPublic = in_array($action, $publicActions) || (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user' && $action === 'dashboard'); // Basic check, refine later

if (!isset($_SESSION['user_id']) && !in_array($action, $publicActions)) {
    $controller = 'user';
    $action = 'login';
    $_GET['controller'] = $controller;
    $_GET['action'] = $action;
}

// Load controller
$controllerClass = ucfirst($controller) . 'Controller';
$controllerFile = '../app/controllers/' . $controllerClass . '.php';

if (file_exists($controllerFile)) {
    $controllerObj = new $controllerClass();
    if (method_exists($controllerObj, $action)) {
        $controllerObj->$action();
    } else {
        // Default action or error
        error_page('Acción no encontrada');
    }
} else {
    error_page('Controlador no encontrado');
}

function error_page($message) {
    $title = 'Error - Sistema de Tickets';
    include '../app/views/errors/error.php';
    exit;
}

// For views, they will be included in controllers
?>