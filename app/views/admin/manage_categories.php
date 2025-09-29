<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Categorías - Sistema de Tickets</title>
    <style>
        body { font-family: Arial, sans-serif; background: #FFFFFF; color: #333; margin: 0; padding: 20px; }
        .header { background: #007BFF; color: #FFFFFF; padding: 15px; border-radius: 4px 4px 0 0; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .nav { margin-bottom: 20px; }
        .nav a { background: #007BFF; color: #FFFFFF; padding: 10px 15px; text-decoration: none; border-radius: 4px; margin-right: 10px; }
        .nav a:hover { background: #0056b3; }
        .logout { float: right; background: #6C757D; }
        .logout:hover { background: #545B62; }
        .container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .form-section, .list-section { background: #FFFFFF; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2, h3 { color: #007BFF; }
        label { display: block; margin: 10px 0 5px; font-weight: bold; }
        input[type="text"], textarea, select { width: 100%; padding: 10px; border: 1px solid #DDD; border-radius: 4px; box-sizing: border-box; margin-bottom: 15px; }
        textarea { height: 80px; resize: vertical; }
        button { padding: 10px 20px; background: #007BFF; color: #FFFFFF; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px; }
        button:hover { background: #0056b3; }
        .delete-btn { background: #DC3545; }
        .delete-btn:hover { background: #C82333; }
        .error { color: #DC3545; text-align: center; margin: 10px 0; padding: 10px; border-radius: 4px; background: #F8D7DA; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #DDD; }
        .table th { background: #F8F9FA; color: #495057; }
        .table tr:hover { background: #F8F9FA; }
        .edit-form { display: none; margin-top: 10px; padding: 15px; background: #F8F9FA; border-radius: 4px; }
        .edit-link { color: #007BFF; cursor: pointer; text-decoration: underline; }
        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>Gestionar Categorías</h1>
        <a href="?controller=admin&action=dashboard" class="nav">Reportes</a>
        <a href="?controller=user&action=logout" class="logout">Cerrar Sesión</a>
    </div>
    
    <div class="container">
        <div class="form-section">
            <h2>Agregar Nueva Categoría</h2>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="hidden" name="add" value="1">
                <label for="name">Nombre:</label>
                <input type="text" id="name" name="name" required>
                
                <label for="description">Descripción:</label>
                <textarea id="description" name="description" placeholder="Descripción opcional..."></textarea>
                
                <label for="department_id">Departamento:</label>
                <select id="department_id" name="department_id">
                    <option value="">Ninguno</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit">Agregar Categoría</button>
            </form>
        </div>
        
        <div class="list-section">
            <h2>Categorías Existentes</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Departamento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?php echo $cat['id']; ?></td>
                            <td><?php echo htmlspecialchars($cat['name']); ?></td>
                            <td><?php echo htmlspecialchars(isset($cat['description']) ? $cat['description'] : 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars(isset($cat['dept_name']) ? $cat['dept_name'] : 'Ninguno'); ?></td>
                            <td>
                                <span class="edit-link" onclick="toggleEdit(<?php echo $cat['id']; ?>)">Editar</span>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar esta categoría?')">
                                    <input type="hidden" name="delete" value="1">
                                    <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                    <button type="submit" class="delete-btn">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5">
                                <div id="edit-<?php echo $cat['id']; ?>" class="edit-form">
                                    <form method="POST">
                                        <input type="hidden" name="edit" value="1">
                                        <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                        <label for="edit-name-<?php echo $cat['id']; ?>">Nombre:</label>
                                        <input type="text" id="edit-name-<?php echo $cat['id']; ?>" name="name" value="<?php echo htmlspecialchars($cat['name']); ?>" required>
                                        
                                        <label for="edit-desc-<?php echo $cat['id']; ?>">Descripción:</label>
                                        <textarea id="edit-desc-<?php echo $cat['id']; ?>" name="description"><?php echo htmlspecialchars(isset($cat['description']) ? $cat['description'] : ''); ?></textarea>
                                        
                                        <label for="edit-dept-<?php echo $cat['id']; ?>">Departamento:</label>
                                        <select id="edit-dept-<?php echo $cat['id']; ?>" name="department_id">
                                            <option value="">Ninguno</option>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?php echo $dept['id']; ?>" <?php echo ($cat['department_id'] == $dept['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($dept['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        
                                        <button type="submit">Guardar Cambios</button>
                                        <button type="button" onclick="toggleEdit(<?php echo $cat['id']; ?>)">Cancelar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        function toggleEdit(id) {
            var editDiv = document.getElementById('edit-' + id);
            editDiv.style.display = editDiv.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>