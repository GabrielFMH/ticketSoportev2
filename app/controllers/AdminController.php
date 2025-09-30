<?php
// AdminController for admin-specific functionality: reports and customization
// PHP 5.5 compatible with sqlsrv

class AdminController {
    private $reportModel;
    
    public function __construct() {
        if ($_SESSION['role'] !== 'admin') {
            header('Location: ?controller=user&action=dashboard');
            exit;
        }
        require_once '../app/models/ReportModel.php';
        $this->reportModel = new ReportModel();
    }
    
    public function dashboard() {
        $ticketsPerCategory = $this->reportModel->getTicketsPerCategory();
        $ticketsPerAgent = $this->reportModel->getTicketsPerAgent();
        $ticketsPerDepartment = $this->reportModel->getTicketsPerDepartment();
        $avgResolutionTime = $this->reportModel->getAverageResolutionTime();
        $ticketsByStatus = $this->reportModel->getTicketsByStatus();
        
        include '../app/views/admin/dashboard.php';
    }
    
    // Customization: Manage categories
    public function manageCategories() {
        $db = getDBConnection();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add'])) {
                $name = $_POST['name'];
                $description = $_POST['description'];
                $dept_id = isset($_POST['department_id']) ? (int)$_POST['department_id'] : null;
                $query = "INSERT INTO categories (name, description, department_id) VALUES (?, ?, ?)";
                $params = array($name, $description, $dept_id);
                $params_ref = &$params;
                $stmt = sqlsrv_prepare($db, $query, $params_ref);
                if ($stmt === false || sqlsrv_execute($stmt) === false) {
                    $error = 'Error al agregar categoría: ' . print_r(sqlsrv_errors(), true);
                }
                sqlsrv_free_stmt($stmt);
            } elseif (isset($_POST['edit'])) {
                $id = (int)$_POST['id'];
                $name = $_POST['name'];
                $description = $_POST['description'];
                $dept_id = isset($_POST['department_id']) ? (int)$_POST['department_id'] : null;
                $query = "UPDATE categories SET name = ?, description = ?, department_id = ? WHERE id = ?";
                $params = array($name, $description, $dept_id, $id);
                $params_ref = &$params;
                $stmt = sqlsrv_prepare($db, $query, $params_ref);
                if ($stmt === false || sqlsrv_execute($stmt) === false) {
                    $error = 'Error al editar categoría: ' . print_r(sqlsrv_errors(), true);
                }
                sqlsrv_free_stmt($stmt);
            } elseif (isset($_POST['delete'])) {
                $id = (int)$_POST['id'];
                $query = "DELETE FROM categories WHERE id = ?";
                $params = array($id);
                $params_ref = &$params;
                $stmt = sqlsrv_prepare($db, $query, $params_ref);
                if ($stmt === false || sqlsrv_execute($stmt) === false) {
                    $error = 'Error al eliminar categoría: ' . print_r(sqlsrv_errors(), true);
                }
                sqlsrv_free_stmt($stmt);
            }
        }
        
        // Get all categories
        $categories_query = "SELECT c.*, d.name as dept_name FROM categories c LEFT JOIN departments d ON c.department_id = d.id ORDER BY c.name";
        $categories_stmt = sqlsrv_query($db, $categories_query);
        if ($categories_stmt === false) {
            $categories = array();
        } else {
            $categories = array();
            while ($row = sqlsrv_fetch_array($categories_stmt, SQLSRV_FETCH_ASSOC)) {
                $categories[] = $row;
            }
            sqlsrv_free_stmt($categories_stmt);
        }
        
        // Get departments for form
        $depts_query = "SELECT id, name FROM departments ORDER BY name";
        $depts_stmt = sqlsrv_query($db, $depts_query);
        if ($depts_stmt === false) {
            $departments = array();
        } else {
            $departments = array();
            while ($row = sqlsrv_fetch_array($depts_stmt, SQLSRV_FETCH_ASSOC)) {
                $departments[] = $row;
            }
            sqlsrv_free_stmt($depts_stmt);
        }
        
        closeDBConnection($db);
        include '../app/views/admin/manage_categories.php';
    }
    
    // Similar for departments, priorities if needed, but basic for now
    
    public function __destruct() {
        // Model destruct
    }
}
?>