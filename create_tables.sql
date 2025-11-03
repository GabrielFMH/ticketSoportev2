IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'tickets_db')
BEGIN
    CREATE DATABASE tickets_db;
END
GO

USE tickets_db;
GO

CREATE TABLE departments (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description NVARCHAR(MAX),
    created_at DATETIME2 DEFAULT GETDATE()
);
GO

INSERT INTO departments (name, description) VALUES 
('Soporte TI', 'Departamento de soporte técnico e informático'),
('Recursos Humanos', 'Departamento de recursos humanos'),
('Administración', 'Departamento administrativo');
GO

CREATE TABLE users (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user' CHECK (role IN ('user', 'agent', 'admin')),
    department_id INT NULL,
    specialization VARCHAR(100),
    created_at DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);
GO

CREATE TABLE categories (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department_id INT NULL,
    description NVARCHAR(MAX),
    created_at DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);
GO

INSERT INTO categories (name, department_id, description) VALUES 
('Hardware', 1, 'Problemas con hardware físico'),
('Software', 1, 'Problemas con aplicaciones o software'),
('Red', 1, 'Problemas de conectividad y red'),
('Contratación', 2, 'Consultas sobre empleo y contratación');
GO

CREATE TABLE priorities (
    id INT IDENTITY(1,1) PRIMARY KEY,
    level VARCHAR(20) NOT NULL CHECK (level IN ('Baja', 'Media', 'Alta', 'Crítica')),
    color VARCHAR(7) DEFAULT '#007BFF',
    created_at DATETIME2 DEFAULT GETDATE()
);
GO

INSERT INTO priorities (level, color) VALUES 
('Baja', '#6C757D'),
('Media', '#FFC107'),
('Alta', '#FD7E14'),
('Crítica', '#DC3545');
GO

CREATE TABLE tickets (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT NOT NULL,
    department_id INT NULL,
    title VARCHAR(200) NOT NULL,
    description NVARCHAR(MAX) NOT NULL,
    contact_info VARCHAR(200) NOT NULL,
    category_id INT NOT NULL,
    priority_id INT NOT NULL,
    status VARCHAR(20) DEFAULT 'Abierto' CHECK (status IN ('Abierto', 'En Progreso', 'Resuelto', 'Cerrado')),
    assignee_id INT NULL,
    impact NVARCHAR(MAX),
    urgency NVARCHAR(MAX),
    created_at DATETIME2 DEFAULT GETDATE(),
    updated_at DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (priority_id) REFERENCES priorities(id),
    FOREIGN KEY (assignee_id) REFERENCES users(id) ON DELETE NO ACTION
);
GO

CREATE TABLE assignments (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ticket_id INT NOT NULL,
    agent_id INT NOT NULL,
    department_id INT NULL,
    assigned_at DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE NO ACTION,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);
GO

