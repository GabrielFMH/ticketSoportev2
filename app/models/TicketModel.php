<?php
// TicketModel for database operations
// PHP 5.5 compatible with sqlsrv (SQL Server)

class TicketModel {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    public function createTicket($data) {
        // $data: array with user_id, title, description, contact_info, category_id, priority_id, impact, urgency, department_id
        $user_id = (int)$data['user_id'];
        $title = $data['title'];
        $description = $data['description'];
        $contact_info = $data['contact_info'];
        $category_id = (int)$data['category_id'];
        $priority_id = (int)$data['priority_id'];
        $impact = $data['impact'];
        $urgency = $data['urgency'];
        $department_id = isset($data['department_id']) ? $data['department_id'] : null;
        
        // If no department selected by user, get department from category using stored procedure
        if (!$department_id) {
            $dept_params = array($category_id);
            $dept_params_ref = &$dept_params;
            $dept_stmt = sqlsrv_prepare($this->db, "EXEC sp_GetDepartmentFromCategory @category_id = ?", $dept_params_ref);
            if ($dept_stmt === false) {
                die('Error preparing dept query: ' . print_r(sqlsrv_errors(), true));
            }
            sqlsrv_execute($dept_stmt);
            $dept_row = sqlsrv_fetch_array($dept_stmt, SQLSRV_FETCH_ASSOC);
            $department_id = $dept_row ? $dept_row['department_id'] : null;
            sqlsrv_free_stmt($dept_stmt);
        }
        
        // Create ticket using stored procedure
        $params = array($user_id, $title, $description, $contact_info, $category_id, $priority_id, $impact, $urgency, $department_id);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, "EXEC sp_CreateTicket @user_id = ?, @title = ?, @description = ?, @contact_info = ?, @category_id = ?, @priority_id = ?, @impact = ?, @urgency = ?, @department_id = ?", $params_ref);
        if ($stmt === false) {
            die('Error preparing insert: ' . print_r(sqlsrv_errors(), true));
        }
        if (sqlsrv_execute($stmt) === false) {
            die('Error executing insert: ' . print_r(sqlsrv_errors(), true));
        }
        sqlsrv_free_stmt($stmt);
        
        // Get insert ID
        $id_stmt = sqlsrv_query($this->db, "SELECT SCOPE_IDENTITY() as id");
        if ($id_stmt === false) {
            die('Error getting ID: ' . print_r(sqlsrv_errors(), true));
        }
        $id_row = sqlsrv_fetch_array($id_stmt, SQLSRV_FETCH_ASSOC);
        $ticket_id = $id_row['id'];
        sqlsrv_free_stmt($id_stmt);
        
        // Auto-assign: Find available agent in department using stored procedure
        if ($department_id) {
            $agent_params = array($department_id);
            $agent_params_ref = &$agent_params;
            $agent_stmt = sqlsrv_prepare($this->db, "EXEC sp_GetAvailableAgent @department_id = ?", $agent_params_ref);
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
        
        // Add to history using stored procedure
        $this->addHistory($ticket_id, 'Ticket creado por usuario', null, $user_id);
        
        // Trigger notification
        $this->sendNotification($ticket_id, 'actualizacion', $this->getUserEmail($user_id));
        
        return $ticket_id;
    }
    
    public function getUserTickets($user_id) {
        $params = array($user_id);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, "EXEC sp_GetUserTickets @user_id = ?", $params_ref);
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
        $params = array($ticket_id);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, "EXEC sp_GetTicketById @ticket_id = ?", $params_ref);
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
    
    public function getRecentTicketUpdates($user_id, $limit = 5) {
        $params = array($user_id, $limit);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, "EXEC sp_GetRecentTicketUpdates @user_id = ?, @limit = ?", $params_ref);
        if ($stmt === false) {
            return array();
        }
        if (sqlsrv_execute($stmt) === false) {
            sqlsrv_free_stmt($stmt);
            return array();
        }
        $updates = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $updates[] = $row;
        }
        sqlsrv_free_stmt($stmt);
        return $updates;
    }
    
    public function updateTicketStatus($ticket_id, $status, $notes, $user_id) {
        // Update status using stored procedure
        $params = array($ticket_id, $status, $user_id, $notes);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, "EXEC sp_UpdateTicketStatus @ticket_id = ?, @status = ?, @user_id = ?, @notes = ?", $params_ref);
        $success = ($stmt !== false && sqlsrv_execute($stmt) !== false);
        sqlsrv_free_stmt($stmt);
        
        if ($success) {
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
    
    public function addHistory($ticket_id, $action, $notes, $user_id) {
        $ticket_id = (int)$ticket_id;
        //$action = $action;
        $notes = $notes ? $notes : null;
        $user_id = $user_id ? (int)$user_id : null;
        
        $params = array($ticket_id, $action, $notes, $user_id);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, "EXEC sp_AddHistory @ticket_id = ?, @action = ?, @notes = ?, @user_id = ?", $params_ref);
        if ($stmt !== false) {
            sqlsrv_execute($stmt);
        }
        sqlsrv_free_stmt($stmt);
    }
    
    private function getTicketHistory($ticket_id) {
        $params = array($ticket_id);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, "EXEC sp_GetTicketHistory @ticket_id = ?", $params_ref);
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
        
        // Assign ticket using stored procedure
        $params = array($ticket_id, $agent_id, $department_id);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, "EXEC sp_AssignTicket @ticket_id = ?, @agent_id = ?, @department_id = ?", $params_ref);
        $update_success = ($stmt !== false && sqlsrv_execute($stmt) !== false);
        sqlsrv_free_stmt($stmt);
        
        if ($update_success) {
            // Notify agent
            $agent_email = $this->getUserEmail($agent_id);
            $this->sendNotification($ticket_id, 'actualizacion', $agent_email);
        }
        
        return $update_success;
    }
    
    public function getCategories() {
        $stmt = sqlsrv_query($this->db, "EXEC sp_GetCategories");
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
        $stmt = sqlsrv_query($this->db, "EXEC sp_GetPriorities");
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
        $stmt = sqlsrv_query($this->db, "EXEC sp_GetAllDepartments");
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
    
    public function getAllAgents() {
        $stmt = sqlsrv_query($this->db, "EXEC sp_GetAllAgents");
        if ($stmt === false) {
            return array();
        }
        $agents = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $agents[] = $row;
        }
        sqlsrv_free_stmt($stmt);
        return $agents;
    }
    
    public function updateAgentDepartment($agent_id, $department_id) {
        $agent_id = (int)$agent_id;
        $department_id = $department_id ? (int)$department_id : null;
        
        $params = array($agent_id, $department_id);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, "EXEC sp_UpdateAgentDepartment @agent_id = ?, @department_id = ?", $params_ref);
        $success = ($stmt !== false && sqlsrv_execute($stmt) !== false);
        sqlsrv_free_stmt($stmt);
        
        return $success;
    }
    
    private function getUserEmail($user_id) {
        $params = array($user_id);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, "EXEC sp_GetUserEmail @user_id = ?", $params_ref);
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
        
        // Log notification using stored procedure
        $log_params = array($ticket_id, $type, $email);
        $log_params_ref = &$log_params;
        $log_stmt = sqlsrv_prepare($this->db, "EXEC sp_LogNotification @ticket_id = ?, @type = ?, @sent_to = ?", $log_params_ref);
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