HISTORIAS DE USUARIO - SISTEMA DE TICKETS UPT
=============================================

Este documento proporciona una visión general de las historias de usuario
creadas para el sistema de tickets UPT (Universidad Politécnica de Trujillo).

ARCHIVOS CREADOS:
================

1. user_stories_usuarios_finales.txt
   - Historias de usuario para usuarios finales
   - 7 historias principales (USUARIO-001 a USUARIO-007)
   - Funcionalidades: crear tickets, seguimiento, notificaciones, perfil

2. user_stories_agentes.txt
   - Historias de usuario para agentes de soporte
   - 9 historias principales (AGENTE-001 a AGENTE-009)
   - Funcionalidades: gestión de tickets, actualización de estados, escalación

3. user_stories_administradores.txt
   - Historias de usuario para administradores del sistema
   - 11 historias principales (ADMIN-001 a ADMIN-011)
   - Funcionalidades: gestión de usuarios, configuración del sistema, reportes

4. user_stories_sistema.txt
   - Características técnicas y funcionalidades del sistema
   - 12 historias principales (SISTEMA-001 a SISTEMA-012)
   - Funcionalidades: notificaciones, seguridad, métricas, integración

ESTRUCTURA DEL SISTEMA:
=====================

ROLES DE USUARIO:
- Usuario Final: Crea y da seguimiento a sus tickets
- Agente: Gestiona y resuelve tickets asignados
- Administrador: Gestiona el sistema completo y usuarios

FUNCIONALIDADES PRINCIPALES:
- Creación y gestión de tickets con categorías y prioridades
- Asignación automática de agentes según departamento y carga de trabajo
- Sistema de historial completo de cambios
- Notificaciones por email automáticas
- Diferentes dashboards según el rol del usuario
- Gestión avanzada de usuarios y permisos
- Generación de métricas y reportes

DEPARTAMENTOS PREDEFINIDOS:
- Soporte TI (Hardware, Software, Red)
- Recursos Humanos (Contratación)
- Administración

PRIORIDADES DISPONIBLES:
- Baja (Gris)
- Media (Amarillo)
- Alta (Naranja)
- Crítica (Rojo)

ESTADOS DE TICKET:
- Abierto
- En Progreso
- Resuelto
- Cerrado

CÓMO USAR ESTAS HISTORIAS:
========================

1. PLANIFICACIÓN: Estas historias sirven como base para la planificación de sprints
2. DESARROLLO: Cada historia incluye criterios de aceptación claros
3. TESTING: Los criterios de aceptación pueden usarse para crear casos de prueba
4. DOCUMENTACIÓN: Proporcionan una visión clara de las funcionalidades requeridas

FORMATO DE LAS HISTORIAS:
=======================

Cada historia sigue el formato estándar:
- Título descriptivo con identificador único
- Rol del usuario ("Como [tipo de usuario]")
- Funcionalidad deseada ("Quiero [acción]")
- Beneficio obtenido ("Para [objetivo]")

- Criterios de aceptación detallados
- Funcionalidades específicas que debe incluir

TOTAL DE HISTORIAS: 39
=====================

- Usuarios Finales: 7 historias
- Agentes: 9 historias
- Administradores: 11 historias
- Sistema: 12 historias

Estas historias cubren todos los aspectos funcionales identificados en el análisis
del código fuente del sistema de tickets UPT.

Fecha de creación: Octubre 2025
Sistema: ticketUPT (Universidad Politécnica de Trujillo)