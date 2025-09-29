<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Ticket - Sistema de Tickets</title>
    <style>
        body { font-family: Arial, sans-serif; background: #FFFFFF; color: #333; margin: 0; padding: 20px; }
        .header { background: #007BFF; color: #FFFFFF; padding: 15px; border-radius: 4px 4px 0 0; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .nav { margin-bottom: 20px; }
        .nav a { background: #007BFF; color: #FFFFFF; padding: 10px 15px; text-decoration: none; border-radius: 4px; margin-right: 10px; }
        .nav a:hover { background: #0056b3; }
        .logout { float: right; background: #6C757D; }
        .logout:hover { background: #545B62; }
        .ticket-details { background: #FFFFFF; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .detail-row { display: flex; margin-bottom: 15px; }
        .detail-label { font-weight: bold; width: 150px; color: #007BFF; }
        .detail-value { flex: 1; }
        .status { padding: 4px 8px; border-radius: 4px; color: #FFFFFF; font-weight: bold; }
        .status-abierto { background: #007BFF; }
        .status-en-progreso { background: #FFC107; color: #000; }
        .status-resuelto { background: #28A745; }
        .status-cerrado { background: #6C757D; }
        .history { background: #F8F9FA; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .history h3 { color: #007BFF; margin-top: 0; }
        .history-item { background: #FFFFFF; padding: 15px; margin-bottom: 10px; border-left: 4px solid #007BFF; border-radius: 4px; }
        .history-item .action { font-weight: bold; color: #007BFF; }
        .history-item .notes { margin-top: 5px; color: #6C757D; }
        .history-item .timestamp { font-size: 12px; color: #999; margin-top: 5px; }
        .update-form { background: #FFFFFF; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .update-form h3 { color: #007BFF; }
        label { display: block; margin: 10px 0 5px; font-weight: bold; }
        input[type="hidden"], select, textarea { width: 100%; padding: 10px; border: 1px solid #DDD; border-radius: 4px; box-sizing: border-box; margin-bottom: 15px; }
        textarea { height: 80px; resize: vertical; }
        .button-group { display: flex; gap: 10px; flex-wrap: wrap; }
        button, .escalate-btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button[type="submit"] { background: #007BFF; color: #FFFFFF; }
        button[type="submit"]:hover { background: #0056b3; }
        .escalate-btn { background: #FD7E14; color: #FFFFFF; }
        .escalate-btn:hover { background: #E36209; }
        .error { color: #DC3545; text-align: center; margin: 10px 0; padding: 10px; border-radius: 4px; background: #F8D7DA; }
        @media (max-width: 768px) { .detail-row { flex-direction: column; } .button-group { flex-direction: column; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>Ticket #<?php echo $ticket['id']; ?></h1>
        <a href="?controller=user&action=dashboard" class="nav">Volver al Dashboard</a>
        <a href="?controller=user&action=logout" class="logout">Cerrar Sesión</a>
    </div>
    
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
</body>
</html>