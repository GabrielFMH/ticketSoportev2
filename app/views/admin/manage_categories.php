<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Categorías - Sistema de Tickets</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #F8F9FA; color: #333; margin: 0; padding: 0; }
        .header { background: linear-gradient(135deg, #007BFF, #0056b3); color: #FFFFFF; padding: 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 10px rgba(0,123,255,0.2); }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .icon { width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .header h1 { margin: 0; font-size: 28px; }
        .header-right { display: flex; gap: 10px; }
        .btn { padding: 10px 20px; border: none; border-radius: 25px; cursor: pointer; text-decoration: none; font-weight: bold; transition: all 0.3s; display: inline-block; }
        .btn-primary { background: #FFFFFF; color: #007BFF; }
        .btn-primary:hover { background: #E9ECEF; transform: translateY(-2px); }
        .btn-danger { background: #DC3545; color: #FFFFFF; }
        .btn-danger:hover { background: #C82333; transform: translateY(-2px); }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .main-container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .form-section, .list-section { background: #FFFFFF; padding: 25px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h2, h3 { color: #007BFF; margin-top: 0; font-size: 24px; }
        label { display: block; margin: 10px 0 5px; font-weight: bold; color: #495057; }
        input[type="text"], textarea, select { width: 100%; padding: 12px; border: 1px solid #DEE2E6; border-radius: 8px; box-sizing: border-box; margin-bottom: 15px; font-size: 16px; transition: border-color 0.3s; }
        input[type="text"]:focus, textarea:focus, select:focus { border-color: #007BFF; outline: none; box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25); }
        textarea { height: 80px; resize: vertical; }
        button { padding: 12px 24px; background: #007BFF; color: #FFFFFF; border: none; border-radius: 25px; cursor: pointer; font-weight: bold; transition: all 0.3s; margin-right: 10px; font-size: 16px; }
        button:hover { background: #0056b3; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,123,255,0.3); }
        .delete-btn { background: #DC3545; }
        .delete-btn:hover { background: #C82333; }
        .cancel-btn { background: #6C757D; }
        .cancel-btn:hover { background: #545B62; }
        .error { color: #DC3545; text-align: center; margin: 10px 0; padding: 12px; border-radius: 8px; background: #F8D7DA; border: 1px solid #F5C6CB; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .table th { background: linear-gradient(135deg, #007BFF, #0056b3); color: #FFFFFF; padding: 15px; text-align: left; font-weight: bold; }
        .table td { padding: 15px; background: #FFFFFF; border-bottom: 1px solid #DEE2E6; }
        .table tr:hover { background: #F8F9FA; }
        .edit-form { display: none; margin-top: 10px; padding: 20px; background: #F8F9FA; border-radius: 10px; border: 1px solid #DEE2E6; }
        .edit-link { color: #007BFF; cursor: pointer; text-decoration: underline; font-weight: bold; }
        .edit-link:hover { color: #0056b3; }
        @media (max-width: 768px) { .main-container { grid-template-columns: 1fr; } .header { flex-direction: column; gap: 10px; text-align: center; } .header-right { justify-content: center; } }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="icon">⚙️</div>
            <h1>Gestionar Categorías</h1>
        </div>
        <div class="header-right">
            <a href="?controller=admin&action=dashboard" class="btn btn-primary">Reportes</a>
            <a href="?controller=user&action=logout" class="btn btn-danger">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container">
        <div class="main-container">
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
                                            <button type="button" class="cancel-btn" onclick="toggleEdit(<?php echo $cat['id']; ?>)">Cancelar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
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