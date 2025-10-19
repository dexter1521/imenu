# Gestión de Tenants - Panel Admin

**Fecha de implementación:** 18 de Octubre de 2025  
**Módulo:** Panel de Administración SaaS

---

## 📋 Resumen

El módulo de gestión de tenants permite a los administradores del sistema SaaS gestionar completamente a los restaurantes/clientes (tenants) que utilizan la plataforma iMenu.

---

## ✅ Funcionalidades Implementadas

### 1. **Listar Tenants**

**Endpoint:** `GET /admin/tenants`  
**Vista:** `/admin/tenants_view`

**Características:**

- Tabla con todos los tenants del sistema
- Columnas: ID, Nombre, Slug, Plan, Estado, Fecha de Creación
- Botones de acción para cada tenant:
  - 👁️ **Ver** - Acceder a la ficha completa
  - ✏️ **Editar** - Modificar datos del tenant
  - 🔄 **Suspender/Activar** - Cambiar estado activo
  - ❌ **Eliminar** - Borrado en cascada

**Tarjetas de estadísticas:**

- Total de tenants
- Tenants activos
- Ingresos mensuales proyectados
- Tenants con plan Pro

---

### 2. **Crear Tenant**

**Endpoint:** `POST /admin/tenant_create`

**Campos:**

- `nombre` (requerido) - Nombre del restaurante
- `slug` (opcional) - URL amigable (se genera automático si no se proporciona)
- `whatsapp` - Número de WhatsApp para pedidos
- `plan_id` - Plan asignado (select con planes disponibles)
- `activo` - Estado inicial (checkbox, activo por defecto)
- `logo_url` - URL del logo
- `color_primario` - Color principal del menú
- `color_secundario` - Color secundario del menú

**Proceso:**

1. Validar que el slug sea único
2. Crear registro en tabla `tenants`
3. Crear ajustes por defecto automáticamente
4. Retornar ID del nuevo tenant

**Validaciones:**

- Solo usuarios con rol `admin` pueden crear tenants
- El nombre es obligatorio
- El slug debe ser único (se verifica con `Tenant_model::is_slug_unique()`)

---

### 3. **Editar Tenant**

**Endpoint:** `POST /admin/tenant_update/[id]`

**Campos actualizables:**

- nombre
- slug (con validación de unicidad)
- logo_url
- color_primario
- color_secundario
- whatsapp
- activo (0 o 1)
- plan_id

**Modal de edición:**

- Formulario prellenado con datos actuales
- Select de planes cargado dinámicamente
- Validación en cliente y servidor

---

### 4. **Suspender / Reactivar Tenant**

**Endpoint:** `POST /admin/tenant_toggle/[id]`

**Funcionalidad:**

- Alterna el campo `activo` entre 0 y 1
- Si `activo = 0` (suspendido):
  - El tenant no puede iniciar sesión
  - El menú público no se muestra
- Si `activo = 1` (activo):
  - El tenant puede acceder al sistema normalmente

**Confirmación:**

- SweetAlert2 solicita confirmación antes de cambiar estado
- Mensaje descriptivo según la acción (suspender vs reactivar)

---

### 5. **Cambiar Plan de Tenant**

**Endpoint:** `POST /admin/tenant_change_plan/[id]`  
**Parámetro:** `plan_id`

**Funcionalidad:**

- Permite cambiar el plan asignado a un tenant
- Valida que el plan existe en la base de datos
- Actualiza inmediatamente el campo `plan_id` en la tabla tenants

**Interfaz:**

- Modal con select de planes disponibles
- Muestra información de cada plan: nombre, precio, límites
- Plan actual aparece preseleccionado
- Mensaje de confirmación después del cambio

**Notas:**

- El cambio de plan es inmediato
- Se recomienda crear una nueva suscripción manualmente si es necesario
- Los límites del nuevo plan se aplican de inmediato

---

### 6. **Eliminar Tenant (con Cascada)**

**Endpoint:** `POST /admin/tenant_delete/[id]`

**Proceso de eliminación en cascada:**

El método `Tenant_model::delete_cascade()` elimina:

