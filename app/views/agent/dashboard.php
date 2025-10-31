<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Agente - Sistema de Tickets</title>
    <link href="css/main.css" rel="stylesheet">
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="icon">🛡️</div>
            <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?> (Agente)</h1>
        </div>
        <div class="header-right">
            <a href="?controller=agent&action=dashboard" class="btn btn-primary">Mis Tickets Asignados</a>
            <a href="?controller=user&action=logout" class="btn btn-danger">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container">
        <h2>Ticket Asignados</h2>
        <?php if (empty($tickets)): ?>
            <div class="no-tickets">
                No tienes tickets asignados.
            </div>
        <?php else: ?>
            <table class="tickets-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th>Fecha de Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?php echo $ticket['id']; ?></td>
                            <td><?php echo htmlspecialchars($ticket['title']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['user_name']); ?></td>
                            <td><span class="status status-<?php echo strtolower(str_replace(' ', '-', $ticket['status'])); ?>"><?php echo $ticket['status']; ?></span></td>
                            <td><?php echo $ticket['created_at'] ? $ticket['created_at']->format('d/m/Y H:i') : 'N/A'; ?></td>
                            <td class="actions">
                                <?php if (!$ticket['assignee_id']): ?>
                                <form method="post" action="?controller=ticket&action=accept" style="display: inline;">
                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                    <button type="submit" class="btn btn-primary">Aceptar</button>
                                </form>
                                <?php endif; ?>
                                <?php if ($ticket['assignee_id'] == $_SESSION['user_id']): ?>
                                <form method="post" action="?controller=ticket&action=update" style="display: inline; margin-left: 5px;">
                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                    <select name="status" style="padding: 5px;">
                                        <option value="Abierto" <?php if ($ticket['status'] == 'Abierto') echo 'selected'; ?>>Abierto</option>
                                        <option value="En Progreso" <?php if ($ticket['status'] == 'En Progreso') echo 'selected'; ?>>En Progreso</option>
                                        <option value="Resuelto" <?php if ($ticket['status'] == 'Resuelto') echo 'selected'; ?>>Resuelto</option>
                                        <option value="Cerrado" <?php if ($ticket['status'] == 'Cerrado') echo 'selected'; ?>>Cerrado</option>
                                    </select>
                                    <input type="hidden" name="notes" value="">
                                    <button type="submit" class="btn btn-primary">Cambiar</button>
                                </form>
                                <?php endif; ?>
                                <br><br><a href="?controller=ticket&action=view&id=<?php echo $ticket['id']; ?>">Detalles</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>