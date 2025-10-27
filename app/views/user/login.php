<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestion de Tickets-UPT</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f0f4ff 0%, #ffffff 100%); color: #333; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .login-form { background: #ffffff; padding: 40px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,123,255,0.1); width: 100%; max-width: 380px; text-align: center; }
        h2 { font-size: 28px; font-weight: 500; color: #007bff; margin-bottom: 32px; letter-spacing: -0.5px; }
        label { display: block; text-align: left; font-size: 14px; font-weight: 400; color: #666; margin-bottom: 8px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 14px; margin-bottom: 20px; border: 1px solid #e1e5e9; border-radius: 8px; font-size: 16px; transition: border-color 0.2s; }
        input[type="text"]:focus, input[type="password"]:focus { outline: none; border-color: #007bff; }
        button { width: 100%; padding: 14px; background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: #ffffff; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500; transition: transform 0.2s; }
        button:hover { transform: translateY(-1px); }
        .error { color: #dc3545; text-align: center; margin: 16px 0; font-size: 14px; }
        .link { margin-top: 24px; }
        .link a { color: #007bff; text-decoration: none; font-size: 14px; font-weight: 400; }
        .link a:hover { text-decoration: underline; }
    </style>
</head>

<body>
    
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