<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario - Sistema de Tickets</title>
    <link href="css/main.css" rel="stylesheet">
    <script src="js/user-notifications.js"></script>
</head>
<body>
    <div class="header">
        <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?> (Usuario)</h1>
        <div class="header-actions">
            <div class="notification-bell" onclick="toggleNotifications()">
                <span class="bell-icon">🔔</span>
                <?php if (!empty($recentUpdates) && count($recentUpdates) > 0): ?>
                    <span class="notification-badge"><?php echo count($recentUpdates); ?></span>
                <?php endif; ?>
                <div id="notificationDropdown" class="notification-dropdown">
                    <div class="notification-header">
                        <h3>Actualizaciones Recientes</h3>
                        <span class="notification-count"><?php echo count($recentUpdates); ?> actualizaciones</span>
                    </div>
                    <div class="notification-list">
                        <?php if (!empty($recentUpdates)): ?>
                            <?php foreach ($recentUpdates as $update): ?>
                                <div class="notification-item" onclick="viewTicket(<?php echo $update['ticket_id']; ?>)">
                                    <div class="notification-title">Ticket #<?php echo $update['ticket_id']; ?> - <?php echo htmlspecialchars($update['title']); ?></div>
                                    <div class="notification-action"><?php echo htmlspecialchars($update['action']); ?></div>
                                    <div class="notification-time"><?php echo $update['created_at'] ? $update['created_at']->format('d/m/Y H:i') : 'Fecha no disponible'; ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-notifications">No hay actualizaciones recientes</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <a href="?controller=user&action=logout" class="logout">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container">
        <div class="nav">
            <a href="?controller=ticket&action=create">Crear Nuevo Ticket</a>
            <a href="?controller=user&action=dashboard">Mis Tickets</a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success" style="margin: 20px 0;">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>
        
        <h2>Mis Tickets</h2>
        <?php if (empty($tickets)): ?>
            <div class="no-tickets">
                No tienes tickets registrados. <a href="?controller=ticket&action=create">Crea uno ahora</a>.
            </div>
        <?php else: ?>
            <table class="tickets-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Descripción</th>
                        <!-- <th>Información de Contacto</th>
                        <th>Categoría</th>
                        <th>Prioridad</th> -->
                        <th>Estado</th>
                        <!-- <th>Asignado a</th>
                        <th>Impacto</th>
                        <th>Urgencia</th> -->
                        <!-- <th>Fecha de Creación</th> -->
                        <th>Última Actualización</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?php echo $ticket['id']; ?></td>
                            <td><?php echo htmlspecialchars($ticket['title']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['description']); ?></td>
                            <!-- <td><?php echo htmlspecialchars($ticket['contact_info']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['category'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($ticket['priority'] ?? 'N/A'); ?></td>*/ -->
                            <td><span class="status status-<?php echo strtolower(str_replace(' ', '-', $ticket['status'])); ?>"><?php echo $ticket['status']; ?></span></td>
                            <!-- <td><?php echo htmlspecialchars($ticket['assignee'] ?? 'No asignado'); ?></td>
                            <td><?php echo htmlspecialchars($ticket['impact'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($ticket['urgency'] ?? 'N/A'); ?></td> -->
                            <!-- <td><?php echo $ticket['created_at'] ? $ticket['created_at']->format('d/m/Y H:i') : 'N/A'; ?></td> -->
                            <td><?php echo $ticket['updated_at'] ? $ticket['updated_at']->format('d/m/Y H:i') : 'N/A'; ?></td>
                            <td class="actions">
                                <a href="?controller=ticket&action=view&id=<?php echo $ticket['id']; ?>" class="btn btn-primary">Detalles</a>
                                <?php if ($ticket['status'] === 'Abierto'): ?>
                                    <a href="?controller=ticket&action=cancel&id=<?php echo $ticket['id']; ?>"
                                       onclick="return confirm('¿Estás seguro de que deseas cancelar este ticket?');"
                                       class="btn btn-primary">Cancelar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>