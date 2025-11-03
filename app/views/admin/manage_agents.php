<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Agentes - Sistema de Tickets</title>
    <link href="css/main.css" rel="stylesheet">
    <script src="js/admin-agents.js"></script>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="icon">👥</div>
            <h1>Gestionar Agentes</h1>
        </div>
        <div class="header-right">
            <a href="?controller=admin&action=dashboard" class="btn btn-primary">Reportes</a>
            <a href="?controller=admin&action=manageDepartments" class="btn btn-primary">Gestionar Departamentos</a>
            <a href="?controller=user&action=logout" class="btn btn-danger">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container">
        <div class="wide-table-container">
            <div class="form-section">
                <h2>Lista de Agentes</h2>
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <div class="success"><?php echo $success; ?></div>
                <?php endif; ?>
            </div>

            <div class="list-section">
                <h2>Agentes y sus Departamentos</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre de Usuario</th>
                            <th>Email</th>
                            <th>Especialización</th>
                            <th>Departamento Actual</th>
                            <th>Fecha de Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($agents)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 20px;">No hay agentes registrados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($agents as $agent): ?>
                                <tr>
                                    <td><?php echo $agent['id']; ?></td>
                                    <td><?php echo htmlspecialchars($agent['username']); ?></td>
                                    <td><?php echo htmlspecialchars($agent['email']); ?></td>
                                    <td><?php echo htmlspecialchars(isset($agent['specialization']) ? $agent['specialization'] : 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(isset($agent['department_name']) ? $agent['department_name'] : 'Sin asignar'); ?></td>
                                    <td><?php echo $agent['created_at'] ? $agent['created_at']->format('d/m/Y H:i') : 'N/A'; ?></td>
                                    <td>
                                        <span class="btn btn-primary" onclick="toggleEdit(<?php echo $agent['id']; ?>)">Editar Departamento</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="8" style="padding: 0; text-align: right;">
                                        <div id="edit-<?php echo $agent['id']; ?>" class="edit-form" style="padding: 10px;">
                                            <form method="POST">
                                                <input type="hidden" name="edit_department" value="1">
                                                <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">
                                                
                                                <div class="form-row" style="flex-direction: column; align-items: flex-start;">
                                                    <label for="edit-dept-<?php echo $agent['id']; ?>" style="display: block; margin-bottom: 5px;">Departamento:</label>
                                                    
                                                    <select id="edit-dept-<?php echo $agent['id']; ?>" name="department_id" style="width: 100%; box-sizing: border-box;">
                                                        <option value="">Sin asignar</option>
                                                        <?php foreach ($departments as $dept): ?>
                                                            <option value="<?php echo $dept['id']; ?>" <?php echo ($agent['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($dept['name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="form-row" style="flex-direction: column; align-items: flex-start;">
                                                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                                    <button type="button" class="btn btn-secondary" onclick="toggleEdit(<?php echo $agent['id']; ?>)">Cancelar</button>
                                                </div>
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
</body>
</html>