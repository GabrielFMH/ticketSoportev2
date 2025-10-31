<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestion de Tickets-UPT</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
</head>

<body class="login-page">
    
    <div class="login-form">
        <h1>Sistema de Gestion de Tickets-UPT</h1>
        <h2>Iniciar Sesión</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Iniciar Sesión</button>
        </form>
        <div class="link">
            <a href="?controller=user&action=register">¿No tienes cuenta? Regístrate</a>
        </div>
    </div>
</body>
</html>