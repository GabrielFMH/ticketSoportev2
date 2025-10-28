<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Sistema de Tickets</title>
    <style>
        body { font-family: Arial, sans-serif; background: #FFFFFF; color: #333; margin: 0; padding: 20px; }
        .error-container { max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 4px; background: #f8f9fa; }
        .error-title { color: #dc3545; margin-bottom: 20px; }
        .error-message { margin-bottom: 20px; }
        .back-link { display: inline-block; background: #007BFF; color: #FFFFFF; padding: 10px 15px; text-decoration: none; border-radius: 4px; }
        .back-link:hover { background: #0056b3; }
    </style>
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