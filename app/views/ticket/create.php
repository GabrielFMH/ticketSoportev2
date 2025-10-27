<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Ticket - Sistema de Tickets</title>
    <style>
        body { font-family: Arial, sans-serif; background: #FFFFFF; color: #333; margin: 0; padding: 20px; }
        .header { background: #007BFF; color: #FFFFFF; padding: 15px; border-radius: 4px 4px 0 0; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .nav { margin-bottom: 20px; }
        .nav a { background: #007BFF; color: #FFFFFF; padding: 10px 15px; text-decoration: none; border-radius: 4px; margin-right: 10px; }
        .nav a:hover { background: #0056b3; }
        .logout { float: right; background: #6C757D; }
        .logout:hover { background: #545B62; }
        .form-container { background: #FFFFFF; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
        h2 { color: #007BFF; margin-bottom: 20px; }
        label { display: block; margin: 10px 0 5px; font-weight: bold; }
        input[type="text"], input[type="email"], textarea, select { width: 100%; padding: 10px; border: 1px solid #DDD; border-radius: 4px; box-sizing: border-box; margin-bottom: 15px; }
        textarea { height: 120px; resize: vertical; }
        button { width: 100%; padding: 12px; background: #007BFF; color: #FFFFFF; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
        .error, .success { text-align: center; margin: 10px 0; padding: 10px; border-radius: 4px; }
        .error { color: #DC3545; background: #F8D7DA; }
        .success { color: #155724; background: #D4EDDA; }
        .form-row { display: flex; gap: 20px; }
        .form-row .form-group { flex: 1; }
        @media (max-width: 768px) { .form-row { flex-direction: column; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>Crear Nuevo Ticket</h1>
        <a href="?controller=user&action=dashboard" class="nav">Volver al Dashboard</a>
        <a href="?controller=user&action=logout" class="logout">Cerrar Sesión</a>
    </div>
    
    <div class="form-container">
        <h2>Detalles del Problema</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="title">Título del Ticket:</label>
            <input type="text" id="title" name="title" required>
            
            <label for="description">Descripción del Problema:</label>
            <textarea id="description" name="description" required placeholder="Describa el problema en detalle..."></textarea>
            
            <label for="contact_info">Datos de Contacto (Email/Teléfono):</label>
            <input type="text" id="contact_info" name="contact_info" required>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="category_id">Categoría:</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Seleccione una categoría</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="priority_id">Prioridad:</label>
                    <select id="priority_id" name="priority_id" required>
                        <option value="">Seleccione prioridad</option>
                        <?php foreach ($priorities as $pri): ?>
                            <option value="<?php echo $pri['id']; ?>"><?php echo htmlspecialchars($pri['level']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="impact">Impacto:</label>
                    <select id="impact" name="impact">
                        <option value="Bajo">Bajo</option>
                        <option value="Medio">Medio</option>
                        <option value="Alto">Alto</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="urgency">Urgencia:</label>
                    <select id="urgency" name="urgency">
                        <option value="Baja">Baja</option>
                        <option value="Media">Media</option>
                        <option value="Alta">Alta</option>
                    </select>
                </div>
            </div>
            
            <button type="submit">Crear Ticket</button>
        </form>
    </div>
</body>
</html>