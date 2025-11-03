# Sistema de Tickets de Soporte

Un sistema completo de gestión de tickets de soporte desarrollado en PHP con arquitectura MVC, diseñado para organizaciones que requieren un sistema de tickets robusto con múltiples roles de usuario y funcionalidades avanzadas.

## 🎯 Características Principales

### Roles de Usuario
- **Usuarios**: Pueden crear tickets, ver sus tickets, agregar comentarios y cancelar tickets abiertos
- **Agentes**: Pueden aceptar tickets de su departamento, actualizar estado, editar detalles (categoría, prioridad, impacto, urgencia) y escalar tickets complejos
- **Administradores**: Gestión completa del sistema, reportes, reasignación de tickets escalados, gestión de categorías y agentes

### Funcionalidades del Sistema

#### Gestión de Tickets
- ✅ Creación simplificada de tickets con selección de departamento
- ✅ Estados de ticket: Abierto, En Progreso, Resuelto, Cerrado
- ✅ Seguimiento completo del historial de cambios
- ✅ Vista detallada de tickets con toda la información relevante

#### Sistema de Escalación
- ✅ Escalación automática por agentes cuando necesitan supervisión
- ✅ Panel de administración para gestión de tickets escalados
- ✅ Reasignación inteligente con filtros por departamento
- ✅ Resolución automática de tickets escalados tras reasignación

#### Gestión por Departamentos
- ✅ Asignación automática de tickets a departamentos
- ✅ Filtros dinámicos de agentes por departamento
- ✅ Agentes ven tanto tickets de su departamento como asignaciones directas
- ✅ Departamento específico para creación de tickets

#### Panel de Administración
- ✅ Dashboard con métricas y reportes
- ✅ Gestión de categorías por departamento
- ✅ Gestión de agentes y sus departamentos
- ✅ Gestión de tickets escalados con herramientas de reasignación

#### Características Técnicas
- ✅ Arquitectura MVC (Model-View-Controller)
- ✅ Base de datos SQL Server con stored procedures
- ✅ Sesiones PHP para autenticación
- ✅ Interfaz responsive con CSS personalizado
- ✅ JavaScript para interactividad dinámica
- ✅ Validación y manejo de errores

## 🏗️ Estructura del Proyecto

```
ticketSoportev2/
├── app/
│   ├── controllers/          # Controladores MVC
│   │   ├── AdminController.php
│   │   ├── AgentController.php
│   │   ├── TicketController.php
│   │   └── UserController.php
│   ├── models/              # Modelos de datos
│   │   ├── ReportModel.php
│   │   └── TicketModel.php
│   └── views/               # Vistas
│       ├── admin/           # Vistas administrativas
│       ├── agent/           # Vistas de agentes
│       ├── ticket/          # Vistas de tickets
│       ├── user/            # Vistas de usuarios
│       └── errors/          # Vistas de errores
├── public/                  # Archivos públicos
│   ├── css/                # Hojas de estilo
│   └── js/                 # JavaScript
├── config/                 # Configuración
│   └── database.php        # Configuración de BD
└── docs/                   # Documentación
```

## 📋 Flujo de Trabajo

### 1. Creación de Tickets
1. Usuario accede a "Crear Nuevo Ticket"
2. Completa formulario con título, descripción y datos de contacto
3. Selecciona departamento (opcional)
4. Ticket se crea con valores por defecto para categoría y prioridad
5. Asignación automática a departamento y agente disponible

### 2. Gestión por Agentes
1. Agente ve tickets de su departamento y asignaciones directas
2. Puede aceptar tickets no asignados
3. Actualiza estado y detalles del ticket
4. Escala tickets complejos a administración

### 3. Escalación y Reasignación
1. Ticket se marca como escalado al sistema administrativo
2. Administrador ve tickets escalados en panel dedicado
3. Puede reasignar a diferentes departamento/agente
4. Ticket desaparece de lista escalada tras reasignación

### 4. Panel Administrativo
- Métricas de rendimiento por categoría, agente y departamento
- Tiempo promedio de resolución
- Gestión de categorías y departamentos
- Supervisión de tickets escalados

## 🔧 Configuración y Instalación

### Requisitos del Sistema
- PHP 5.5 o superior
- SQL Server
- Servidor web (Apache/Nginx)

### Configuración de Base de Datos
1. Ejecutar el script `create_tables.sql` para crear la estructura
2. Configurar credenciales en `config/database.php`
3. Los stored procedures están incluidos en el script SQL

### Configuración de la Aplicación
1. Clonar el proyecto en el directorio web
2. Configurar variables de sesión y autenticación
3. Personalizar CSS según necesidades
4. Configurar emails para notificaciones (opcional)

## 📊 Características Avanzadas

### Reportes y Métricas
- Tickets por categoría
- Tickets por agente
- Tickets por departamento
- Tiempo promedio de resolución
- Distribución por estado

### Notificaciones
- Sistema de notificaciones en tiempo real
- Email automático para actualizaciones
- Notificaciones de asignación y escalación

### Historial y Auditoría
- Registro completo de cambios por ticket
- Seguimiento de asignaciones y reasignaciones
- Historial de escalaciones y resoluciones

## 🚀 Uso del Sistema

### Para Usuarios
1. **Crear Ticket**: Llenar formulario de creación con detalles del problema
2. **Ver Mis Tickets**: Monitorear estado y actualizaciones
3. **Agregar Comentarios**: Proporcionar información adicional
4. **Cancelar Tickets**: Solo tickets en estado "Abierto"

### Para Agentes
1. **Dashboard**: Ver tickets disponibles y asignados
2. **Aceptar Tickets**: Tomar ownership de tickets disponibles
3. **Actualizar Estado**: Cambiar estado según progreso
4. **Editar Detalles**: Modificar categoría, prioridad, impacto y urgencia
5. **Escalar**: Reenviar tickets complejos a administración

### Para Administradores
1. **Dashboard**: Revisar métricas y reportes generales
2. **Gestionar Categorías**: Crear/editar categorías y asignar departamentos
3. **Gestionar Agentes**: Asignar agentes a departamentos
4. **Gestionar Escalaciones**: Reasignar tickets escalados
5. **Reportes**: Analizar rendimiento del sistema



---

**Desarrollado con PHP, SQL Server y tecnologías web modernas para proporcionar una solución completa de gestión de tickets de soporte.**