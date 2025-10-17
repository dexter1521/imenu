🛡️ Sistema de Roles, Permisos y Seguridad – iMenu
📌 Resumen general

Esta actualización implementa un sistema de autenticación y control de acceso profesional en iMenu basado en:

✅ JWT + Hook global para validar sesiones automáticamente.

🔐 Roles de usuario (admin, owner, staff) con jerarquía.

📊 Permisos granulares almacenados en base de datos.

⚙️ Seeder automático para inicializar permisos.

🧩 Integración con el flujo de CodeIgniter 3 sin dependencias externas.

🧱 Estructura del sistema
application/
├─ hooks/
│ └─ AuthHook.php ← Middleware global que protege todo el sistema
├─ core/
│ └─ traits/
│ └─ RoleTrait.php ← Trait para control manual de roles (opcional)
├─ helpers/
│ └─ auth_helper.php ← Funciones JWT (jwt_require, current_role, etc.)
├─ models/
│ └─ Permission_model.php ← Acceso a la tabla permisos
├─ tools/
│ └─ seed_permissions.php ← Seeder PHP para inicializar permisos por rol

🧪 Roles y jerarquía
Rol Descripción
admin Tiene acceso global a todo el sistema. No aplica validaciones.
owner Dueño del tenant. Acceso completo a todas las secciones.
staff Usuario operativo con permisos limitados configurables.
🔐 Validación automática – AuthHook.php

📁 application/hooks/AuthHook.php

Este hook global se ejecuta antes de cualquier controlador (pre_controller) y realiza:

🔎 Verificación de rutas públicas (login, registro, menú público).

🔑 Validación del JWT con jwt_require().

🧑‍💼 Obtención de current_role(), current_user_id() y current_tenant_id().

📊 Carga automática de permisos desde la tabla permisos.

✅ Bloqueo automático de controladores o métodos si el usuario no tiene permisos.

📍 Ventajas:

No necesitas escribir require_role() o require_permission() manualmente.

Todas las rutas están protegidas automáticamente.

Cualquier módulo nuevo se puede proteger solo agregándolo al permission_map.

🗄️ Tabla permisos

Se amplió para soportar control granular por módulo:

ALTER TABLE permisos
ADD COLUMN can_manage_orders TINYINT DEFAULT 0,
ADD COLUMN can_manage_payments TINYINT DEFAULT 0,
ADD COLUMN can_manage_subscriptions TINYINT DEFAULT 0,
ADD COLUMN can_manage_users TINYINT DEFAULT 0,
ADD COLUMN can_manage_plans TINYINT DEFAULT 0,
ADD COLUMN can_manage_reports TINYINT DEFAULT 0;

📊 Permisos disponibles:

Columna Funcionalidad que controla
can_products CRUD de productos
can_categories CRUD de categorías
can_adjustments Configuración del tenant
can_manage_orders Gestión de pedidos
can_manage_payments Pagos y cobros
can_manage_subscriptions Planes y suscripciones
can_manage_users Gestión de usuarios
can_manage_plans Administración de planes
can_manage_reports Reportes y analítica
can_view_stats Acceso al dashboard y métricas
🪄 Seeder de permisos – tools/seed_permissions.php

Este script inicializa automáticamente los permisos para todos los usuarios que no tengan permisos asignados:

owner → acceso completo.

staff → permisos limitados por defecto (productos, categorías, pedidos, estadísticas).

📌 Ejecución:

php tools/seed_permissions.php

o navegador:

https://tusitio.com/tools/seed_permissions.php

⚠️ Elimina este archivo después de usarlo en producción por seguridad.

🧰 Recomendaciones de uso

✅ Nuevos módulos: solo agrega el nombre del controlador al $permission_map en AuthHook.php.

✅ Nuevos permisos: crea una nueva columna en permisos y actualiza el seeder.

✅ Usuarios nuevos: al crearse, asegúrate de correr el seeder o insertar permisos en el registro inicial.

✅ Pruebas locales: borra los registros de permisos para regenerarlos con el seeder y validar la lógica.

🚀 Flujo de autenticación completo
flowchart TD
A[Petición HTTP] --> B[AuthHook pre_controller]
B --> C{Ruta pública?}
C -->|Sí| D[Permitir acceso]
C -->|No| E[Validar JWT]
E -->|Inválido| F[401 - No autorizado]
E -->|Válido| G[Obtener rol y permisos]
G --> H{Permiso requerido?}
H -->|No| I[403 - Acceso denegado]
H -->|Sí| J[Ejecutar controlador]

📦 Checklist post-implementación

Hook global instalado en application/config/hooks.php

Tabla permisos actualizada con nuevas columnas

Seeder ejecutado (tools/seed_permissions.php)

Permisos iniciales asignados a todos los usuarios

$permission_map actualizado para todos los módulos

Panel listo para extender con UI de permisos (opcional)

📌 Próximos pasos sugeridos

🧑‍💻 Crear interfaz en el panel admin para editar permisos por usuario.

📜 Agregar logs de intentos de acceso denegado.

🔁 Permitir clonar permisos entre usuarios.

📈 Añadir niveles de plan (Free, Pro) a la lógica del hook.

💡 Tip: Con este sistema implementado, iMenu ya tiene una capa de seguridad, roles y permisos al nivel de un SaaS profesional listo para escalar.
