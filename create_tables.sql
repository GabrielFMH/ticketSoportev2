-- Create database if not exists
IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'tickets_db')
BEGIN
    CREATE DATABASE tickets_db;
END
GO

USE tickets_db;
GO

-- Departments table
CREATE TABLE departments (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description NVARCHAR(MAX),
    created_at DATETIME2 DEFAULT GETDATE()
);
GO

-- Insert default departments
INSERT INTO departments (name, description) VALUES 
('Soporte TI', 'Departamento de soporte técnico e informático'),
('Recursos Humanos', 'Departamento de recursos humanos'),
('Administración', 'Departamento administrativo');
GO

-- Users table
CREATE TABLE users (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user' CHECK (role IN ('user', 'agent', 'admin')),
    department_id INT,
    specialization VARCHAR(100),
    created_at DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);
GO

-- Categories table
CREATE TABLE categories (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department_id INT,
    description NVARCHAR(MAX),
    created_at DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);
GO

-- Insert default categories
INSERT INTO categories (name, department_id, description) VALUES 
('Hardware', 1, 'Problemas con hardware físico'),
('Software', 1, 'Problemas con aplicaciones o software'),
('Red', 1, 'Problemas de conectividad y red'),
('Contratación', 2, 'Consultas sobre empleo y contratación');
GO

-- Priorities table
CREATE TABLE priorities (
    id INT IDENTITY(1,1) PRIMARY KEY,
    level VARCHAR(20) NOT NULL CHECK (level IN ('Baja', 'Media', 'Alta', 'Crítica')),
    color VARCHAR(7) DEFAULT '#007BFF',
    created_at DATETIME2 DEFAULT GETDATE()
);
GO

-- Insert default priorities
INSERT INTO priorities (level, color) VALUES 
('Baja', '#6C757D'),
('Media', '#FFC107'),
('Alta', '#FD7E14'),
('Crítica', '#DC3545');
GO

-- Tickets table
CREATE TABLE tickets (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT NOT NULL,
    department_id INT,
    title VARCHAR(200) NOT NULL,
    description NVARCHAR(MAX) NOT NULL,
    contact_info VARCHAR(200) NOT NULL,
    category_id INT NOT NULL,
    priority_id INT NOT NULL,
    status VARCHAR(20) DEFAULT 'Abierto' CHECK (status IN ('Abierto', 'En Progreso', 'Resuelto', 'Cerrado')),
    assignee_id INT,
    impact NVARCHAR(MAX),
    urgency NVARCHAR(MAX),
    created_at DATETIME2 DEFAULT GETDATE(),
    updated_at DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (priority_id) REFERENCES priorities(id),
    FOREIGN KEY (assignee_id) REFERENCES users(id) ON DELETE SET NULL
);
GO

-- Assignments table
CREATE TABLE assignments (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ticket_id INT NOT NULL,
    agent_id INT NOT NULL,
    department_id INT,
    assigned_at DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);
GO

-- History table
CREATE TABLE history (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ticket_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    notes NVARCHAR(MAX),
    user_id INT,
    timestamp DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
GO

-- Notifications table
CREATE TABLE notifications (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ticket_id INT NOT NULL,
    type VARCHAR(20) NOT NULL CHECK (type IN ('actualizacion', 'cambio_estado', 'solicitud_info')),
    sent_to VARCHAR(100) NOT NULL,
    sent_at DATETIME2 DEFAULT GETDATE(),
    status VARCHAR(20) DEFAULT 'enviado' CHECK (status IN ('enviado', 'fallido')),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
);
GO

-- Indexes for performance
CREATE INDEX idx_tickets_status ON tickets(status);
CREATE INDEX idx_tickets_department ON tickets(department_id);
CREATE INDEX idx_tickets_assignee ON tickets(assignee_id);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_department ON users(department_id);
GO