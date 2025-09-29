<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Agente - Sistema de Tickets</title>
    <style>
        body { font-family: Arial, sans-serif; background: #FFFFFF; color: #333; margin: 0; padding: 20px; }
        .header { background: #007BFF; color: #FFFFFF; padding: 15px; border-radius: 4px 4px 0 0; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .nav { margin-bottom: 20px; }
        .nav a { background: #007BFF; color: #FFFFFF; padding: 10px 15px; text-decoration: none; border-radius: 4px; margin-right: 10px; }
        .nav a:hover { background: #0056b3; }
        .logout { float: right; background: #6C757D; }
        .logout:hover { background: #545B62; }
        .tickets-table { width: 100%; border-collapse: collapse; margin-top: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .tickets-table th, .tickets-table td { padding: 12px; text-align: left; border-bottom: 1px solid #DDD; }
        .tickets-table th { background: #F8F9FA; color: #495057; }
        .tickets-table tr:hover { background: #F8F9FA; }
        .status { padding: 4px 8px; border-radius: 4px; color: #FFFFFF; font-size: 12px; }
        .status-abierto { background: #007BFF; }
        .status-en-progreso { background: #FFC107; color: #000; }
        .status-resuelto { background: #28A745; }
        .status-cerrado { background: #6C757D; }
        .no-tickets { text-align: center; color: #6C757D; padding: 40px; }
        .actions a { color: #007BFF; text-decoration: none; margin-right: 10px; }
        .actions a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?> (Agente)</h1>
        <a href="?controller=agent&action=dashboard">Mis Tickets Asignados</a>
        <a href="?controller=user&action=logout" class="logout">Cerrar Sesión</a>
    </div>
    
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
                        <td><?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?></td>
                        <td class="actions">
                            <a href="?controller=ticket&action=view&id=<?php echo $ticket['id']; ?>">Ver/Actualizar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>