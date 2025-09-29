<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Ticket - Sistema de Tickets</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #F8F9FA; color: #333; margin: 0; padding: 0; }
        .header { background: linear-gradient(135deg, #007BFF, #0056b3); color: #FFFFFF; padding: 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 10px rgba(0,123,255,0.2); }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .icon { width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .header h1 { margin: 0; font-size: 28px; }
        .header-right { display: flex; gap: 10px; }
        .btn { padding: 10px 20px; border: none; border-radius: 25px; cursor: pointer; text-decoration: none; font-weight: bold; transition: all 0.3s; display: inline-block; }
        .btn-primary { background: #FFFFFF; color: #007BFF; }
        .btn-primary:hover { background: #E9ECEF; transform: translateY(-2px); }
        .btn-danger { background: #DC3545; color: #FFFFFF; }
        .btn-danger:hover { background: #C82333; transform: translateY(-2px); }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .ticket-details { background: #FFFFFF; padding: 25px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .detail-row { display: flex; margin-bottom: 15px; align-items: center; }
        .detail-label { font-weight: bold; width: 150px; color: #007BFF; font-size: 16px; }
        .detail-value { flex: 1; padding: 8px; background: #F8F9FA; border-radius: 8px; border-left: 4px solid #007BFF; }
        .status { padding: 6px 12px; border-radius: 20px; color: #FFFFFF; font-weight: bold; font-size: 14px; }
        .status-abierto { background: #007BFF; }
        .status-en-progreso { background: #FFC107; color: #000; }
        .status-resuelto { background: #28A745; }
        .status-cerrado { background: #6C757D; }
        .history { background: #FFFFFF; padding: 25px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .history h3 { color: #007BFF; margin-top: 0; font-size: 24px; border-bottom: 2px solid #007BFF; padding-bottom: 10px; }
        .history-item { background: #F8F9FA; padding: 15px; margin-bottom: 15px; border-left: 4px solid #007BFF; border-radius: 8px; transition: box-shadow 0.3s; }
        .history-item:hover { box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .history-item .action { font-weight: bold; color: #007BFF; font-size: 16px; margin-bottom: 5px; }
        .history-item .notes { margin-top: 5px; color: #6C757D; background: #FFFFFF; padding: 10px; border-radius: 5px; }
        .history-item .timestamp { font-size: 12px; color: #999; margin-top: 10px; font-style: italic; }
        .update-form { background: #FFFFFF; padding: 25px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .update-form h3 { color: #007BFF; font-size: 24px; margin-bottom: 20px; border-bottom: 2px solid #007BFF; padding-bottom: 10px; }
        label { display: block; margin: 15px 0 5px; font-weight: bold; color: #495057; }
        input[type="hidden"], select, textarea { width: 100%; padding: 12px; border: 1px solid #DEE2E6; border-radius: 8px; box-sizing: border-box; margin-bottom: 15px; font-size: 16px; transition: border-color 0.3s; }
        input[type="hidden"], select, textarea:focus { border-color: #007BFF; outline: none; box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25); }
        textarea { height: 100px; resize: vertical; }
        .button-group { display: flex; gap: 10px; flex-wrap: wrap; }
        button, .escalate-btn { padding: 12px 24px; border: none; border-radius: 25px; cursor: pointer; font-weight: bold; font-size: 16px; transition: all 0.3s; }
        button[type="submit"] { background: #007BFF; color: #FFFFFF; }
        button[type="submit"]:hover { background: #0056b3; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,123,255,0.3); }
        .escalate-btn { background: #FD7E14; color: #FFFFFF; }
        .escalate-btn:hover { background: #E36209; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(253,126,20,0.3); }
        .error { color: #DC3545; text-align: center; margin: 10px 0; padding: 12px; border-radius: 8px; background: #F8D7DA; border: 1px solid #F5C6CB; }
        @media (max-width: 768px) { .detail-row { flex-direction: column; align-items: flex-start; } .detail-label { width: auto; margin-bottom: 5px; } .button-group { flex-direction: column; } .header { flex-direction: column; gap: 10px; text-align: center; } .header-right { justify-content: center; } }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="icon">🎫</div>
            <h1>Ticket #<?php echo $ticket['id']; ?></h1>
        </div>
        <div class="header-right">
            <a href="<?php echo ($_SESSION['role'] === 'user' ? '?controller=user&action=dashboard' : ($_SESSION['role'] === 'agent' ? '?controller=agent&action=dashboard' : '?controller=admin&action=dashboard')); ?>" class="btn btn-primary">Volver al Dashboard</a>
            <a href="?controller=user&action=logout" class="btn btn-danger">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="ticket-details">
            <div class="detail-row">
                <span class="detail-label">Título:</span>
                <span class="detail-value"><?php echo htmlspecialchars($ticket['title']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Descripción:</span>
                <span class="detail-value"><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Contacto:</span>
                <span class="detail-value"><?php echo htmlspecialchars($ticket['contact_info']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Categoría:</span>
                <span class="detail-value"><?php echo htmlspecialchars($ticket['category_name']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Prioridad:</span>
                <span class="detail-value"><?php echo htmlspecialchars($ticket['priority_level']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Estado:</span>
                <span class="detail-value"><span class="status status-<?php echo strtolower(str_replace(' ', '-', $ticket['status'])); ?>"><?php echo $ticket['status']; ?></span></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Departamento:</span>
                <span class="detail-value"><?php echo htmlspecialchars(isset($ticket['department_name']) ? $ticket['department_name'] : 'No asignado'); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Asignado a:</span>
                <span class="detail-value"><?php echo htmlspecialchars(isset($ticket['assignee_name']) ? $ticket['assignee_name'] : 'No asignado'); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Creado por:</span>
                <span class="detail-value"><?php echo htmlspecialchars($ticket['user_name']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Fecha de Creación:</span>
                <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Última Actualización:</span>
                <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($ticket['updated_at'])); ?></span>
            </div>
        </div>
        
        <div class="history">
            <h3>Historial del Ticket</h3>
            <?php if (empty($ticket['history'])): ?>
                <p>No hay historial aún.</p>
            <?php else: ?>
                <?php foreach ($ticket['history'] as $item): ?>
                    <div class="history-item">
                        <div class="action"><?php echo htmlspecialchars($item['action']); ?></div>
                        <?php if ($item['notes']): ?>
                            <div class="notes"><?php echo nl2br(htmlspecialchars($item['notes'])); ?></div>
                        <?php endif; ?>
                        <div class="timestamp">Por <?php echo htmlspecialchars(isset($item['username']) ? $item['username'] : 'Sistema'); ?> el <?php echo date('d/m/Y H:i', strtotime($item['timestamp'])); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if ($_SESSION['role'] === 'user'): ?>
            <div class="update-form">
                <h3>Agregar Comentario</h3>
                <form method="POST" action="?controller=ticket&action=update">
                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                    <label for="notes">Comentario:</label>
                    <textarea name="notes" placeholder="Agregue un comentario o información adicional..."></textarea>
                    <div class="button-group">
                        <button type="submit">Enviar Comentario</button>
                    </div>
                </form>
            </div>
        <?php elseif (in_array($_SESSION['role'], ['agent', 'admin'])): ?>
            <div class="update-form">
                <h3>Actualizar Ticket</h3>
                <form method="POST" action="?controller=ticket&action=update">
                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                    <label for="status">Nuevo Estado:</label>
                    <select name="status" required>
                        <option value="">Seleccione estado</option>
                        <option value="Abierto" <?php echo $ticket['status'] === 'Abierto' ? 'selected' : ''; ?>>Abierto</option>
                        <option value="En Progreso" <?php echo $ticket['status'] === 'En Progreso' ? 'selected' : ''; ?>>En Progreso</option>
                        <option value="Resuelto" <?php echo $ticket['status'] === 'Resuelto' ? 'selected' : ''; ?>>Resuelto</option>
                        <option value="Cerrado" <?php echo $ticket['status'] === 'Cerrado' ? 'selected' : ''; ?>>Cerrado</option>
                    </select>
                    <label for="notes">Notas Internas:</label>
                    <textarea name="notes" placeholder="Agregue notas o comentarios..."></textarea>
                    <div class="button-group">
                        <button type="submit">Actualizar</button>
                        <?php if ($_SESSION['role'] === 'agent' && $ticket['status'] === 'En Progreso'): ?>
                            <button type="submit" formaction="?controller=ticket&action=escalate" class="escalate-btn" onclick="return confirm('¿Escalar este ticket a administrador?')">Escalar</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>