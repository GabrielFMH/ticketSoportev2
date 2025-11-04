IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'tickets_db')
BEGIN
    CREATE DATABASE tickets_db;
END
GO

USE tickets_db;
GO

CREATE TABLE departamentos (
    id INT IDENTITY(1,1) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion NVARCHAR(MAX),
    creado_en DATETIME2 DEFAULT GETDATE()
);
GO

INSERT INTO departamentos (nombre, descripcion) VALUES
('Sin departamento', 'Departamento por defecto para agentes sin asignar'),
('Soporte TI', 'Departamento de soporte técnico e informático'),
('Recursos Humanos', 'Departamento de recursos humanos'),
('Administración', 'Departamento administrativo');
GO

CREATE TABLE usuarios (
    id INT IDENTITY(1,1) PRIMARY KEY,
    nombre_usuario VARCHAR(50) UNIQUE NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    rol VARCHAR(20) NOT NULL DEFAULT 'usuario' CHECK (rol IN ('usuario', 'agente', 'admin')),
    departamento_id INT NULL,
    especializacion VARCHAR(100),
    creado_en DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL
);
GO


CREATE TABLE prioridades (
    id INT IDENTITY(1,1) PRIMARY KEY,
    nivel VARCHAR(20) NOT NULL CHECK (nivel IN ('Baja', 'Media', 'Alta', 'Crítica')),
    color VARCHAR(7) DEFAULT '#007BFF',
    creado_en DATETIME2 DEFAULT GETDATE()
);
GO

INSERT INTO prioridades (nivel, color) VALUES 
('Baja', '#6C757D'),
('Media', '#FFC107'),
('Alta', '#FD7E14'),
('Crítica', '#DC3545');
GO

CREATE TABLE tickets (
    id INT IDENTITY(1,1) PRIMARY KEY,
    usuario_id INT NOT NULL,
    departamento_id INT NULL,
    titulo VARCHAR(200) NOT NULL,
    descripcion NVARCHAR(MAX) NOT NULL,
    info_contacto VARCHAR(200) NOT NULL,
    prioridad_id INT NOT NULL,
    estado VARCHAR(20) DEFAULT 'Abierto' CHECK (estado IN ('Abierto', 'En Progreso', 'Resuelto', 'Cerrado')),
    asignado_a_id INT NULL,
    impacto NVARCHAR(MAX),
    urgencia NVARCHAR(MAX),
    creado_en DATETIME2 DEFAULT GETDATE(),
    actualizado_en DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL,
    FOREIGN KEY (prioridad_id) REFERENCES prioridades(id),
    FOREIGN KEY (asignado_a_id) REFERENCES usuarios(id) ON DELETE NO ACTION
);
GO

