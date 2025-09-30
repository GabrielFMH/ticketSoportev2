<?php
// TicketModel for database operations
// PHP 5.5 compatible with sqlsrv (SQL Server)

class TicketModel {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    public function createTicket($data) {
        // $data: array with user_id, title, description, contact_info, category_id, priority_id, impact, urgency
        $user_id = (int)$data['user_id'];
        $title = $data['title'];
        $description = $data['description'];
        $contact_info = $data['contact_info'];
        $category_id = (int)$data['category_id'];
        $priority_id = (int)$data['priority_id'];
        $impact = $data['impact'];
        $urgency = $data['urgency'];
        
        // Get department from category
        $dept_query = "SELECT department_id FROM categories WHERE id = ?";
        $dept_params = array($category_id);
        $dept_params_ref = &$dept_params;
        $dept_stmt = sqlsrv_prepare($this->db, $dept_query, $dept_params_ref);
        if ($dept_stmt === false) {
            die('Error preparing dept query: ' . print_r(sqlsrv_errors(), true));
        }
        sqlsrv_execute($dept_stmt);
        $dept_row = sqlsrv_fetch_array($dept_stmt, SQLSRV_FETCH_ASSOC);
        $department_id = $dept_row ? $dept_row['department_id'] : null;
        sqlsrv_free_stmt($dept_stmt);
        
        // Insert ticket
        $query = "INSERT INTO tickets (user_id, department_id, title, description, contact_info, category_id, priority_id, impact, urgency) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = array($user_id, $department_id, $title, $description, $contact_info, $category_id, $priority_id, $impact, $urgency);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, $query, $params_ref);
        if ($stmt === false) {
            die('Error preparing insert: ' . print_r(sqlsrv_errors(), true));
        }
        if (sqlsrv_execute($stmt) === false) {
            die('Error executing insert: ' . print_r(sqlsrv_errors(), true));
        }
        sqlsrv_free_stmt($stmt);
        
        // Get insert ID
        $id_query = "SELECT SCOPE_IDENTITY() as id";
        $id_stmt = sqlsrv_query($this->db, $id_query);
        if ($id_stmt === false) {
            die('Error getting ID: ' . print_r(sqlsrv_errors(), true));
        }
        $id_row = sqlsrv_fetch_array($id_stmt, SQLSRV_FETCH_ASSOC);
        $ticket_id = $id_row['id'];
        sqlsrv_free_stmt($id_stmt);
        
        // Auto-assign: Find available agent in department (simple: first agent with <5 open tickets)
        if ($department_id) {
            $agent_query = "SELECT u.id FROM users u WHERE u.role = 'agent' AND u.department_id = ? AND (SELECT COUNT(*) FROM tickets t WHERE t.assignee_id = u.id AND t.status != 'Cerrado') < 5";
            $agent_params = array($department_id);
            $agent_params_ref = &$agent_params;
            $agent_stmt = sqlsrv_prepare($this->db, $agent_query, $agent_params_ref);
            if ($agent_stmt === false) {
                die('Error preparing agent query: ' . print_r(sqlsrv_errors(), true));
            }
            sqlsrv_execute($agent_stmt);
            $agent_row = sqlsrv_fetch_array($agent_stmt, SQLSRV_FETCH_ASSOC);
            $agent_id = $agent_row ? $agent_row['id'] : null;
            sqlsrv_free_stmt($agent_stmt);
            
            if ($agent_id) {
                $this->assignTicket($ticket_id, $agent_id, $department_id);
            }
        }
        
        // Add to history
        $this->addHistory($ticket_id, 'Ticket creado por usuario', null, $user_id);
        
        // Trigger notification
        $this->sendNotification($ticket_id, 'actualizacion', $this->getUserEmail($user_id));
        
        return $ticket_id;
    }
    
