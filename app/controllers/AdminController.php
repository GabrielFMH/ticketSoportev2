<?php
// AdminController for admin-specific functionality: reports and customization
// PHP 5.5 compatible

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
                $name = $db->real_escape_string($_POST['name']);
                $description = $db->real_escape_string($_POST['description']);
                $dept_id = isset($_POST['department_id']) ? (int)$_POST['department_id'] : null;
                $query = "INSERT INTO categories (name, description, department_id) VALUES ('$name', '$description', " . ($dept_id ? $dept_id : 'NULL') . ")";
                if (!$db->query($query)) {
                    $error = 'Error al agregar categoría: ' . $db->error;
                }
            } elseif (isset($_POST['edit'])) {
                $id = (int)$_POST['id'];
                $name = $db->real_escape_string($_POST['name']);
                $description = $db->real_escape_string($_POST['description']);
                $dept_id = isset($_POST['department_id']) ? (int)$_POST['department_id'] : null;
                $query = "UPDATE categories SET name = '$name', description = '$description', department_id = " . ($dept_id ? $dept_id : 'NULL') . " WHERE id = $id";
                if (!$db->query($query)) {
                    $error = 'Error al editar categoría: ' . $db->error;
                }
            } elseif (isset($_POST['delete'])) {
                $id = (int)$_POST['id'];
                $query = "DELETE FROM categories WHERE id = $id";
                if (!$db->query($query)) {
                    $error = 'Error al eliminar categoría: ' . $db->error;
                }
            }
        }
        
        // Get all categories
        $categories_query = "SELECT c.*, d.name as dept_name FROM categories c LEFT JOIN departments d ON c.department_id = d.id ORDER BY c.name";
        $categories_result = $db->query($categories_query);
        $categories = $categories_result ? $categories_result->fetch_all(MYSQLI_ASSOC) : array();
        
        // Get departments for form
        $depts_query = "SELECT id, name FROM departments ORDER BY name";
        $depts_result = $db->query($depts_query);
        $departments = $depts_result ? $depts_result->fetch_all(MYSQLI_ASSOC) : array();
        
        closeDBConnection($db);
        include '../app/views/admin/manage_categories.php';
    }
    
    // Similar for departments, priorities if needed, but basic for now
    
    public function __destruct() {
        // Model destruct
    }
}
?>