<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - Sistema de Tickets</title>
    <link href="css/main.css" rel="stylesheet">
</head>
<body class="register-page">
    <div class="register-form">
        <h2>Registrarse</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            
            <label for="confirm_password">Confirmar Contraseña:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            
            <label for="role">Rol:</label>
            <select id="role" name="role">
                <option value="usuario" selected>Usuario</option>
                <option value="agente">Agente</option>
                <option value="admin">Administrador</option>
            </select>
            
            <button type="submit">Registrarse</button>
        </form>
        <div class="link">
            <a href="?controller=user&action=login">¿Ya tienes cuenta? Inicia sesión</a>
        </div>
    </div>
</body>
</html>