<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Categorías - Sistema de Tickets</title>
    <link href="css/main.css" rel="stylesheet">
    <script src="js/admin-categories.js"></script>
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
        <div class="wide-table-container">
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
                    
                    <button type="submit" class="btn btn-primary">Agregar Categoría</button>
                </form>
            </div>


            <div class="list-section">
                <h2>Categorías Existentes</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <!-- <th>ID</th> -->
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Departamento</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <!-- <td><?php echo $cat['id']; ?></td> -->
                                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                <td><?php echo htmlspecialchars(isset($cat['description']) ? $cat['description'] : 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(isset($cat['dept_name']) ? $cat['dept_name'] : 'Ninguno'); ?></td>
                                <td>
                                    <span class="cancel-btn" onclick="toggleEdit(<?php echo $cat['id']; ?>)">Editar</span>
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
                                            
                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
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
    
</body>
</html>