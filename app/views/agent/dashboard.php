<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Agente - Sistema de Tickets</title>
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
        .tickets-table { width: 100%; border-collapse: collapse; margin-top: 20px; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .tickets-table th { background: linear-gradient(135deg, #007BFF, #0056b3); color: #FFFFFF; padding: 15px; text-align: left; font-weight: bold; }
        .tickets-table td { padding: 15px; background: #FFFFFF; border-bottom: 1px solid #DEE2E6; }
        .tickets-table tr:hover { background: #F8F9FA; }
        .status { padding: 6px 12px; border-radius: 20px; color: #FFFFFF; font-weight: bold; font-size: 14px; }
        .status-abierto { background: #007BFF; }
        .status-en-progreso { background: #FFC107; color: #000; }
        .status-resuelto { background: #28A745; }
        .status-cerrado { background: #6C757D; }
        .no-tickets { text-align: center; color: #6C757D; padding: 60px; background: #FFFFFF; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); margin-top: 20px; }
        .actions a { color: #007BFF; text-decoration: none; padding: 8px 16px; border: 1px solid #007BFF; border-radius: 20px; transition: all 0.3s; }
        .actions a:hover { background: #007BFF; color: #FFFFFF; transform: translateY(-2px); }
        h2 { color: #007BFF; font-size: 24px; margin-bottom: 20px; }
        @media (max-width: 768px) { .header { flex-direction: column; gap: 10px; text-align: center; } .header-right { justify-content: center; } .tickets-table { font-size: 14px; } .tickets-table th, .tickets-table td { padding: 10px; } }
    </style>
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
                            <td><?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?></td>
                            <td class="actions">
                                <a href="?controller=ticket&action=view&id=<?php echo $ticket['id']; ?>">Ver/Actualizar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>