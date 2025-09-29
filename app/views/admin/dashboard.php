<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador - Sistema de Tickets</title>
    <style>
        body { font-family: Arial, sans-serif; background: #FFFFFF; color: #333; margin: 0; padding: 20px; }
        .header { background: #007BFF; color: #FFFFFF; padding: 15px; border-radius: 4px 4px 0 0; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .nav { margin-bottom: 20px; }
        .nav a { background: #007BFF; color: #FFFFFF; padding: 10px 15px; text-decoration: none; border-radius: 4px; margin-right: 10px; }
        .nav a:hover { background: #0056b3; }
        .logout { float: right; background: #6C757D; }
        .logout:hover { background: #545B62; }
        .metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .metric-card { background: #FFFFFF; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .metric-value { font-size: 2em; color: #007BFF; font-weight: bold; }
        .metric-label { color: #6C757D; }
        .report-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .report-table th, .report-table td { padding: 12px; text-align: left; border-bottom: 1px solid #DDD; }
        .report-table th { background: #F8F9FA; color: #495057; }
        .report-table tr:hover { background: #F8F9FA; }
        .section { margin-bottom: 30px; }
        .section h3 { color: #007BFF; border-bottom: 2px solid #007BFF; padding-bottom: 5px; }
        canvas { max-width: 100%; height: 300px; }
    </style>
    <script>
        // Simple bar chart using canvas for tickets by status (basic for PHP 5.5, no libs)
        window.onload = function() {
            var canvas = document.getElementById('statusChart');
            var ctx = canvas.getContext('2d');
            var statuses = <?php echo json_encode(array_column($ticketsByStatus, 'status')); ?>;
            var counts = <?php echo json_encode(array_column($ticketsByStatus, 'count')); ?>;
            var maxCount = Math.max(...counts);
            var barWidth = canvas.width / statuses.length;
            
            ctx.fillStyle = '#007BFF';
            for (var i = 0; i < statuses.length; i++) {
                var height = (counts[i] / maxCount) * 200;
                ctx.fillRect(i * barWidth, canvas.height - height, barWidth - 10, height);
                ctx.fillStyle = '#000';
                ctx.font = '12px Arial';
                ctx.fillText(statuses[i], i * barWidth + 5, canvas.height - 5);
                ctx.fillText(counts[i], i * barWidth + 5, canvas.height - height - 5);
            }
        };
    </script>
</head>
<body>
    <div class="header">
        <h1>Panel de Administrador</h1>
        <a href="?controller=admin&action=dashboard">Reportes</a>
        <a href="?controller=admin&action=manageCategories">Gestionar Categorías</a>
        <a href="?controller=user&action=logout" class="logout">Cerrar Sesión</a>
    </div>
    
    <div class="metrics">
        <div class="metric-card">
            <div class="metric-value"><?php echo $avgResolutionTime; ?> días</div>
            <div class="metric-label">Tiempo Promedio de Resolución</div>
        </div>
        <div class="metric-card">
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
        <canvas id="statusChart" width="400" height="200"></canvas>
    </div>
</body>
</html>