1. **Ítems de pedidos** relacionados con pedidos del tenant
2. **Pedidos** del tenant
3. **Pagos** del tenant
4. **Suscripciones** del tenant
5. **Productos** del tenant
6. **Categorías** del tenant
7. **Ajustes** del tenant
8. **Usuarios** del tenant (incluyendo permisos)
9. **Permisos** de usuarios del tenant
10. **Registro del tenant**

**Características de seguridad:**

- Usa **transacciones** para garantizar integridad
- Si falla alguna eliminación, se hace rollback completo
- Confirmación doble con SweetAlert2
- Solo accesible para usuarios con rol `admin`

**Opcional:**

- Eliminar archivos físicos de uploads del tenant (comentado en código)

---

### 7. **Ver Ficha Completa de Tenant**

**Endpoint:** `GET /admin/tenant_show/[id]`  
**Vista:** `application/views/admin/tenant_show.php`

#### **Secciones de la ficha:**

##### 📊 **Tarjetas de Estadísticas**

- **Categorías:** Total de categorías creadas
- **Productos:** Total de productos en el menú
- **Pedidos:** Total de pedidos recibidos
- **Pagos:** Total de pagos registrados

##### ℹ️ **Detalles del Tenant**

- ID
- Nombre
- Slug (código único)
- WhatsApp
- Estado (activo/suspendido con badge)
- Fecha de creación
- Enlace al menú público

##### 📦 **Plan y Suscripción**

**Información del Plan:**

- Nombre del plan actual (con badge)
- Precio mensual
- Límite de categorías
- Límite de items
- Tiene anuncios (Sí/No)

**Botón:** Cambiar Plan (abre modal)

**Información de Suscripción:**

- Estado (activa/pendiente/expirada con badge de color)
- Fecha de inicio
- Fecha de fin
- Días restantes (o días desde que expiró)

##### 💰 **Últimos 10 Pagos**

Tabla con:

- ID del pago
- Concepto
- Monto ($XX.XX)
- Fecha
- Estado

##### 🛒 **Últimos 10 Pedidos**

Tabla con:

- ID del pedido
- Nombre del cliente
- Total ($XX.XX)
- Fecha y hora
- Estado

##### 📱 **Código QR**

- Imagen del código QR generado
- Botón de descarga
- Si no existe: botón para generar QR

##### 🔗 **Enlaces Útiles**

- URL del menú público (con input de solo lectura)
- Botón para copiar URL al portapapeles
- Botón "Ver Menú Público" (abre en nueva pestaña)

##### ⚡ **Botones de Acción**

- **Volver a Tenants** - Regresa a la lista
- **Suspender/Reactivar** - Alterna el estado del tenant

---

## 🎨 Diseño e Interfaz

### **Tecnologías UI:**

- **Bootstrap 4** (SB Admin 2)
- **FontAwesome** para iconos
- **SweetAlert2** para alertas y confirmaciones
- **Animate.css** para animaciones

### **Componentes visuales:**

- Cards con bordes de color según tipo (primary, success, info, warning)
- Badges de color según estado
- Botones con iconos descriptivos
- Tablas responsivas con scroll horizontal
- Modales centrados con backdrop

### **Experiencia de usuario:**

- Alertas descriptivas con colores temáticos
- Confirmaciones con iconos y mensajes claros
- Auto-cierre de alertas de éxito después de 3 segundos
- Animaciones suaves en transiciones
- Feedback visual inmediato en todas las acciones

---

## 🔧 Tecnologías Backend

### **Controlador:**

`application/controllers/Admin.php`

**Métodos implementados:**

- `tenants()` - Lista todos los tenants
- `tenant_create()` - Crea un nuevo tenant
- `tenant_update($id)` - Actualiza un tenant
- `tenant_delete($id)` - Elimina un tenant con cascada
- `tenant_toggle($id)` - Suspende/reactiva un tenant
- `tenant_change_plan($id)` - Cambia el plan de un tenant
- `tenant_show($id)` - Muestra la ficha completa

### **Modelos utilizados:**

