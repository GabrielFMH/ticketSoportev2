<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Ticket - Sistema de Tickets</title>
    <link href="css/main.css" rel="stylesheet">
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
            <!-- Categoría eliminada del sistema -->
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
                <span class="detail-value"><?php echo htmlspecialchars(isset($ticket['nombre_departamento']) ? $ticket['nombre_departamento'] : 'No asignado'); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Asignado a:</span>
                <span class="detail-value"><?php echo htmlspecialchars(isset($ticket['nombre_asignado']) ? $ticket['nombre_asignado'] : 'No asignado'); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Creado por:</span>
                <span class="detail-value"><?php echo htmlspecialchars($ticket['nombre_usuario']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Impacto:</span>
                <span class="detail-value"><?php echo htmlspecialchars($ticket['impact'] ?? 'N/A'); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Urgencia:</span>
                <span class="detail-value"><?php echo htmlspecialchars($ticket['urgency'] ?? 'N/A'); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Fecha de Creación:</span>
                <span class="detail-value"><?php echo $ticket['created_at'] ? $ticket['created_at']->format('d/m/Y H:i') : 'N/A'; ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Última Actualización:</span>
                <span class="detail-value"><?php echo $ticket['updated_at'] ? $ticket['updated_at']->format('d/m/Y H:i') : 'N/A'; ?></span>
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
                        <div class="timestamp">Por <?php echo htmlspecialchars(isset($item['nombre_usuario']) ? $item['nombre_usuario'] : 'Sistema'); ?> el <?php echo $item['timestamp'] ? $item['timestamp']->format('d/m/Y H:i') : 'N/A'; ?></div>
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
                        <button type="submit" class="btn btn-primary">Enviar Comentario</button>
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
                    
                    <!-- Categoría eliminada del sistema -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="priority_id">Prioridad:</label>
                            <select id="priority_id" name="priority_id">
                                <?php foreach ($priorities as $pri): ?>
                                    <option value="<?php echo $pri['id']; ?>" <?php echo ($pri['id'] == $ticket['priority_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($pri['nivel']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                        <div class="form-group">
                            <label for="department_id">Departamento:</label>
                            <select id="department_id" name="department_id">
                                <option value="">Seleccionar departamento</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>" <?php echo (isset($ticket['department_id']) && $dept['id'] == $ticket['department_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($dept['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="impact">Impacto:</label>
                            <select id="impact" name="impact">
                                <option value="Bajo" <?php echo ($ticket['impact'] == 'Bajo') ? 'selected' : ''; ?>>Bajo</option>
                                <option value="Medio" <?php echo ($ticket['impact'] == 'Medio') ? 'selected' : ''; ?>>Medio</option>
                                <option value="Alto" <?php echo ($ticket['impact'] == 'Alto') ? 'selected' : ''; ?>>Alto</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="urgency">Urgencia:</label>
                            <select id="urgency" name="urgency">
                                <option value="Baja" <?php echo ($ticket['urgency'] == 'Baja') ? 'selected' : ''; ?>>Baja</option>
                                <option value="Media" <?php echo ($ticket['urgency'] == 'Media') ? 'selected' : ''; ?>>Media</option>
                                <option value="Alta" <?php echo ($ticket['urgency'] == 'Alta') ? 'selected' : ''; ?>>Alta</option>
                            </select>
                        </div>
                    </div>
                    
                    <label for="notes">Notas Internas:</label>
                    <textarea name="notes" placeholder="Agregue notas o comentarios..."></textarea>
                    <div class="button-group">
                        <button type="submit" name="update_ticket">Actualizar</button>
                        <?php if ($_SESSION['role'] === 'agent' && $ticket['status'] === 'En Progreso'): ?>
                            <button type="submit" name="escalate_ticket" class="escalate-btn" onclick="return confirm('¿Escalar este ticket a administrador?')">Escalar</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>