CREATE TABLE asignaciones (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ticket_id INT NOT NULL,
    agente_id INT NOT NULL,
    departamento_id INT NULL,
    asignado_en DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE NO ACTION,
    FOREIGN KEY (agente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL
);
GO

CREATE TABLE historial (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ticket_id INT NOT NULL,
    accion VARCHAR(255) NOT NULL,
    notas NVARCHAR(MAX),
    usuario_id INT NULL,
    fecha_hora DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE NO ACTION,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);
GO

CREATE TABLE notificaciones (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ticket_id INT NOT NULL,
    tipo VARCHAR(20) NOT NULL CHECK (tipo IN ('actualizacion', 'cambio_estado', 'solicitud_info')),
    enviado_a VARCHAR(100) NOT NULL,
    enviado_en DATETIME2 DEFAULT GETDATE(),
    estado VARCHAR(20) DEFAULT 'enviado' CHECK (estado IN ('enviado', 'fallido')),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE NO ACTION
);
GO

CREATE INDEX idx_tickets_estado ON tickets(estado);
CREATE INDEX idx_tickets_departamento ON tickets(departamento_id);
CREATE INDEX idx_tickets_asignado ON tickets(asignado_a_id);
CREATE INDEX idx_usuarios_rol ON usuarios(rol);
CREATE INDEX idx_usuarios_departamento ON usuarios(departamento_id);
GO

-- =============================================
-- STORED PROCEDURES
-- =============================================

-- User Management Procedures
CREATE PROCEDURE Usp_Tik_S_AutenticarUsuario
    @nombre_usuario VARCHAR(50),
    @contrasena VARCHAR(255)
AS
BEGIN
    SELECT id, nombre_usuario, rol FROM usuarios WHERE nombre_usuario = @nombre_usuario AND contrasena = @contrasena;
END;
GO

CREATE PROCEDURE Usp_Tik_S_VerificarUsuarioExiste
    @nombre_usuario VARCHAR(50),
    @correo VARCHAR(100)
AS
BEGIN
    SELECT id FROM usuarios WHERE nombre_usuario = @nombre_usuario OR correo = @correo;
END;
GO

CREATE PROCEDURE Usp_Tik_U_Usuario
    @id INT = NULL,
    @nombre_usuario VARCHAR(50) = NULL,
    @correo VARCHAR(100) = NULL,
    @contrasena VARCHAR(255) = NULL,
    @rol VARCHAR(20) = NULL,
    @departamento_id INT = NULL,
    @especializacion VARCHAR(100) = NULL,
    @tipo_upsert VARCHAR(20) = 'id'  -- 'id', 'nombre_usuario', or 'correo'
AS
BEGIN
    DECLARE @id_existente INT;
    DECLARE @departamento_final_id INT = @departamento_id;
    
    -- For new users with 'agente' role, if no department is specified, use the default 'Sin departamento'
    IF @id_existente IS NULL AND @rol = 'agente' AND @departamento_id IS NULL
    BEGIN
        SELECT @departamento_final_id = id FROM departamentos WHERE nombre = 'Sin departamento';
    END
    
    -- Determine the appropriate upsert strategy
    IF @tipo_upsert = 'nombre_usuario' AND @nombre_usuario IS NOT NULL
    BEGIN
        SELECT @id_existente = id FROM usuarios WHERE nombre_usuario = @nombre_usuario;
    END
    ELSE IF @tipo_upsert = 'correo' AND @correo IS NOT NULL
    BEGIN
        SELECT @id_existente = id FROM usuarios WHERE correo = @correo;
    END
    ELSE
    BEGIN
        SELECT @id_existente = @id;
    END
    
    IF @id_existente IS NULL
    BEGIN
        -- Insert new user
        INSERT INTO usuarios (nombre_usuario, correo, contrasena, rol, departamento_id, especializacion)
        VALUES (@nombre_usuario, @correo, @contrasena, @rol, @departamento_final_id, @especializacion);
        SELECT SCOPE_IDENTITY() as id, 'INSERTADO' as accion;
    END
    ELSE
    BEGIN
        -- Update existing user
        UPDATE usuarios
        SET
            nombre_usuario = COALESCE(@nombre_usuario, nombre_usuario),
            correo = COALESCE(@correo, correo),
            contrasena = COALESCE(@contrasena, contrasena),
            rol = COALESCE(@rol, rol),
            departamento_id = COALESCE(@departamento_id, departamento_id),
            especializacion = COALESCE(@especializacion, especializacion)
        WHERE id = @id_existente;
        
        SELECT @id_existente as id, 'ACTUALIZADO' as accion;
    END
END;
GO

CREATE PROCEDURE Usp_Tik_S_ObtenerTicketsUsuario
    @usuario_id INT
AS
BEGIN
    SELECT t.id, t.titulo, t.descripcion, t.info_contacto, p.nivel AS prioridad, t.estado, u.nombre_usuario AS asignado_a, t.impacto, t.urgencia, t.creado_en, t.actualizado_en
    FROM tickets t
    LEFT JOIN prioridades p ON t.prioridad_id = p.id
    LEFT JOIN usuarios u ON t.asignado_a_id = u.id
    WHERE t.usuario_id = @usuario_id
    ORDER BY t.creado_en DESC;
END;
GO


CREATE PROCEDURE Usp_Tik_S_ObtenerTodosDepartamentos
AS
BEGIN
    SELECT id, nombre FROM departamentos ORDER BY nombre;
END;
GO

-- Department Management Procedures
CREATE PROCEDURE Usp_Tik_U_Departamento
    @id INT = NULL,
    @nombre VARCHAR(100),
    @descripcion NVARCHAR(MAX) = NULL
AS
BEGIN
    IF @id IS NULL OR @id = 0
    BEGIN
        -- Insert new department
        INSERT INTO departamentos (nombre, descripcion) VALUES (@nombre, @descripcion);
        SELECT SCOPE_IDENTITY() as id, 'INSERTADO' as accion;
    END
    ELSE
    BEGIN
        -- Update existing department
        UPDATE departamentos SET nombre = @nombre, descripcion = @descripcion WHERE id = @id;
        SELECT @id as id, 'ACTUALIZADO' as accion;
    END
END;
GO

CREATE PROCEDURE Usp_Tik_D_EliminarDepartamento
    @id INT
AS
BEGIN
    DELETE FROM departamentos WHERE id = @id;
END;
GO

CREATE PROCEDURE Usp_Tik_S_ObtenerDepartamentosConConteoAgentes
AS
BEGIN
    SELECT
        d.*,
        COUNT(u.id) as conteo_agentes
    FROM departamentos d
    LEFT JOIN usuarios u ON d.id = u.departamento_id AND u.rol = 'agente'
    GROUP BY d.id, d.nombre, d.descripcion, d.creado_en
    ORDER BY d.nombre;
END;
GO

-- Agent Management Procedures
CREATE PROCEDURE Usp_Tik_S_ObtenerDepartamentoAgente
    @agente_id INT
AS
BEGIN
    SELECT departamento_id FROM usuarios WHERE id = @agente_id;
END;
GO

CREATE PROCEDURE Usp_Tik_S_ObtenerTicketsDepartamentoAgente
    @departamento_id INT
AS
BEGIN
    SELECT t.id, t.titulo, t.estado, t.creado_en, t.asignado_a_id, u.nombre_usuario as nombre_usuario
    FROM tickets t
    LEFT JOIN usuarios u ON t.usuario_id = u.id
    WHERE t.departamento_id = @departamento_id
    ORDER BY t.creado_en DESC;
END;
GO

CREATE PROCEDURE Usp_Tik_S_ObtenerAdmin
AS
BEGIN
    SELECT id FROM usuarios WHERE rol = 'admin';
END;
GO

-- Ticket Management Procedures

CREATE PROCEDURE Usp_Tik_U_CrearTicket
    @usuario_id INT,
    @titulo VARCHAR(200),
    @descripcion NVARCHAR(MAX),
    @info_contacto VARCHAR(200),
    @prioridad_id INT,
    @impacto NVARCHAR(MAX),
    @urgencia NVARCHAR(MAX),
    @departamento_id INT = NULL
AS
BEGIN
    INSERT INTO tickets (usuario_id, departamento_id, titulo, descripcion, info_contacto, prioridad_id, impacto, urgencia)
    VALUES (@usuario_id, @departamento_id, @titulo, @descripcion, @info_contacto, @prioridad_id, @impacto, @urgencia);
    SELECT SCOPE_IDENTITY() as id;
END;
GO

CREATE PROCEDURE Usp_Tik_S_ObtenerTicketPorId
    @ticket_id INT
AS
BEGIN
    SELECT t.*, u.nombre_usuario as nombre_usuario, u.correo as correo_usuario, p.nivel as nivel_prioridad, a.nombre_usuario as nombre_asignado, d.nombre as nombre_departamento
    FROM tickets t
    LEFT JOIN usuarios u ON t.usuario_id = u.id
    LEFT JOIN prioridades p ON t.prioridad_id = p.id
    LEFT JOIN usuarios a ON t.asignado_a_id = a.id
    LEFT JOIN departamentos d ON t.departamento_id = d.id
    WHERE t.id = @ticket_id;
END;
GO

CREATE PROCEDURE Usp_Tik_S_ObtenerHistorialTicket
    @ticket_id INT
AS
BEGIN
    SELECT h.*, u.nombre_usuario FROM historial h LEFT JOIN usuarios u ON h.usuario_id = u.id WHERE h.ticket_id = @ticket_id ORDER BY h.fecha_hora ASC;
END;
GO

CREATE PROCEDURE Usp_Tik_U_ActualizarEstadoTicket
    @ticket_id INT,
    @estado VARCHAR(20),
    @usuario_id INT,
    @notas NVARCHAR(MAX) = NULL
AS
BEGIN
    UPDATE tickets SET estado = @estado, actualizado_en = GETDATE() WHERE id = @ticket_id;
    
    INSERT INTO historial (ticket_id, accion, notas, usuario_id)
    VALUES (@ticket_id, 'Estado cambiado a: ' + @estado, @notas, @usuario_id);
END;
GO

CREATE PROCEDURE Usp_Tik_U_AgregarHistorial
    @ticket_id INT,
    @accion VARCHAR(255),
    @notas NVARCHAR(MAX) = NULL,
    @usuario_id INT = NULL
AS
BEGIN
    INSERT INTO historial (ticket_id, accion, notas, usuario_id) VALUES (@ticket_id, @accion, @notas, @usuario_id);
END;
GO

CREATE PROCEDURE Usp_Tik_U_AsignarTicket
    @ticket_id INT,
    @agente_id INT,
    @departamento_id INT = NULL
AS
BEGIN
    UPDATE tickets SET asignado_a_id = @agente_id WHERE id = @ticket_id;
    
    INSERT INTO asignaciones (ticket_id, agente_id, departamento_id) VALUES (@ticket_id, @agente_id, @departamento_id);
    
    INSERT INTO historial (ticket_id, accion, notas, usuario_id)
    VALUES (@ticket_id, 'Ticket asignado', NULL, @agente_id);
END;
GO

CREATE PROCEDURE Usp_Tik_S_ObtenerAgenteDisponible
    @departamento_id INT
AS
BEGIN
    SELECT TOP 1 u.id
    FROM usuarios u
    WHERE u.rol = 'agente'
    AND u.departamento_id = @departamento_id
    AND (SELECT COUNT(*) FROM tickets t WHERE t.asignado_a_id = u.id AND t.estado != 'Cerrado') < 5
    ORDER BY u.id;
END;
GO

CREATE PROCEDURE Usp_Tik_S_ObtenerCorreoUsuario
    @usuario_id INT
AS
BEGIN
    SELECT correo FROM usuarios WHERE id = @usuario_id;
END;
GO

CREATE PROCEDURE Usp_Tik_U_RegistrarNotificacion
    @ticket_id INT,
    @tipo VARCHAR(20),
    @enviado_a VARCHAR(100),
    @estado VARCHAR(20) = 'enviado'
AS
BEGIN
    INSERT INTO notificaciones (ticket_id, tipo, enviado_a, estado) VALUES (@ticket_id, @tipo, @enviado_a, @estado);
END;
GO

-- Report Procedures

CREATE PROCEDURE Usp_Tik_S_ObtenerTicketsPorAgente
AS
BEGIN
    SELECT u.nombre_usuario as agente, COUNT(t.id) as cantidad
    FROM tickets t
    LEFT JOIN usuarios u ON t.asignado_a_id = u.id
    WHERE u.rol = 'agente'
    GROUP BY u.id, u.nombre_usuario
    ORDER BY cantidad DESC;
END;
GO

CREATE PROCEDURE Usp_Tik_S_ObtenerTicketsPorDepartamento
AS
BEGIN
    SELECT d.nombre as departamento, COUNT(t.id) as cantidad
    FROM tickets t
    LEFT JOIN departamentos d ON t.departamento_id = d.id
    GROUP BY d.id, d.nombre
    ORDER BY cantidad DESC;
END;
GO

CREATE PROCEDURE Usp_Tik_S_ObtenerTiempoPromedioResolucion
AS
BEGIN
    SELECT AVG(DATEDIFF(DAY, creado_en, actualizado_en)) as tiempo_promedio
    FROM tickets
    WHERE estado = 'Resuelto';
END;
GO

CREATE PROCEDURE Usp_Tik_S_ObtenerTicketsPorEstado
AS
BEGIN
    SELECT estado, COUNT(id) as cantidad FROM tickets GROUP BY estado;
END;
GO

-- Lookup Procedures

CREATE PROCEDURE Usp_Tik_S_ObtenerPrioridades
AS
BEGIN
    SELECT id, nivel, color FROM prioridades ORDER BY id;
END;
GO

-- Recent Updates Procedure for Notifications
CREATE PROCEDURE Usp_Tik_S_ObtenerActualizacionesTicketsRecientes
    @usuario_id INT,
    @limite INT = 5
AS
BEGIN
    SELECT TOP (@limite)
        t.id as ticket_id,
        t.titulo,
        h.accion,
        h.fecha_hora as creado_en,
        t.estado
    FROM tickets t
    INNER JOIN historial h ON t.id = h.ticket_id
    WHERE t.usuario_id = @usuario_id
    ORDER BY h.fecha_hora DESC;
END;
GO

-- Get All Agents Procedure
CREATE PROCEDURE Usp_Tik_S_ObtenerTodosAgentes
AS
BEGIN
    SELECT u.id, u.nombre_usuario, u.correo, u.rol, u.departamento_id, d.nombre as nombre_departamento, u.creado_en
    FROM usuarios u
    LEFT JOIN departamentos d ON u.departamento_id = d.id
    WHERE u.rol = 'agente'
    ORDER BY u.nombre_usuario;
END;
GO

-- Agent Department Management Procedure
CREATE PROCEDURE Usp_Tik_U_DepartamentoAgente
    @agente_id INT = NULL,
    @nombre_usuario VARCHAR(50) = NULL,
    @departamento_id INT = NULL,
    @rol VARCHAR(20) = NULL,
    @tipo_upsert VARCHAR(20) = 'id'  -- 'id' or 'nombre_usuario'
AS
BEGIN
    DECLARE @id_agente_existente INT;
    DECLARE @departamento_actual_id INT;
    
    -- Determine the appropriate upsert strategy
    IF @tipo_upsert = 'nombre_usuario' AND @nombre_usuario IS NOT NULL
    BEGIN
        SELECT @id_agente_existente = id, @departamento_actual_id = departamento_id
        FROM usuarios
        WHERE nombre_usuario = @nombre_usuario AND rol = 'agente';
    END
    ELSE
    BEGIN
        SELECT @id_agente_existente = id, @departamento_actual_id = departamento_id
        FROM usuarios
        WHERE id = @agente_id AND rol = 'agente';
    END
    
    IF @id_agente_existente IS NULL
    BEGIN
        -- Agent not found, return error
        SELECT -1 as id, 'AGENTE_NO_ENCONTRADO' as accion, 'Agente no encontrado' as mensaje;
        RETURN;
    END
    
    -- Check if department change is needed
    IF @departamento_actual_id != @departamento_id
    BEGIN
        -- Update agent department
        UPDATE usuarios
        SET
            departamento_id = @departamento_id,
            rol = COALESCE(@rol, rol)
        WHERE id = @id_agente_existente;
        
        -- Log the department change
        INSERT INTO historial (ticket_id, accion, notas, usuario_id)
        VALUES (0, 'Departamento de agente actualizado', 'Departamento cambiado a ID: ' + CAST(@departamento_id AS VARCHAR), @id_agente_existente);
        
        SELECT @id_agente_existente as id, 'DEPARTAMENTO_ACTUALIZADO' as accion, 'Departamento actualizado exitosamente' as mensaje;
    END
    ELSE
    BEGIN
        -- No change needed
        SELECT @id_agente_existente as id, 'SIN_CAMBIOS' as accion, 'El departamento permanece igual' as mensaje;
    END
END;
GO

-- Get Tickets Assigned to Specific Agent
CREATE PROCEDURE Usp_Tik_S_ObtenerTicketsAgente
    @agente_id INT
AS
BEGIN
    SELECT t.id, t.titulo, t.estado, t.creado_en, t.asignado_a_id, u.nombre_usuario as nombre_usuario, t.departamento_id
    FROM tickets t
    LEFT JOIN usuarios u ON t.usuario_id = u.id
    WHERE t.asignado_a_id = @agente_id
    ORDER BY t.creado_en DESC;
END;
GO

-- Ticket Management UPSERT Procedure
CREATE PROCEDURE Usp_Tik_U_DetallesTicket
    @ticket_id INT = NULL,
    @usuario_id INT = NULL,
    @departamento_id INT = NULL,
    @titulo VARCHAR(200) = NULL,
    @descripcion NVARCHAR(MAX) = NULL,
    @info_contacto VARCHAR(200) = NULL,
    @prioridad_id INT = NULL,
    @estado VARCHAR(20) = NULL,
    @asignado_a_id INT = NULL,
    @impacto NVARCHAR(MAX) = NULL,
    @urgencia NVARCHAR(MAX) = NULL,
    @es_upsert BIT = 0  -- 0 = update existing, 1 = create new
AS
BEGIN
    IF @es_upsert = 1 OR @ticket_id IS NULL
    BEGIN
        -- Create new ticket
        INSERT INTO tickets (
            usuario_id, departamento_id, titulo, descripcion, info_contacto,
            prioridad_id, estado, asignado_a_id, impacto, urgencia
        ) VALUES (
            @usuario_id, @departamento_id, @titulo, @descripcion, @info_contacto,
            @prioridad_id, @estado, @asignado_a_id, @impacto, @urgencia
        );
        SELECT SCOPE_IDENTITY() as id, 'INSERTADO' as accion, 'Nuevo ticket creado' as mensaje;
    END
    ELSE
    BEGIN
        -- Update existing ticket
        UPDATE tickets
        SET
            departamento_id = COALESCE(@departamento_id, departamento_id),
            titulo = COALESCE(@titulo, titulo),
            descripcion = COALESCE(@descripcion, descripcion),
            info_contacto = COALESCE(@info_contacto, info_contacto),
            prioridad_id = COALESCE(@prioridad_id, prioridad_id),
            estado = COALESCE(@estado, estado),
            asignado_a_id = COALESCE(@asignado_a_id, asignado_a_id),
            impacto = COALESCE(@impacto, impacto),
            urgencia = COALESCE(@urgencia, urgencia),
            actualizado_en = GETDATE()
        WHERE id = @ticket_id;
        
        IF @@ROWCOUNT > 0
        BEGIN
            SELECT @ticket_id as id, 'ACTUALIZADO' as accion, 'Ticket actualizado exitosamente' as mensaje;
        END
        ELSE
        BEGIN
            SELECT -1 as id, 'NO_ENCONTRADO' as accion, 'Ticket no encontrado' as mensaje;
        END
    END
END;
GO

-- Get Escalated Tickets Procedure
CREATE PROCEDURE Usp_Tik_S_ObtenerTicketsEscalados
AS
BEGIN
    SELECT DISTINCT
        t.*,
        u.nombre_usuario as nombre_usuario,
        d.nombre as nombre_departamento,
        a.nombre_usuario as nombre_asignado,
        h.fecha_hora as escalado_en
    FROM tickets t
    LEFT JOIN usuarios u ON t.usuario_id = u.id
    LEFT JOIN departamentos d ON t.departamento_id = d.id
    LEFT JOIN usuarios a ON t.asignado_a_id = a.id
    LEFT JOIN historial h ON t.id = h.ticket_id
        AND (h.accion LIKE '%escalado%' OR h.accion LIKE '%Escalar%')
    WHERE t.estado = 'En Progreso'
        AND h.id IS NOT NULL  -- Has escalation history
        AND NOT EXISTS (
            SELECT 1 FROM historial h2
            WHERE h2.ticket_id = t.id
            AND (h2.accion LIKE '%Escalación resuelta%' OR h2.accion LIKE '%reasignado por administrador%')
        )  -- Has not been resolved
    ORDER BY h.fecha_hora DESC;
END;
GO

-- Clear Escalation Status Procedure
CREATE PROCEDURE Usp_Tik_U_LimpiarEstadoEscalacion
    @ticket_id INT
AS
BEGIN
    -- Add history entry indicating escalation was resolved
    INSERT INTO historial (ticket_id, accion, notas, usuario_id)
    VALUES (@ticket_id, 'Escalación resuelta por administrador', 'Ticket reasignado y escalación resuelta', NULL);
END;
GO