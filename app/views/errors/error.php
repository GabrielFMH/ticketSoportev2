<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Sistema de Tickets</title>
    <link href="css/main.css" rel="stylesheet">
</head>
<body>
    <div class="error-container">
        <h1 class="error-title">Error</h1>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php else: ?>
            <div class="error-message">Ha ocurrido un error inesperado.</div>
        <?php endif; ?>
        <a href="?controller=user&action=dashboard" class="back-link">Volver al Panel</a>
    </div>
</body>
</html>