- `Tenant_model` - Gestión de tenants
- `Plan_model` - Gestión de planes
- `Suscripcion_model` - Gestión de suscripciones
- `Pago_model` - Gestión de pagos
- `Pedido_model` - Gestión de pedidos
- `Categoria_model` - Conteo de categorías
- `Producto_model` - Conteo de productos
- `Ajustes_model` - Creación de ajustes por defecto

### **Patrón de arquitectura:**

- **MVC estricto:** Controladores solo manejan HTTP, modelos manejan datos
- **Repository Pattern:** Modelos encapsulan acceso a datos
- **Transaction Pattern:** Operaciones críticas usan transacciones
- **Chainable Methods:** Queries complejas con métodos encadenables

---

## 🔒 Seguridad

### **Autenticación:**

- JWT con HttpOnly cookies (8 horas de validez)
- Verificación de rol `admin` en cada método
- Redirección automática si no está autorizado

### **Autorización:**

- Solo usuarios con `rol = 'admin'` pueden acceder
- Validación en constructor del controlador
- Respuestas HTTP 403 para accesos no autorizados

### **Validación de datos:**

- Sanitización de inputs con `$this->input->post(x, true)`
- Validación de IDs (deben ser enteros positivos)
- Validación de unicidad de slug
- Validación de existencia de registros antes de operaciones

### **CSRF Protection:**

- Token CSRF incluido en todas las peticiones POST
- Regeneración automática después de cada request
- Actualización del token desde cookies vía JavaScript

### **SQL Injection:**

- Uso de Active Record de CodeIgniter (prepared statements)
- Binding automático de parámetros
- Escape de HTML en vistas con `html_escape()`

---

## 📊 Flujos de Trabajo

### **Flujo: Crear Nuevo Tenant**

1. Admin hace click en "Nuevo Tenant"
2. Se abre modal con formulario
3. Se cargan planes disponibles en select
4. Admin completa datos y envía
5. Sistema valida unicidad de slug
6. Se crea tenant en BD
7. Se crean ajustes por defecto
8. Se muestra alerta de éxito
9. Se recarga tabla de tenants

### **Flujo: Suspender Tenant**

1. Admin hace click en "Suspender"
2. SweetAlert2 pide confirmación
3. Si confirma, se envía petición POST
4. Se actualiza `activo = 0`
5. Se muestra alerta de éxito
6. Se recarga página
7. El botón ahora dice "Reactivar"

### **Flujo: Cambiar Plan**

1. Admin accede a ficha del tenant
2. Click en "Cambiar Plan"
3. Se abre modal con select de planes
4. Admin selecciona nuevo plan
5. Click en "Cambiar Plan"
6. Sistema valida que plan existe
7. Se actualiza `plan_id` del tenant
8. Se cierra modal
9. Se recarga página para mostrar nuevo plan

### **Flujo: Eliminar Tenant**

1. Admin hace click en "Eliminar"
2. SweetAlert2 pide confirmación con advertencia
3. Si confirma, se envía petición DELETE
4. Sistema inicia transacción
5. Se eliminan datos en orden (items → pedidos → pagos → etc.)
6. Si todo OK, commit; si falla, rollback
7. Se muestra alerta de éxito o error
8. Se recarga tabla de tenants

---

## 📁 Archivos Modificados/Creados

### **Controlador:**

- ✅ `application/controllers/Admin.php`
  - Método `tenant_change_plan()` (nuevo)
  - Método `tenant_show()` (mejorado)

### **Vistas:**

- ✅ `application/views/admin/tenant_show.php` (renombrado de tenant_show_view.php)
  - Diseño completo con todas las secciones
  - Modal de cambio de plan
  - JavaScript integrado

### **Modelos:**

- ✅ `application/models/Tenant_model.php` (ya existía con métodos completos)
- ✅ `application/models/Plan_model.php` (ya existía)
- ✅ `application/models/Suscripcion_model.php` (ya existía)
- ✅ `application/models/Pago_model.php` (ya existía)
- ✅ `application/models/Pedido_model.php` (ya existía)

### **JavaScript:**

- ✅ `assets/js/admin.js`
  - Función `onTenantShow()` (ya existía)
  - Endpoints configurados en objeto `api`

### **Documentación:**

- ✅ `docs/GESTION_TENANTS.md` (este archivo)

