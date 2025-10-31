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