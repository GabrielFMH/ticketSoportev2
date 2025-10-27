<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador - Sistema de Tickets</title>
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
        .metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .metric-card { background: #FFFFFF; padding: 25px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); text-align: center; transition: transform 0.3s; }
        .metric-card:hover { transform: translateY(-5px); }
        .metric-icon { font-size: 40px; color: #007BFF; margin-bottom: 10px; }
        .metric-value { font-size: 2.5em; color: #007BFF; font-weight: bold; margin-bottom: 5px; }
        .metric-label { color: #6C757D; font-size: 16px; }
        .section { background: #FFFFFF; padding: 25px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .section h3 { color: #007BFF; border-bottom: 3px solid #007BFF; padding-bottom: 10px; margin-bottom: 20px; font-size: 24px; }
        .report-table { width: 100%; border-collapse: collapse; margin-top: 20px; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .report-table th { background: linear-gradient(135deg, #007BFF, #0056b3); color: #FFFFFF; }
        .report-table td { background: #FFFFFF; }
        .report-table tr:hover { background: #F8F9FA; }
        canvas { max-width: 100%; height: 300px; border-radius: 10px; }
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
        <div class="header-left">
            <div class="icon">👤</div>
            <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        </div>
        <div class="header-right">
            <a href="?controller=admin&action=dashboard" class="btn btn-primary">Reportes</a>
            <a href="?controller=admin&action=manageCategories" class="btn btn-primary">Gestionar Categorías</a>
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