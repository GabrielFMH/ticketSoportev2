<?php
// TicketModel for database operations
// PHP 5.5 compatible with sqlsrv (SQL Server)

class TicketModel {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    public function createTicket($data) {
        // $data: array with user_id, title, description, contact_info, department_id
        $user_id = (int)$data['user_id'];
        $title = $data['title'];
        $description = $data['description'];
        $contact_info = $data['contact_info'];
        $department_id = isset($data['department_id']) ? $data['department_id'] : null;
        
        // For simplified form, use default values for required fields
        $priority_id = 1; // Default priority
        $impact = 'Medio'; // Default impact
        $urgency = 'Media'; // Default urgency
        
        // Create ticket using stored procedure (no category_id as it was removed)
        $params = array($user_id, $title, $description, $contact_info, $priority_id, $impact, $urgency, $department_id);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, "EXEC Usp_Tik_U_CrearTicket @usuario_id = ?, @titulo = ?, @descripcion = ?, @info_contacto = ?, @prioridad_id = ?, @impacto = ?, @urgencia = ?, @departamento_id = ?", $params_ref);
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
            $agent_stmt = sqlsrv_prepare($this->db, "EXEC Usp_Tik_S_ObtenerAgenteDisponible @departamento_id = ?", $agent_params_ref);
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
        $stmt = sqlsrv_prepare($this->db, "EXEC Usp_Tik_S_ObtenerTicketsUsuario @usuario_id = ?", $params_ref);
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
        $stmt = sqlsrv_prepare($this->db, "EXEC Usp_Tik_S_ObtenerTicketPorId @ticket_id = ?", $params_ref);
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
        $stmt = sqlsrv_prepare($this->db, "EXEC Usp_Tik_S_ObtenerActualizacionesTicketsRecientes @usuario_id = ?, @limite = ?", $params_ref);
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
        $stmt = sqlsrv_prepare($this->db, "EXEC Usp_Tik_U_ActualizarEstadoTicket @ticket_id = ?, @estado = ?, @usuario_id = ?, @notas = ?", $params_ref);
        $success = ($stmt !== false && sqlsrv_execute($stmt) !== false);
        sqlsrv_free_stmt($stmt);
        
        if ($success) {
            // Trigger notification to user and assignee
            $ticket = $this->getTicketById($ticket_id);
            $user_email = isset($ticket['correo_usuario']) ? $ticket['correo_usuario'] : '';
            $this->sendNotification($ticket_id, 'cambio_estado', $user_email);
            if (isset($ticket['asignado_a_id']) && $ticket['asignado_a_id']) {
                $assignee_email = $this->getUserEmail($ticket['asignado_a_id']);
                $this->sendNotification($ticket_id, 'cambio_estado', $assignee_email);
            }
        }
        
