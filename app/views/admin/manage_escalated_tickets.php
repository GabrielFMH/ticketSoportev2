<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Tickets Escalados - Sistema de Tickets</title>
    <link href="css/main.css" rel="stylesheet">
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="icon">⚠️</div>
            <h1>Gestionar Tickets Escalados</h1>
        </div>
        <div class="header-right">
            <a href="?controller=admin&action=dashboard" class="btn btn-primary">Volver al Dashboard</a>
            <a href="?controller=admin&action=manageCategories" class="btn btn-primary">Gestionar Categorías</a>
            <a href="?controller=admin&action=manageAgents" class="btn btn-primary">Gestionar Agentes</a>
            <a href="?controller=user&action=logout" class="btn btn-danger">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container">
        <div class="form-section">
            <h2>Tickets Escalados</h2>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
        </div>

        <div class="list-section">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Usuario</th>
                        <th>Departamento Actual</th>
                        <th>Agente Actual</th>
                        <th>Estado</th>
                        <th>Fecha de Escalación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($escalatedTickets)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;">No hay tickets escalados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($escalatedTickets as $ticket): ?>
                            <tr>
                                <td><?php echo $ticket['id']; ?></td>
                                <td><?php echo htmlspecialchars($ticket['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['nombre_usuario']); ?></td>
                                <td><?php echo htmlspecialchars(isset($ticket['nombre_departamento']) ? $ticket['nombre_departamento'] : 'No asignado'); ?></td>
                                <td><?php echo htmlspecialchars(isset($ticket['nombre_asignado']) ? $ticket['nombre_asignado'] : 'No asignado'); ?></td>
                                <td><span class="status status-<?php echo strtolower(str_replace(' ', '-', $ticket['estado'])); ?>"><?php echo $ticket['estado']; ?></span></td>
                                <td><?php echo $ticket['escalado_en'] ? $ticket['escalado_en']->format('d/m/Y H:i') : 'N/A'; ?></td>
                                <td>
                                    <span class="btn btn-warning" onclick="toggleReassign(<?php echo $ticket['id']; ?>)">Reasignar</span>
                                    <a href="?controller=ticket&action=view&id=<?php echo $ticket['id']; ?>" class="btn btn-primary">Ver Detalles</a>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8" style="padding: 0; text-align: right;">
                                    <div id="reassign-<?php echo $ticket['id']; ?>" class="reassign-form" style="padding: 10px; display: none;">
                                        <form method="POST">
                                            <input type="hidden" name="reassign_ticket" value="1">
                                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                            
                                            <div class="form-row">
                                                <div class="form-group">
                                                    <label for="new-dept-<?php echo $ticket['id']; ?>">Nuevo Departamento:</label>
                                                    <select id="new-dept-<?php echo $ticket['id']; ?>" name="new_department_id" onchange="updateAgentDropdown(<?php echo $ticket['id']; ?>)">
                                                        <option value="">Sin cambiar</option>
                                                        <?php foreach ($departments as $dept): ?>
                                                            <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['nombre']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="new-agent-<?php echo $ticket['id']; ?>">Nuevo Agente:</label>
                                                    <select id="new-agent-<?php echo $ticket['id']; ?>" name="new_agent_id">
                                                        <option value="">Primero seleccione un departamento</option>
                                                        <?php foreach ($agents as $agent): ?>
                                                            <option value="<?php echo $agent['id']; ?>" data-department="<?php echo $agent['department_id']; ?>" style="display: none;"><?php echo htmlspecialchars($agent['nombre_usuario']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="form-row">
                                                <div class="form-group">
                                                    <label for="reassign-notes-<?php echo $ticket['id']; ?>">Notas de Reasignación:</label>
                                                    <textarea id="reassign-notes-<?php echo $ticket['id']; ?>" name="reassignment_notes" placeholder="Motivo de la reasignación..." style="width: 100%;"></textarea>
                                                </div>
                                            </div>
                                            
                                            <div class="form-row">
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-warning">Confirmar Reasignación</button>
                                                    <button type="button" class="btn btn-secondary" onclick="toggleReassign(<?php echo $ticket['id']; ?>)">Cancelar</button>
                                                </div>
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

    <script>
        function toggleReassign(ticketId) {
            var form = document.getElementById('reassign-' + ticketId);
            if (form.style.display === 'none') {
                form.style.display = 'block';
                // Reset agent dropdown when opening form
                resetAgentDropdown(ticketId);
            } else {
                form.style.display = 'none';
            }
        }
        
        function updateAgentDropdown(ticketId) {
            var deptSelect = document.getElementById('new-dept-' + ticketId);
            var agentSelect = document.getElementById('new-agent-' + ticketId);
            var selectedDept = deptSelect.value;
            
            // Reset agent dropdown
            for (var i = 0; i < agentSelect.options.length; i++) {
                var option = agentSelect.options[i];
                if (option.value === '') {
                    option.textContent = selectedDept ? 'Seleccione un agente' : 'Primero seleccione un departamento';
                    continue;
                }
                
                // Show/hide agents based on department
                if (selectedDept === '' || option.getAttribute('data-department') === selectedDept) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            }
            
            // Clear any previously selected agent
            agentSelect.value = '';
        }
        
        function resetAgentDropdown(ticketId) {
            var agentSelect = document.getElementById('new-agent-' + ticketId);
            var firstOption = agentSelect.options[0];
            firstOption.textContent = 'Primero seleccione un departamento';
            
            // Hide all agent options except the first one
            for (var i = 1; i < agentSelect.options.length; i++) {
                agentSelect.options[i].style.display = 'none';
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Reset any open forms
            var forms = document.querySelectorAll('.reassign-form');
            forms.forEach(function(form) {
                form.style.display = 'none';
            });
        });
    </script>
</body>
</html>