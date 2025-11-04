<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Departamentos - Sistema de Tickets</title>
    <link href="css/main.css" rel="stylesheet">
    <script src="js/admin-categories.js"></script>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="icon">⚙️</div>
            <h1>Gestionar Departamentos</h1>
        </div>
        <div class="header-right">
            <a href="?controller=admin&action=dashboard" class="btn btn-primary">Reportes</a>
            <a href="?controller=admin&action=manageAgents" class="btn btn-primary">Gestionar Agentes</a>
            <a href="?controller=admin&action=manageEscalatedTickets" class="btn btn-primary">Tickets Escalados</a>
            <a href="?controller=user&action=logout" class="btn btn-danger">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container">
        <div class="wide-table-container">
            <div class="form-section">
                <h2>Agregar Nuevo Departamento</h2>
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <div class="success"><?php echo $success; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <input type="hidden" name="add" value="1">
                    <label for="name">Nombre:</label>
                    <input type="text" id="name" name="name" required>
                    
                    <label for="description">Descripción:</label>
                    <textarea id="description" name="description" placeholder="Descripción opcional..."></textarea>
                    
                    <button type="submit" class="btn btn-primary">Agregar Departamento</button>
                </form>
            </div>

            <div class="list-section">
                <h2>Departamentos Existentes</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Número de Agentes</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($departments)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 20px;">No hay departamentos registrados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($departments as $dept): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($dept['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars(isset($dept['descripcion']) ? $dept['descripcion'] : 'N/A'); ?></td>
                                    <td><?php echo isset($dept['cantidad_agentes']) ? $dept['cantidad_agentes'] : 0; ?></td>
                                    <td>
                                        <span class="cancel-btn" onclick="toggleEdit(<?php echo $dept['id']; ?>)">Editar</span>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar este departamento?')">
                                            <input type="hidden" name="delete" value="1">
                                            <input type="hidden" name="id" value="<?php echo $dept['id']; ?>">
                                            <button type="submit" class="delete-btn">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        <div id="edit-<?php echo $dept['id']; ?>" class="edit-form">
                                            <form method="POST">
                                                <input type="hidden" name="edit" value="1">
                                                <input type="hidden" name="id" value="<?php echo $dept['id']; ?>">
                                                <label for="edit-name-<?php echo $dept['id']; ?>">Nombre:</label>
                                                <input type="text" id="edit-name-<?php echo $dept['id']; ?>" name="name" value="<?php echo htmlspecialchars($dept['nombre']); ?>" required>
                                                
                                                <label for="edit-desc-<?php echo $dept['id']; ?>">Descripción:</label>
                                                <textarea id="edit-desc-<?php echo $dept['id']; ?>" name="description"><?php echo htmlspecialchars(isset($dept['descripcion']) ? $dept['descripcion'] : ''); ?></textarea>
                                                
                                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                                <button type="button" class="cancel-btn" onclick="toggleEdit(<?php echo $dept['id']; ?>)">Cancelar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        function toggleEdit(id) {
            var form = document.getElementById('edit-' + id);
            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }
    </script>
</body>
</html>