<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - Sistema de Tickets</title>
    <style>
        body { font-family: Arial, sans-serif; background: #FFFFFF; color: #333; margin: 0; padding: 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .register-form { background: #FFFFFF; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #007BFF; margin-bottom: 20px; }
        input[type="text"], input[type="email"], input[type="password"], select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #DDD; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007BFF; color: #FFFFFF; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        button:hover { background: #0056b3; }
        .error, .success { text-align: center; margin: 10px 0; padding: 10px; border-radius: 4px; }
        .error { color: #DC3545; background: #F8D7DA; }
        .success { color: #155724; background: #D4EDDA; }
        .link { text-align: center; margin-top: 15px; }
        .link a { color: #007BFF; text-decoration: none; }
    </style>
</head>
<body>
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
                <option value="user" selected>Usuario</option>
                <option value="agent">Agente</option>
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