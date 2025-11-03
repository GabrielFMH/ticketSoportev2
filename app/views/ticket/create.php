<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Ticket - Sistema de Tickets</title>
    <link href="css/main.css" rel="stylesheet">
</head>
<body>
    <div class="header">
        <h1>Crear Nuevo Ticket</h1>
        <a href="?controller=user&action=dashboard" class="logout">Volver al Dashboard</a>
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
                    <label for="department_id">Departamento:</label>
                    <select id="department_id" name="department_id">
                        <option value="">Seleccione un departamento</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Crear Ticket</button>
        </form>
    </div>
</body>
</html>