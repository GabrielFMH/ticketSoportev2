<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario - Sistema de Tickets</title>
    <link href="css/main.css" rel="stylesheet">
</head>
<body>
    <div class="header">
        <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?> (Usuario)</h1>
        <a href="?controller=user&action=logout" class="logout">Cerrar Sesión</a>
    </div>
    
    <div class="nav">
        <a href="?controller=ticket&action=create">Crear Nuevo Ticket</a>
        <a href="?controller=user&action=dashboard">Mis Tickets</a>
    </div>
    
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
                    <th>Fecha de Creación</th>
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
                        <td><?php echo $ticket['created_at'] ? $ticket['created_at']->format('d/m/Y H:i') : 'N/A'; ?></td>
                        <td><?php echo $ticket['updated_at'] ? $ticket['updated_at']->format('d/m/Y H:i') : 'N/A'; ?></td>
                        <td class="actions">
                            <a href="?controller=ticket&action=view&id=<?php echo $ticket['id']; ?>">Ver Detalles</a><br><br>
                            <?php if ($ticket['status'] === 'Abierto'): ?>
                                <a href="?controller=ticket&action=cancel&id=<?php echo $ticket['id']; ?>"
                                   onclick="return confirm('¿Estás seguro de que deseas cancelar este ticket?');"
                                   style="color: #DC3545; margin-left: 10px;">Cancelar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>