        return $success;
    }
    
    public function updateTicketDetails($ticket_id, $priority_id = null, $impact = null, $urgency = null) {
        try {
            $ticket_id = (int)$ticket_id;
            $priority_id = $priority_id ? (int)$priority_id : null;
            $impact = $impact ? $impact : null;
            $urgency = $urgency ? $urgency : null;
            
            // Check if any values actually changed
            $current_ticket = $this->getTicketById($ticket_id);
            if (!$current_ticket) {
                return false;
            }
            
            $has_changes = false;
            $change_log = array();
            
            if ($priority_id && $priority_id != $current_ticket['prioridad_id']) {
                $has_changes = true;
                $change_log[] = 'Prioridad';
            }
            if ($impact && $impact != $current_ticket['impacto']) {
                $has_changes = true;
                $change_log[] = 'Impacto';
            }
            if ($urgency && $urgency != $current_ticket['urgencia']) {
                $has_changes = true;
                $change_log[] = 'Urgencia';
            }
            
            if (!$has_changes) {
                return true; // No changes needed
            }
            
            // Try stored procedure first
            $params = array($ticket_id, $priority_id, $impact, $urgency);
            $params_ref = &$params;
            $stmt = sqlsrv_prepare($this->db, "EXEC Usp_Tik_U_DetallesTicket @ticket_id = ?, @prioridad_id = ?, @impacto = ?, @urgencia = ?", $params_ref);
            
            if ($stmt !== false && sqlsrv_execute($stmt) !== false) {
                sqlsrv_free_stmt($stmt);
                // Add history entry
                $this->addHistory($ticket_id, 'Detalles del ticket actualizados: ' . implode(', ', $change_log), null, $_SESSION['user_id']);
                return true;
            } else {
                sqlsrv_free_stmt($stmt);
                
                // Fallback to manual UPDATE if stored procedure doesn't exist
                $update_parts = array();
                $update_params = array();
                
                if ($priority_id) {
                    $update_parts[] = "prioridad_id = ?";
                    $update_params[] = $priority_id;
                }
                if ($impact) {
                    $update_parts[] = "impacto = ?";
                    $update_params[] = $impact;
                }
                if ($urgency) {
                    $update_parts[] = "urgencia = ?";
                    $update_params[] = $urgency;
                }
                
                if (!empty($update_parts)) {
                    $update_params[] = $ticket_id;
                    $update_params_ref = &$update_params;
                    
                    $update_query = "UPDATE tickets SET " . implode(', ', $update_parts) . " WHERE id = ?";
                    $update_stmt = sqlsrv_prepare($this->db, $update_query, $update_params_ref);
                    
                    if ($update_stmt !== false && sqlsrv_execute($update_stmt) !== false) {
                        sqlsrv_free_stmt($update_stmt);
                        // Add history entry
                        $this->addHistory($ticket_id, 'Detalles del ticket actualizados (manual): ' . implode(', ', $change_log), null, $_SESSION['user_id']);
                        return true;
                    } else {
                        sqlsrv_free_stmt($update_stmt);
                        return false;
                    }
                }
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error in updateTicketDetails: " . $e->getMessage());
            return false;
        }
    }
    
    public function addHistory($ticket_id, $action, $notes, $user_id) {
        $ticket_id = (int)$ticket_id;
        $notes = $notes ? $notes : null;
        $user_id = $user_id ? (int)$user_id : null;
        
        $params = array($ticket_id, $action, $notes, $user_id);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, "EXEC Usp_Tik_U_AgregarHistorial @ticket_id = ?, @accion = ?, @notas = ?, @usuario_id = ?", $params_ref);
        if ($stmt !== false) {
            sqlsrv_execute($stmt);
        }
        sqlsrv_free_stmt($stmt);
    }
    
    private function getTicketHistory($ticket_id) {
        $params = array($ticket_id);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, "EXEC Usp_Tik_S_ObtenerHistorialTicket @ticket_id = ?", $params_ref);
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
        $stmt = sqlsrv_prepare($this->db, "EXEC Usp_Tik_U_AsignarTicket @ticket_id = ?, @agente_id = ?, @departamento_id = ?", $params_ref);
        $update_success = ($stmt !== false && sqlsrv_execute($stmt) !== false);
        sqlsrv_free_stmt($stmt);
        
        if ($update_success) {
            // Notify agent
            $agent_email = $this->getUserEmail($agent_id);
            $this->sendNotification($ticket_id, 'actualizacion', $agent_email);
        }
        
        return $update_success;
    }
    
    public function getPriorities() {
        $stmt = sqlsrv_query($this->db, "EXEC Usp_Tik_S_ObtenerPrioridades");
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
        $stmt = sqlsrv_query($this->db, "EXEC Usp_Tik_S_ObtenerTodosDepartamentos");
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
        $stmt = sqlsrv_query($this->db, "EXEC Usp_Tik_S_ObtenerTodosAgentes");
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
        $stmt = sqlsrv_prepare($this->db, "EXEC Usp_Tik_U_DepartamentoAgente @agente_id = ?, @departamento_id = ?", $params_ref);
        $success = ($stmt !== false && sqlsrv_execute($stmt) !== false);
        sqlsrv_free_stmt($stmt);
        
        return $success;
    }
    
    private function getUserEmail($user_id) {
        $params = array($user_id);
        $params_ref = &$params;
        $stmt = sqlsrv_prepare($this->db, "EXEC Usp_Tik_S_ObtenerCorreoUsuario @usuario_id = ?", $params_ref);
        if ($stmt === false) {
            return '';
        }
        if (sqlsrv_execute($stmt) === false) {
            sqlsrv_free_stmt($stmt);
            return '';
        }
        $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($stmt);
        return $user ? $user['correo'] : '';
    }
    
    private function sendNotification($ticket_id, $type, $email) {
        if (!$email) return;
        
        $ticket = $this->getTicketById($ticket_id);
        $subject = "Actualización de Ticket #$ticket_id - $type";
        $message = "Su ticket '" . $ticket['titulo'] . "' ha sido actualizado. Tipo: $type. Detalles: " . $ticket['descripcion'];
        
        // Log notification using stored procedure
        $log_params = array($ticket_id, $type, $email);
        $log_params_ref = &$log_params;
        $log_stmt = sqlsrv_prepare($this->db, "EXEC Usp_Tik_U_RegistrarNotificacion @ticket_id = ?, @tipo = ?, @enviado_a = ?", $log_params_ref);
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
            $update_log = "UPDATE notificaciones SET estado = 'fallido' WHERE ticket_id = ? AND enviado_a = ? AND estado = 'enviado'";
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