CREATE TABLE history (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ticket_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    notes NVARCHAR(MAX),
    user_id INT NULL,
    timestamp DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE NO ACTION,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
GO

CREATE TABLE notifications (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ticket_id INT NOT NULL,
    type VARCHAR(20) NOT NULL CHECK (type IN ('actualizacion', 'cambio_estado', 'solicitud_info')),
    sent_to VARCHAR(100) NOT NULL,
    sent_at DATETIME2 DEFAULT GETDATE(),
    status VARCHAR(20) DEFAULT 'enviado' CHECK (status IN ('enviado', 'fallido')),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE NO ACTION
);
GO

CREATE INDEX idx_tickets_status ON tickets(status);
CREATE INDEX idx_tickets_department ON tickets(department_id);
CREATE INDEX idx_tickets_assignee ON tickets(assignee_id);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_department ON users(department_id);
GO

-- =============================================
-- STORED PROCEDURES
-- =============================================

-- User Management Procedures
CREATE PROCEDURE sp_AuthenticateUser
    @username VARCHAR(50),
    @password VARCHAR(255)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT id, username, role FROM users WHERE username = @username AND password = @password;
END;
GO

CREATE PROCEDURE sp_CheckUserExists
    @username VARCHAR(50),
    @email VARCHAR(100)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT id FROM users WHERE username = @username OR email = @email;
END;
GO

CREATE PROCEDURE sp_CreateUser
    @username VARCHAR(50),
    @email VARCHAR(100),
    @password VARCHAR(255),
    @role VARCHAR(20),
    @department_id INT = NULL
AS
BEGIN
    SET NOCOUNT ON;
    INSERT INTO users (username, email, password, role, department_id) VALUES (@username, @email, @password, @role, @department_id);
    SELECT SCOPE_IDENTITY() as id;
END;
GO

CREATE PROCEDURE sp_GetUserTickets
    @user_id INT
AS
BEGIN
    SET NOCOUNT ON;
    SELECT t.id, t.title, t.description, t.contact_info, c.name AS category, p.level AS priority, t.status, u.username AS assignee, t.impact, t.urgency, t.created_at, t.updated_at
    FROM tickets t
    LEFT JOIN categories c ON t.category_id = c.id
    LEFT JOIN priorities p ON t.priority_id = p.id
    LEFT JOIN users u ON t.assignee_id = u.id
    WHERE t.user_id = @user_id
    ORDER BY t.created_at DESC;
END;
GO

-- Category Management Procedures
CREATE PROCEDURE sp_CreateCategory
    @name VARCHAR(100),
    @description NVARCHAR(MAX) = NULL,
    @department_id INT = NULL
AS
BEGIN
    SET NOCOUNT ON;
    INSERT INTO categories (name, description, department_id) VALUES (@name, @description, @department_id);
    SELECT SCOPE_IDENTITY() as id;
END;
GO

CREATE PROCEDURE sp_UpdateCategory
    @id INT,
    @name VARCHAR(100),
    @description NVARCHAR(MAX) = NULL,
    @department_id INT = NULL
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE categories SET name = @name, description = @description, department_id = @department_id WHERE id = @id;
END;
GO

CREATE PROCEDURE sp_DeleteCategory
    @id INT
AS
BEGIN
    SET NOCOUNT ON;
    DELETE FROM categories WHERE id = @id;
END;
GO

CREATE PROCEDURE sp_GetAllCategories
AS
BEGIN
    SET NOCOUNT ON;
    SELECT c.*, d.name as dept_name FROM categories c LEFT JOIN departments d ON c.department_id = d.id ORDER BY c.name;
END;
GO

CREATE PROCEDURE sp_GetAllDepartments
AS
BEGIN
    SET NOCOUNT ON;
    SELECT id, name FROM departments ORDER BY name;
END;
GO

-- Agent Management Procedures
CREATE PROCEDURE sp_GetAgentDepartment
    @agent_id INT
AS
BEGIN
    SET NOCOUNT ON;
    SELECT department_id FROM users WHERE id = @agent_id;
END;
GO

CREATE PROCEDURE sp_GetAgentDepartmentTickets
    @department_id INT
AS
BEGIN
    SET NOCOUNT ON;
    SELECT t.id, t.title, t.status, t.created_at, t.assignee_id, u.username as user_name 
    FROM tickets t 
    LEFT JOIN users u ON t.user_id = u.id 
    WHERE t.department_id = @department_id 
    ORDER BY t.created_at DESC;
END;
GO

CREATE PROCEDURE sp_GetAdmin
AS
BEGIN
    SET NOCOUNT ON;
    SELECT id FROM users WHERE role = 'admin';
END;
GO

-- Ticket Management Procedures
CREATE PROCEDURE sp_GetDepartmentFromCategory
    @category_id INT
AS
BEGIN
    SET NOCOUNT ON;
    SELECT department_id FROM categories WHERE id = @category_id;
END;
GO

CREATE PROCEDURE sp_CreateTicket
    @user_id INT,
    @title VARCHAR(200),
    @description NVARCHAR(MAX),
    @contact_info VARCHAR(200),
    @category_id INT,
    @priority_id INT,
    @impact NVARCHAR(MAX),
    @urgency NVARCHAR(MAX),
    @department_id INT = NULL
AS
BEGIN
    SET NOCOUNT ON;
    INSERT INTO tickets (user_id, department_id, title, description, contact_info, category_id, priority_id, impact, urgency) 
    VALUES (@user_id, @department_id, @title, @description, @contact_info, @category_id, @priority_id, @impact, @urgency);
    SELECT SCOPE_IDENTITY() as id;
END;
GO

CREATE PROCEDURE sp_GetTicketById
    @ticket_id INT
AS
BEGIN
    SET NOCOUNT ON;
    SELECT t.*, u.username as user_name, u.email as user_email, c.name as category_name, p.level as priority_level, a.username as assignee_name, d.name as department_name
    FROM tickets t
    LEFT JOIN users u ON t.user_id = u.id
    LEFT JOIN categories c ON t.category_id = c.id
    LEFT JOIN priorities p ON t.priority_id = p.id
    LEFT JOIN users a ON t.assignee_id = a.id
    LEFT JOIN departments d ON t.department_id = d.id
    WHERE t.id = @ticket_id;
END;
GO

CREATE PROCEDURE sp_GetTicketHistory
    @ticket_id INT
AS
BEGIN
    SET NOCOUNT ON;
    SELECT h.*, u.username FROM history h LEFT JOIN users u ON h.user_id = u.id WHERE h.ticket_id = @ticket_id ORDER BY h.timestamp ASC;
END;
GO

CREATE PROCEDURE sp_UpdateTicketStatus
    @ticket_id INT,
    @status VARCHAR(20),
    @user_id INT,
    @notes NVARCHAR(MAX) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE tickets SET status = @status, updated_at = GETDATE() WHERE id = @ticket_id;
    
    INSERT INTO history (ticket_id, action, notes, user_id) 
    VALUES (@ticket_id, 'Estado cambiado a: ' + @status, @notes, @user_id);
END;
GO

CREATE PROCEDURE sp_AddHistory
    @ticket_id INT,
    @action VARCHAR(255),
    @notes NVARCHAR(MAX) = NULL,
    @user_id INT = NULL
AS
BEGIN
    SET NOCOUNT ON;
    INSERT INTO history (ticket_id, action, notes, user_id) VALUES (@ticket_id, @action, @notes, @user_id);
END;
GO

CREATE PROCEDURE sp_AssignTicket
    @ticket_id INT,
    @agent_id INT,
    @department_id INT = NULL
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE tickets SET assignee_id = @agent_id WHERE id = @ticket_id;
    
    INSERT INTO assignments (ticket_id, agent_id, department_id) VALUES (@ticket_id, @agent_id, @department_id);
    
    INSERT INTO history (ticket_id, action, notes, user_id) 
    VALUES (@ticket_id, 'Ticket asignado', NULL, @agent_id);
END;
GO

CREATE PROCEDURE sp_GetAvailableAgent
    @department_id INT
AS
BEGIN
    SET NOCOUNT ON;
    SELECT TOP 1 u.id 
    FROM users u 
    WHERE u.role = 'agent' 
    AND u.department_id = @department_id 
    AND (SELECT COUNT(*) FROM tickets t WHERE t.assignee_id = u.id AND t.status != 'Cerrado') < 5
    ORDER BY u.id;
END;
GO

CREATE PROCEDURE sp_GetUserEmail
    @user_id INT
AS
BEGIN
    SET NOCOUNT ON;
    SELECT email FROM users WHERE id = @user_id;
END;
GO

CREATE PROCEDURE sp_LogNotification
    @ticket_id INT,
    @type VARCHAR(20),
    @sent_to VARCHAR(100),
    @status VARCHAR(20) = 'enviado'
AS
BEGIN
    SET NOCOUNT ON;
    INSERT INTO notifications (ticket_id, type, sent_to, status) VALUES (@ticket_id, @type, @sent_to, @status);
END;
GO

-- Report Procedures
CREATE PROCEDURE sp_GetTicketsPerCategory
AS
BEGIN
    SET NOCOUNT ON;
    SELECT c.name as category, COUNT(t.id) as count 
    FROM tickets t 
    LEFT JOIN categories c ON t.category_id = c.id 
    GROUP BY c.id, c.name 
    ORDER BY count DESC;
END;
GO

CREATE PROCEDURE sp_GetTicketsPerAgent
AS
BEGIN
    SET NOCOUNT ON;
    SELECT u.username as agent, COUNT(t.id) as count 
    FROM tickets t 
    LEFT JOIN users u ON t.assignee_id = u.id 
    WHERE u.role = 'agent' 
    GROUP BY u.id, u.username 
    ORDER BY count DESC;
END;
GO

CREATE PROCEDURE sp_GetTicketsPerDepartment
AS
BEGIN
    SET NOCOUNT ON;
    SELECT d.name as department, COUNT(t.id) as count 
    FROM tickets t 
    LEFT JOIN departments d ON t.department_id = d.id 
    GROUP BY d.id, d.name 
    ORDER BY count DESC;
END;
GO

CREATE PROCEDURE sp_GetAverageResolutionTime
AS
BEGIN
    SET NOCOUNT ON;
    SELECT AVG(DATEDIFF(DAY, created_at, updated_at)) as avg_time 
    FROM tickets 
    WHERE status = 'Resuelto';
END;
GO

CREATE PROCEDURE sp_GetTicketsByStatus
AS
BEGIN
    SET NOCOUNT ON;
    SELECT status, COUNT(id) as count FROM tickets GROUP BY status;
END;
GO

-- Lookup Procedures
CREATE PROCEDURE sp_GetCategories
AS
BEGIN
    SET NOCOUNT ON;
    SELECT id, name FROM categories ORDER BY name;
END;
GO

CREATE PROCEDURE sp_GetPriorities
AS
BEGIN
    SET NOCOUNT ON;
    SELECT id, level, color FROM priorities ORDER BY id;
END;
GO

-- Recent Updates Procedure for Notifications
CREATE PROCEDURE sp_GetRecentTicketUpdates
    @user_id INT,
    @limit INT = 5
AS
BEGIN
    SET NOCOUNT ON;
    SELECT TOP (@limit) 
        t.id as ticket_id,
        t.title,
        h.action,
        h.timestamp as created_at,  -- <-- ESTAS LÍNEAS DEBEN IR JUNTAS
        t.status
    FROM tickets t
    INNER JOIN history h ON t.id = h.ticket_id
    WHERE t.user_id = @user_id
    ORDER BY h.timestamp DESC;
END;
GO

-- Get All Agents Procedure (AHORA ESTÁ AFUERA, EN EL LUGAR CORRECTO)
CREATE PROCEDURE sp_GetAllAgents
AS
BEGIN
    SET NOCOUNT ON;
    SELECT u.id, u.username, u.email, u.role, u.department_id, d.name as department_name, u.created_at
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.id
    WHERE u.role = 'agent'
    ORDER BY u.username;
END;
GO

-- Update Agent Department Procedure (AHORA ESTÁ AFUERA, EN EL LUGAR CORRECTO)
CREATE PROCEDURE sp_UpdateAgentDepartment
    @agent_id INT,
    @department_id INT = NULL
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE users SET department_id = @department_id WHERE id = @agent_id AND role = 'agent';
END;
GO

-- Get Tickets Assigned to Specific Agent
CREATE PROCEDURE sp_GetAgentTickets
    @agent_id INT
AS
BEGIN
    SET NOCOUNT ON;
    SELECT t.id, t.title, t.status, t.created_at, t.assignee_id, u.username as user_name, t.department_id
    FROM tickets t
    LEFT JOIN users u ON t.user_id = u.id
    WHERE t.assignee_id = @agent_id
    ORDER BY t.created_at DESC;
END;
GO

-- Update Ticket Details Procedure
CREATE PROCEDURE sp_UpdateTicketDetails
    @ticket_id INT,
    @category_id INT = NULL,
    @priority_id INT = NULL,
    @impact NVARCHAR(MAX) = NULL,
    @urgency NVARCHAR(MAX) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE tickets
    SET
        category_id = COALESCE(@category_id, category_id),
        priority_id = COALESCE(@priority_id, priority_id),
        impact = COALESCE(@impact, impact),
        urgency = COALESCE(@urgency, urgency),
        updated_at = GETDATE()
    WHERE id = @ticket_id;
END;
GO

-- Get Escalated Tickets Procedure
CREATE PROCEDURE sp_GetEscalatedTickets
AS
BEGIN
    SET NOCOUNT ON;
    SELECT DISTINCT
        t.*,
        u.username as user_name,
        d.name as department_name,
        a.username as assignee_name,
        h.timestamp as escalated_at
    FROM tickets t
    LEFT JOIN users u ON t.user_id = u.id
    LEFT JOIN departments d ON t.department_id = d.id
    LEFT JOIN users a ON t.assignee_id = a.id
    LEFT JOIN history h ON t.id = h.ticket_id
        AND (h.action LIKE '%escalado%' OR h.action LIKE '%Escalar%')
    WHERE t.status = 'En Progreso'
        AND h.id IS NOT NULL  -- Has escalation history
        AND NOT EXISTS (
            SELECT 1 FROM history h2
            WHERE h2.ticket_id = t.id
            AND (h2.action LIKE '%Escalación resuelta%' OR h2.action LIKE '%reasignado por administrador%')
        )  -- Has not been resolved
    ORDER BY h.timestamp DESC;
END;
GO

-- Clear Escalation Status Procedure
CREATE PROCEDURE sp_ClearEscalationStatus
    @ticket_id INT
AS
BEGIN
    SET NOCOUNT ON;
    -- Add history entry indicating escalation was resolved
    INSERT INTO history (ticket_id, action, notes, user_id)
    VALUES (@ticket_id, 'Escalación resuelta por administrador', 'Ticket reasignado y escalación resuelta', NULL);
END;
GO