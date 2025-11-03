<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador - Sistema de Tickets</title>
    <link href="css/main.css" rel="stylesheet">
    <script src="js/admin-dashboard.js"></script>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="icon">👤</div>
            <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        </div>
        <div class="header-right">
            <a href="?controller=admin&action=dashboard" class="btn btn-primary">Reportes</a>
            <a href="?controller=admin&action=manageDepartments" class="btn btn-primary">Gestionar Departamentos</a>
            <a href="?controller=admin&action=manageAgents" class="btn btn-primary">Gestionar Agentes</a>
            <a href="?controller=admin&action=manageEscalatedTickets" class="btn btn-warning">Tickets Escalados</a>
            <a href="?controller=user&action=logout" class="btn btn-danger">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container">
        <div class="metrics">
            <div class="metric-card">
                <div class="metric-icon">⏱️</div>
                <div class="metric-value"><?php echo $avgResolutionTime; ?> días</div>
                <div class="metric-label">Tiempo Promedio de Resolución</div>
            </div>
            <div class="metric-card">
                <div class="metric-icon">📊</div>
                <div class="metric-value"><?php echo array_sum(array_column($ticketsByStatus, 'count')); ?></div>
                <div class="metric-label">Total de Tickets</div>
            </div>
        </div>
        
        <div class="section">
            <h3>Tiempos de Resolución Promedio</h3>
            <p>El tiempo promedio de resolución para tickets resueltos es de <?php echo $avgResolutionTime; ?> días.</p>
        </div>
        
        <div class="section">
            <h3>Tickets por Categoría</h3>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ticketsPerCategory as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['category']); ?></td>
                            <td><?php echo $item['count']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <h3>Tickets por Agente</h3>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Agente</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ticketsPerAgent as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['agent']); ?></td>
                            <td><?php echo $item['count']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <h3>Tickets por Departamento</h3>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Departamento</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ticketsPerDepartment as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['department']); ?></td>
                            <td><?php echo $item['count']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <h3>Gráfico de Tickets por Estado</h3>
            <canvas id="statusChart" width="600" height="300"></canvas>
        </div>
    </div>
</body>
</html>