    public function getUserTickets($user_id) {
        $query = "SELECT t.id, t.title, t.status, t.created_at FROM tickets t WHERE t.user_id = ? ORDER BY t.created_at DESC";
        $params = array($user_id);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, $query, $params_ref);
        if ($stmt === false) {
            return array();
        }
        if (sqlsrv_execute($stmt) === false) {
            sqlsrv_free_stmt($stmt);
            return array();
        }
        $tickets = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $tickets[] = $row;
        }
        sqlsrv_free_stmt($stmt);
        return $tickets;
    }
    
    public function getTicketById($ticket_id) {
        $query = "SELECT t.*, u.username as user_name, u.email as user_email, c.name as category_name, p.level as priority_level, a.username as assignee_name, d.name as department_name
                  FROM tickets t
                  LEFT JOIN users u ON t.user_id = u.id
                  LEFT JOIN categories c ON t.category_id = c.id
                  LEFT JOIN priorities p ON t.priority_id = p.id
                  LEFT JOIN users a ON t.assignee_id = a.id
                  LEFT JOIN departments d ON t.department_id = d.id
                  WHERE t.id = ?";
        $params = array($ticket_id);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, $query, $params_ref);
        if ($stmt === false) {
            return null;
        }
        if (sqlsrv_execute($stmt) === false) {
            sqlsrv_free_stmt($stmt);
            return null;
        }
        $ticket = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($stmt);
        
        if ($ticket) {
            $ticket['history'] = $this->getTicketHistory($ticket_id);
        }
        
        return $ticket;
    }
    
    public function updateTicketStatus($ticket_id, $status, $notes, $user_id) {
        // Update status
        $query = "UPDATE tickets SET status = ?, updated_at = GETDATE() WHERE id = ?";
        $params = array($status, $ticket_id);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, $query, $params_ref);
        $success = ($stmt !== false && sqlsrv_execute($stmt) !== false);
        sqlsrv_free_stmt($stmt);
        
        if ($success) {
            // Add to history
            $this->addHistory($ticket_id, "Estado cambiado a: $status", $notes, $user_id);
            
            // Trigger notification to user and assignee
            $ticket = $this->getTicketById($ticket_id);
            $user_email = isset($ticket['user_email']) ? $ticket['user_email'] : '';
            $this->sendNotification($ticket_id, 'cambio_estado', $user_email);
            if (isset($ticket['assignee_id']) && $ticket['assignee_id']) {
                $assignee_email = $this->getUserEmail($ticket['assignee_id']);
                $this->sendNotification($ticket_id, 'cambio_estado', $assignee_email);
            }
        }
        
        return $success;
    }
    
    private function addHistory($ticket_id, $action, $notes, $user_id) {
        $ticket_id = (int)$ticket_id;
        $action = $action;
        $notes = $notes ? $notes : null;
        $user_id = $user_id ? (int)$user_id : null;
        
        $query = "INSERT INTO history (ticket_id, action, notes, user_id) VALUES (?, ?, ?, ?)";
        $params = array($ticket_id, $action, $notes, $user_id);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, $query, $params_ref);
        if ($stmt !== false) {
            sqlsrv_execute($stmt);
        }
        sqlsrv_free_stmt($stmt);
    }
    
    private function getTicketHistory($ticket_id) {
        $query = "SELECT h.*, u.username FROM history h LEFT JOIN users u ON h.user_id = u.id WHERE h.ticket_id = ? ORDER BY h.timestamp ASC";
        $params = array($ticket_id);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, $query, $params_ref);
        if ($stmt === false) {
            return array();
        }
        if (sqlsrv_execute($stmt) === false) {
            sqlsrv_free_stmt($stmt);
            return array();
        }
        $history = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $history[] = $row;
        }
        sqlsrv_free_stmt($stmt);
        return $history;
    }
    
    public function assignTicket($ticket_id, $agent_id, $department_id = null) {
        $ticket_id = (int)$ticket_id;
        $agent_id = (int)$agent_id;
        $department_id = $department_id ? (int)$department_id : null;
        
        // Update ticket assignee
        $update_query = "UPDATE tickets SET assignee_id = ? WHERE id = ?";
        $update_params = array($agent_id, $ticket_id);
        $update_params_ref = &$update_params;
        $update_stmt = sqlsrv_prepare($this->db, $update_query, $update_params_ref);
        $update_success = ($update_stmt !== false && sqlsrv_execute($update_stmt) !== false);
        sqlsrv_free_stmt($update_stmt);
        
        if ($update_success) {
            // Add assignment record
            $assign_query = "INSERT INTO assignments (ticket_id, agent_id, department_id) VALUES (?, ?, ?)";
            $assign_params = array($ticket_id, $agent_id, $department_id);
            $assign_params_ref = &$assign_params;
            $assign_stmt = sqlsrv_prepare($this->db, $assign_query, $assign_params_ref);
            if ($assign_stmt !== false) {
                sqlsrv_execute($assign_stmt);
            }
            sqlsrv_free_stmt($assign_stmt);
            
            // Add to history
            $this->addHistory($ticket_id, 'Ticket asignado', null, $agent_id);
            
            // Notify agent
            $agent_email = $this->getUserEmail($agent_id);
            $this->sendNotification($ticket_id, 'actualizacion', $agent_email);
        }
        
        return $update_success;
    }
    
    public function getCategories() {
        $query = "SELECT id, name FROM categories ORDER BY name";
        $stmt = sqlsrv_query($this->db, $query);
        if ($stmt === false) {
            return array();
        }
        $categories = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $categories[] = $row;
        }
        sqlsrv_free_stmt($stmt);
        return $categories;
    }
    
    public function getPriorities() {
        $query = "SELECT id, level, color FROM priorities ORDER BY id";
        $stmt = sqlsrv_query($this->db, $query);
        if ($stmt === false) {
            return array();
        }
        $priorities = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $priorities[] = $row;
        }
        sqlsrv_free_stmt($stmt);
        return $priorities;
    }
    
    public function getDepartments() {
        $query = "SELECT id, name FROM departments ORDER BY name";
        $stmt = sqlsrv_query($this->db, $query);
        if ($stmt === false) {
            return array();
        }
        $departments = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $departments[] = $row;
        }
        sqlsrv_free_stmt($stmt);
        return $departments;
    }
    
    private function getUserEmail($user_id) {
        $query = "SELECT email FROM users WHERE id = ?";
        $params = array($user_id);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, $query, $params_ref);
        if ($stmt === false) {
            return '';
        }
        if (sqlsrv_execute($stmt) === false) {
            sqlsrv_free_stmt($stmt);
            return '';
        }
        $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($stmt);
        return $user ? $user['email'] : '';
    }
    
    private function sendNotification($ticket_id, $type, $email) {
        if (!$email) return;
        
        $ticket = $this->getTicketById($ticket_id);
        $subject = "Actualización de Ticket #$ticket_id - $type";
        $message = "Su ticket '" . $ticket['title'] . "' ha sido actualizado. Tipo: $type. Detalles: " . $ticket['description'];
        
        // Log notification
        $log_query = "INSERT INTO notifications (ticket_id, type, sent_to) VALUES (?, ?, ?)";
        $log_params = array($ticket_id, $type, $email);
        $log_params_ref = &$log_params;
        $log_stmt = sqlsrv_prepare($this->db, $log_query, $log_params_ref);
        if ($log_stmt !== false) {
            sqlsrv_execute($log_stmt);
        }
        sqlsrv_free_stmt($log_stmt);
        
        // Send email (simple mail())
        $headers = 'From: no-reply@tickets.com' . "\r\n" .
                   'Content-Type: text/plain; charset=UTF-8' . "\r\n";
        $sent = mail($email, $subject, $message, $headers);
        
        // Update status if failed
        if (!$sent) {
            $update_log = "UPDATE notifications SET status = 'fallido' WHERE ticket_id = ? AND sent_to = ? AND status = 'enviado'";
            $update_params = array($ticket_id, $email);
            $update_params_ref = &$update_params;
            $update_stmt = sqlsrv_prepare($this->db, $update_log, $update_params_ref);
            if ($update_stmt !== false) {
                sqlsrv_execute($update_stmt);
            }
            sqlsrv_free_stmt($update_stmt);
        }
    }
    
    public function __destruct() {
        closeDBConnection($this->db);
    }
}
?>