---

## 🧪 Pruebas Recomendadas

### **Test 1: Crear Tenant**

1. Ir a `/admin/tenants_view`
2. Click en "Nuevo Tenant"
3. Llenar: nombre, whatsapp, seleccionar plan
4. Enviar formulario
5. ✅ Verificar: alerta de éxito, tenant aparece en tabla

### **Test 2: Ver Ficha**

1. En tabla de tenants, click en botón "Ver" (ojo)
2. ✅ Verificar: se carga página con todas las secciones
3. ✅ Verificar: estadísticas muestran números correctos
4. ✅ Verificar: plan actual se muestra correctamente
5. ✅ Verificar: tablas de pagos y pedidos tienen datos

### **Test 3: Cambiar Plan**

1. En ficha de tenant, click "Cambiar Plan"
2. Seleccionar un plan diferente
3. Click en "Cambiar Plan" del modal
4. ✅ Verificar: alerta de éxito
5. ✅ Verificar: después de recargar, el plan cambió

### **Test 4: Suspender Tenant**

1. En ficha de tenant, click "Suspender"
2. Confirmar en SweetAlert2
3. ✅ Verificar: alerta de éxito
4. ✅ Verificar: badge cambia a "Suspendido"
5. ✅ Verificar: botón ahora dice "Reactivar"
6. Intentar login como ese tenant
7. ✅ Verificar: no puede iniciar sesión

### **Test 5: Reactivar Tenant**

1. Con tenant suspendido, click "Reactivar"
2. Confirmar
3. ✅ Verificar: badge cambia a "Activo"
4. Intentar login como ese tenant
5. ✅ Verificar: puede iniciar sesión

### **Test 6: Eliminar Tenant**

1. En tabla de tenants, click "Eliminar"
2. Confirmar en SweetAlert2
3. ✅ Verificar: alerta de éxito
4. ✅ Verificar: tenant desaparece de la tabla
5. Verificar en BD:
   - ✅ No hay registros en `pedidos` de ese tenant
   - ✅ No hay registros en `productos` de ese tenant
   - ✅ No hay registros en `categorias` de ese tenant
   - ✅ No hay registros en `ajustes` de ese tenant
   - ✅ No hay registros en `users` de ese tenant

### **Test 7: Copiar URL**

1. En ficha de tenant, click en botón de copiar (📋)
2. Abrir bloc de notas y pegar (Ctrl+V)
3. ✅ Verificar: la URL del menú se copió correctamente

### **Test 8: Ver Menú Público**

1. En ficha de tenant, click "Ver Menú Público"
2. ✅ Verificar: se abre nueva pestaña
3. ✅ Verificar: se muestra el menú del restaurante

---

## 🚀 Próximas Mejoras (Pendientes)

### **Generación de QR automática:**

- Implementar función `generarQR()` en JavaScript
- Crear endpoint `POST /admin/tenant_generate_qr/[id]`
- Usar librería PHP para generar QR (ejemplo: endroid/qr-code)
- Guardar imagen en `uploads/tenants/[id]/qr.png`

### **Estadísticas avanzadas:**

- Gráficos de pedidos por mes
- Gráficos de ingresos por tenant
- Comparativa de planes más usados
- Métricas de retención de clientes

### **Gestión de suscripciones:**

- Vista dedicada para suscripciones
- Crear/renovar suscripción desde ficha
- Alertas de suscripciones próximas a vencer
- Facturación automática

### **Notificaciones:**

- Email al suspender/reactivar tenant
- Email al cambiar plan
- Notificaciones push para administradores

### **Exportación de datos:**

- Exportar lista de tenants a CSV/Excel
- Exportar ficha de tenant a PDF
- Backup individual de datos de un tenant

---

## 📞 Soporte

Para dudas o mejoras de esta funcionalidad, revisar:

- `docs/REFACTORING_MVC.md` - Arquitectura general
- `docs/API_DOCUMENTATION.md` - Endpoints disponibles
- `docs/FEATURES_ROADMAP.md` - Próximas funcionalidades

---

**Última actualización:** 18 de Octubre de 2025  
**Estado del módulo:** ✅ **Completo y funcional**
