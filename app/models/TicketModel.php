<?php
// TicketModel for database operations
// PHP 5.5 compatible with mysqli

class TicketModel {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    public function createTicket($data) {
        // $data: array with user_id, title, description, contact_info, category_id, priority_id, impact, urgency
        $user_id = (int)$data['user_id'];
        $title = $this->db->real_escape_string($data['title']);
        $description = $this->db->real_escape_string($data['description']);
        $contact_info = $this->db->real_escape_string($data['contact_info']);
        $category_id = (int)$data['category_id'];
        $priority_id = (int)$data['priority_id'];
        $impact = $this->db->real_escape_string($data['impact']);
        $urgency = $this->db->real_escape_string($data['urgency']);
        
        // Get department from category
        $dept_query = $this->db->prepare("SELECT department_id FROM categories WHERE id = ?");
        $dept_query->bind_param("i", $category_id);
        $dept_query->execute();
        $dept_result = $dept_query->get_result();
        $dept_row = $dept_result->fetch_assoc();
        $department_id = $dept_row ? $dept_row['department_id'] : null;
        $dept_query->close();
        
        // Insert ticket
        $query = "INSERT INTO tickets (user_id, department_id, title, description, contact_info, category_id, priority_id, impact, urgency) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iisssisss", $user_id, $department_id, $title, $description, $contact_info, $category_id, $priority_id, $impact, $urgency);
        
        if ($stmt->execute()) {
            $ticket_id = $this->db->insert_id;
            
            // Auto-assign: Find available agent in department (simple: first agent with <5 open tickets)
            if ($department_id) {
                $agent_query = "SELECT u.id FROM users u WHERE u.role = 'agent' AND u.department_id = ? AND (SELECT COUNT(*) FROM tickets t WHERE t.assignee_id = u.id AND t.status != 'Cerrado') < 5 LIMIT 1";
                $agent_stmt = $this->db->prepare($agent_query);
                $agent_stmt->bind_param("i", $department_id);
                $agent_stmt->execute();
                $agent_result = $agent_stmt->get_result();
                $agent = $agent_result->fetch_assoc();
                $agent_stmt->close();
                
                if ($agent) {
                    $this->assignTicket($ticket_id, $agent['id'], $department_id);
                }
            }
            
            // Add to history
            $this->addHistory($ticket_id, 'Ticket creado por usuario', null, $user_id);
            
            // Trigger notification
            $this->sendNotification($ticket_id, 'actualizacion', $this->getUserEmail($user_id));
            
            $stmt->close();
            return $ticket_id;
        }
        
        $stmt->close();
        return false;
    }
    
    public function getUserTickets($user_id) {
        $query = "SELECT t.id, t.title, t.status, t.created_at FROM tickets t WHERE t.user_id = ? ORDER BY t.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tickets = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
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
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $ticket_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $ticket = $result->fetch_assoc();
        $stmt->close();
        
        if ($ticket) {
            $ticket['history'] = $this->getTicketHistory($ticket_id);
        }
        
        return $ticket;
    }
    
    public function updateTicketStatus($ticket_id, $status, $notes, $user_id) {
        // Update status
        $query = "UPDATE tickets SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("si", $status, $ticket_id);
        $success = $stmt->execute();
        $stmt->close();
        
        if ($success) {
            // Add to history
            $this->addHistory($ticket_id, "Estado cambiado a: $status", $notes, $user_id);
            
            // Trigger notification to user and assignee
            $ticket = $this->getTicketById($ticket_id);
            $user_email = isset($ticket['user_email']) ? $ticket['user_email'] : '';
            $this->sendNotification($ticket_id, 'cambio_estado', $user_email);
            if ($ticket['assignee_id']) {
                $assignee_email = $this->getUserEmail($ticket['assignee_id']);
                $this->sendNotification($ticket_id, 'cambio_estado', $assignee_email);
            }
        }
        
        return $success;
    }
    
    private function addHistory($ticket_id, $action, $notes, $user_id) {
        $ticket_id = (int)$ticket_id;
        $action = $this->db->real_escape_string($action);
        $notes = $notes ? $this->db->real_escape_string($notes) : null;
        $user_id = $user_id ? (int)$user_id : null;
        
        $query = "INSERT INTO history (ticket_id, action, notes, user_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("issi", $ticket_id, $action, $notes, $user_id);
        $stmt->execute();
        $stmt->close();
    }
    
    private function getTicketHistory($ticket_id) {
        $query = "SELECT h.*, u.username FROM history h LEFT JOIN users u ON h.user_id = u.id WHERE h.ticket_id = ? ORDER BY h.timestamp ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $ticket_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $history = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $history;
    }
    
    public function assignTicket($ticket_id, $agent_id, $department_id = null) {
        $ticket_id = (int)$ticket_id;
        $agent_id = (int)$agent_id;
        $department_id = $department_id ? (int)$department_id : null;
        
        // Update ticket assignee
        $update_query = "UPDATE tickets SET assignee_id = ? WHERE id = ?";
        $update_stmt = $this->db->prepare($update_query);
        $update_stmt->bind_param("ii", $agent_id, $ticket_id);
        $update_success = $update_stmt->execute();
        $update_stmt->close();
        
        if ($update_success) {
            // Add assignment record
            $assign_query = "INSERT INTO assignments (ticket_id, agent_id, department_id) VALUES (?, ?, ?)";
            $assign_stmt = $this->db->prepare($assign_query);
            $assign_stmt->bind_param("iii", $ticket_id, $agent_id, $department_id);
            $assign_stmt->execute();
            $assign_stmt->close();
            
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
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : array();
    }
    
    public function getPriorities() {
        $query = "SELECT id, level, color FROM priorities ORDER BY id";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : array();
    }
    
    public function getDepartments() {
        $query = "SELECT id, name FROM departments ORDER BY name";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : array();
    }
    
    private function getUserEmail($user_id) {
        $query = "SELECT email FROM users WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user ? $user['email'] : '';
    }
    
    private function sendNotification($ticket_id, $type, $email) {
        if (!$email) return;
        
        $ticket = $this->getTicketById($ticket_id);
        $subject = "Actualización de Ticket #$ticket_id - $type";
        $message = "Su ticket '$ticket[title]' ha sido actualizado. Tipo: $type. Detalles: $ticket[description]";
        
        // Log notification
        $log_query = "INSERT INTO notifications (ticket_id, type, sent_to) VALUES (?, ?, ?)";
        $log_stmt = $this->db->prepare($log_query);
        $log_stmt->bind_param("iss", $ticket_id, $type, $email);
        $log_stmt->execute();
        $log_stmt->close();
        
        // Send email (simple mail())
        $headers = 'From: no-reply@tickets.com' . "\r\n" .
                   'Content-Type: text/plain; charset=UTF-8' . "\r\n";
        $sent = mail($email, $subject, $message, $headers);
        
        // Update status if failed
        if (!$sent) {
            $update_log = "UPDATE notifications SET status = 'fallido' WHERE ticket_id = ? AND sent_to = ? ORDER BY id DESC LIMIT 1";
            $update_stmt = $this->db->prepare($update_log);
            $update_stmt->bind_param("is", $ticket_id, $email);
            $update_stmt->execute();
            $update_stmt->close();
        }
    }
    
    public function __destruct() {
        closeDBConnection($this->db);
    }
}
?>