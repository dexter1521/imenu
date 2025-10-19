# Gestión de Planes y Suscripciones - Panel Admin

**Fecha de implementación:** 18 de Octubre de 2025  
**Módulo:** Panel de Administración SaaS

---

## 📋 Resumen

Sistema completo de gestión de planes de suscripción y suscripciones activas para los tenants del sistema SaaS iMenu. Permite a los administradores crear planes con diferentes límites, asignar suscripciones a tenants, y monitorear el estado de las suscripciones.

---

## ✅ Funcionalidades Implementadas

### 1. **Gestión de Planes**

#### **Listar Planes**

**Endpoint:** `GET /admin/planes`  
**Vista:** `/admin/planes_view`

**Características:**

- Tabla con todos los planes del sistema
- Columnas: ID, Nombre, Precio Mensual, Límite Categorías, Límite Productos, Publicidad, Tenants Activos
- Cards visuales con información destacada de cada plan
- Botones de acción: Editar, Eliminar

#### **Crear Plan**

**Endpoint:** `POST /admin/plan_create`

**Campos:**

- `nombre` (requerido) - Nombre del plan (ej: "Free", "Pro", "Enterprise")
- `precio_mensual` - Precio en formato decimal (ej: 199.00)
- `limite_categorias` - Número máximo de categorías permitidas
- `limite_items` - Número máximo de productos permitidos
- `ads` - Checkbox para indicar si incluye publicidad (0 = sin ads, 1 = con ads)

**Validaciones:**

- Solo usuarios con rol `admin` pueden crear planes
- El nombre es obligatorio
- El precio debe ser un número válido
- Los límites deben ser enteros positivos

#### **Editar Plan**

**Endpoint:** `POST /admin/plan_update/[id]`

**Funcionalidad:**

- Permite actualizar cualquier campo del plan
- Validación de tipos de datos (float para precio, int para límites)
- Modal prellenado con datos actuales

#### **Eliminar Plan**

**Endpoint:** `POST /admin/plan_delete/[id]`

**Consideraciones:**

- Solo elimina el registro del plan
- No afecta a tenants que ya lo tienen asignado
- Confirmación con SweetAlert2

---

### 2. **Gestión de Suscripciones**

#### **Listar Suscripciones**

**Endpoint:** `GET /admin/suscripciones`  
**Vista:** `/admin/suscripciones_view`

**Características:**

- Tabla con todas las suscripciones del sistema
- Columnas: ID, Tenant, Plan, Fecha Inicio, Fecha Fin, Estado, Días Restantes
- **Cálculo automático de días restantes** con código de colores:
  - 🟢 Verde: Más de 30 días
  - 🟡 Amarillo: Entre 1-30 días
  - 🔴 Rojo: Vencida
- Estados posibles: `activa`, `pendiente`, `expirada`, `cancelada`

**Tarjetas de estadísticas:**

1. **Suscripciones Activas** - Count de suscripciones con más de 30 días
2. **Próximas a Vencer** - Count de suscripciones entre 1-30 días
3. **Expiradas** - Count de suscripciones vencidas

#### **Crear Suscripción**

**Endpoint:** `POST /admin/suscripcion_create`

**Campos:**

- `tenant_id` (requerido) - Select con todos los tenants disponibles
- `plan_id` (requerido) - Select con todos los planes disponibles
- `inicio` (requerido) - Fecha de inicio (date input)
- `fin` (requerido) - Fecha de finalización (date input)
- `estatus` - Select con opciones: activa, pendiente, expirada, cancelada

**Valores por defecto:**

- Fecha inicio: Hoy
- Fecha fin: 1 mes después
- Estado: activa

**Validaciones:**

- Tenant y plan deben existir en la base de datos
- Fechas son obligatorias
- Solo admin puede crear suscripciones

**Proceso:**

1. Validar que el tenant existe
2. Validar que el plan existe
3. Crear registro en tabla `suscripciones`
4. Retornar ID de la nueva suscripción

#### **Editar Suscripción**

**Endpoint:** `POST /admin/suscripcion_update/[id]`

**Campos actualizables:**

- plan_id - Cambiar a otro plan
- inicio - Modificar fecha de inicio
- fin - Extender o acortar periodo
- estatus - Cambiar estado (activar, suspender, etc.)

**Casos de uso:**

- Extender suscripción: Actualizar campo `fin`
- Cambiar plan: Actualizar campo `plan_id`
- Suspender: Cambiar `estatus` a 'cancelada'
- Reactivar: Cambiar `estatus` a 'activa'

#### **Eliminar Suscripción**

**Endpoint:** `POST /admin/suscripcion_delete/[id]`

**Consideraciones:**

- Eliminación permanente del registro
- No afecta al plan del tenant (campo `tenants.plan_id` permanece)
- Confirmación obligatoria con advertencia

#### **Histórico de Suscripciones por Tenant**

**Endpoint:** `GET /admin/tenant_suscripciones/[tenant_id]`

**Funcionalidad:**

- Modal con todas las suscripciones del tenant
- Información del tenant en la parte superior
- Tabla con: ID, Plan, Inicio, Fin, Estado, Precio
- **Join con tabla planes** para mostrar nombre y precio
- Ordenadas por fecha de inicio (más recientes primero)

**Uso:**

- Botón "Ver Histórico" (icono 🕒) en cada fila de la tabla
- Permite ver todas las suscripciones pasadas y actuales
- Útil para auditoría y seguimiento

---

## 🎨 Diseño e Interfaz

### **Vista de Planes (`planes.php`)**

**Estructura:**

1. **Header** con título y botón "Nuevo Plan"
2. **Cards de planes destacados** (3 columnas):
   - Plan Free (borde azul info)
   - Plan Pro (borde verde success)
   - Card "Crear Nuevo Plan" (llamada a la acción)
3. **Tabla completa de planes** con todas las columnas
4. **Modal de creación/edición** con formulario

### **Vista de Suscripciones (`suscripciones.php`)**

**Estructura:**

1. **Header** con título y botón "Nueva Suscripción"
2. **3 Tarjetas de estadísticas**:
   - Activas (verde)
   - Próximas a vencer (amarillo)
   - Expiradas (rojo)
3. **Tabla de suscripciones** con:
   - Días restantes calculados dinámicamente
   - Badges de color según estado
   - Botones: Ver Histórico, Editar, Eliminar
4. **Modal de crear/editar suscripción**:
   - Selects dinámicos de tenants y planes
   - Inputs de fecha con valores por defecto
   - Select de estado
5. **Modal de histórico**:
   - Información del tenant
   - Tabla con todas sus suscripciones
   - Join con planes para mostrar detalles

### **Código de colores:**

- 🟢 **Verde (success)**: Suscripción activa con > 30 días
- 🟡 **Amarillo (warning)**: Suscripción activa con 1-30 días restantes / Estado pendiente
- 🔴 **Rojo (danger)**: Suscripción vencida / Estado expirado o cancelado
- ⚫ **Gris (secondary)**: Estado inactivo o sin datos

---

## 🔧 Tecnologías Backend

### **Controlador: Admin.php**

**Métodos de Planes:**

```php
planes()              // GET - Listar todos los planes
plan_create()         // POST - Crear nuevo plan
plan_update($id)      // POST - Actualizar plan
plan_delete($id)      // POST - Eliminar plan
```

**Métodos de Suscripciones:**

```php
suscripciones()           // GET - Listar todas las suscripciones
suscripcion_create()      // POST - Crear nueva suscripción
suscripcion_update($id)   // POST - Actualizar suscripción
suscripcion_delete($id)   // POST - Eliminar suscripción
tenant_suscripciones($id) // GET - Histórico de un tenant
```

**Vista:**

```php
planes_view()          // Renderiza vista de planes
suscripciones_view()   // Renderiza vista de suscripciones
```

### **Modelo: Plan_model.php**

**Métodos:**

```php
get_all()         // Obtener todos los planes
get($id)          // Obtener un plan por ID
insert($data)     // Crear nuevo plan
update($id, $data) // Actualizar plan
delete($id)       // Eliminar plan
```

**Estructura de tabla `planes`:**

```sql
CREATE TABLE planes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    precio_mensual DECIMAL(10,2) DEFAULT 0.00,
    limite_categorias INT DEFAULT 5,
    limite_items INT DEFAULT 50,
    ads TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### **Modelo: Suscripcion_model.php (Extendido)**

**Métodos nuevos:**

```php
get_by_tenant($tenant_id)          // Todas las suscripciones de un tenant
get_active_by_tenant($tenant_id)   // Suscripción activa actual
insert($data)                      // Crear suscripción
update($id, $data)                 // Actualizar suscripción
delete($id)                        // Eliminar suscripción
count_active()                     // Contar suscripciones activas
```

**Métodos existentes (chainable):**

```php
where($field, $value)
order_by($field, $direction)
limit($limit, $offset)
get_one()        // Para chainable
get_results()    // Para chainable
```

**Estructura de tabla `suscripciones`:**

```sql
CREATE TABLE suscripciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT NOT NULL,
    plan_id INT NOT NULL,
    inicio DATE NOT NULL,
    fin DATE NOT NULL,
    estatus ENUM('activa','pendiente','expirada','cancelada') DEFAULT 'activa',
    FOREIGN KEY (tenant_id) REFERENCES tenants(id),
    FOREIGN KEY (plan_id) REFERENCES planes(id)
);
```

---

## 💻 JavaScript (admin.js)

### **Funciones de Suscripciones:**

```javascript
// Carga y renderizado
fetchSuscripciones(); // Obtener del servidor
renderSuscripciones(rows); // Renderizar tabla
updateSuscripcionesStats(rows); // Actualizar tarjetas estadísticas

// Modal y formulario
openSuscripcionModal(suscripcion); // Abrir modal (crear o editar)
loadTenantsForSelect(); // Cargar tenants en select
loadPlanesForSelect(selectId); // Cargar planes en select

// CRUD
createSuscripcion(payload); // POST crear
updateSuscripcion(id, payload); // POST actualizar
onSuscripcionEdit(e); // Handler editar
onSuscripcionDelete(e); // Handler eliminar

// Histórico
onSuscripcionHistorico(e); // Abrir modal histórico
renderHistoricoSuscripciones(rows, tenant); // Renderizar tabla histórico

// Utilidades
formatDate(dateStr); // Formato DD/MM/YYYY
ucfirst(str); // Capitalizar primera letra
```

### **Cálculo de Días Restantes:**

```javascript
const hoy = new Date();
const fin = new Date(r.fin);
const diffTime = fin - hoy;
const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

if (r.estatus === "activa") {
	if (diffDays > 30) {
		// Verde: Más de 30 días
		diasRestantes = `<span class="text-success">${diffDays} días</span>`;
		badgeClass = "success";
	} else if (diffDays > 0) {
		// Amarillo: Entre 1-30 días
		diasRestantes = `<span class="text-warning">${diffDays} días</span>`;
		badgeClass = "warning";
	} else {
		// Rojo: Vencida
		diasRestantes = `<span class="text-danger">Vencida hace ${Math.abs(
			diffDays
		)} días</span>`;
		badgeClass = "danger";
	}
}
```

### **API Endpoints (objeto `api`):**

```javascript
const api = {
	// ... otros endpoints
	suscripciones: url("admin/suscripciones"),
	suscripcion_create: url("admin/suscripcion_create"),
	suscripcion_update: (id) => url("admin/suscripcion_update/" + id),
	suscripcion_delete: (id) => url("admin/suscripcion_delete/" + id),
	tenant_suscripciones: (id) => url("admin/tenant_suscripciones/" + id),
};
```

---

## 🔒 Seguridad

### **Autenticación y Autorización:**

- JWT con HttpOnly cookies (validez 8 horas)
- Verificación de rol `admin` en todos los métodos
- Respuestas HTTP 403 para accesos no autorizados

### **Validación de Datos:**

- Sanitización con `$this->input->post(x, true)`
- Validación de tipos (int, float, date)
- Validación de existencia de registros relacionados (tenant, plan)
- Confirmaciones antes de eliminar

### **Integridad de Datos:**

- Foreign keys en base de datos
- Validación de que tenant y plan existan antes de crear suscripción
- No permite eliminar planes o tenants con suscripciones activas (opcional)

---

## 📊 Flujos de Trabajo

### **Flujo: Crear Plan**

1. Admin hace click en "Nuevo Plan"
2. Se abre modal con formulario vacío
3. Admin completa: nombre, precio, límites, ads
4. Click en "Guardar"
5. Sistema valida datos
6. Se crea plan en BD
7. Alerta de éxito y recarga tabla

### **Flujo: Editar Plan**

1. Admin hace click en "Editar" en tabla de planes
2. Se abre modal prellenado con datos actuales
3. Admin modifica campos necesarios
4. Click en "Guardar"
5. Sistema actualiza plan
6. Alerta de éxito y recarga tabla

### **Flujo: Crear Suscripción**

1. Admin hace click en "Nueva Suscripción"
2. Se abre modal con:
   - Select de tenants (cargado dinámicamente)
   - Select de planes (cargado dinámicamente)
   - Fecha inicio (por defecto: hoy)
   - Fecha fin (por defecto: +1 mes)
   - Estado (por defecto: activa)
3. Admin selecciona tenant y plan
4. Ajusta fechas si es necesario
5. Click en "Guardar"
6. Sistema valida:
   - Que tenant existe
   - Que plan existe
   - Que fechas son válidas
7. Se crea suscripción
8. Alerta de éxito y recarga tabla

### **Flujo: Ver Histórico de Tenant**

1. Admin hace click en botón "🕒" (Ver Histórico) en tabla de suscripciones
2. Sistema obtiene `tenant_id` del atributo data
3. Hace request a `/admin/tenant_suscripciones/[id]`
4. Backend:
   - Verifica que tenant existe
   - Hace JOIN con tabla planes
   - Retorna todas las suscripciones ordenadas por fecha
5. Se abre modal con:
   - Card con info del tenant (nombre, slug, estado)
   - Tabla con todas sus suscripciones (ID, plan, fechas, estado, precio)
6. Admin puede cerrar modal

### **Flujo: Extender Suscripción**

1. Admin identifica suscripción próxima a vencer (badge amarillo)
2. Click en "Editar"
3. Modal se abre con datos actuales
4. Admin modifica campo "Fecha Fin" a una fecha futura
5. Click en "Guardar"
6. Sistema actualiza fecha
7. Días restantes se recalculan automáticamente
8. Badge cambia de color según nuevo periodo

---

## 📁 Archivos Modificados/Creados

### **Backend:**

- ✅ `application/controllers/Admin.php`
  - 5 métodos de suscripciones (nuevos)
  - 1 método de vista (nuevo): `suscripciones_view()`
  - Array `$allowed_views` actualizado
- ✅ `application/models/Suscripcion_model.php`
  - 6 métodos nuevos (get_by_tenant, get_active_by_tenant, insert, update, delete, count_active)
  - Métodos chainables mantenidos

### **Frontend:**

- ✅ `application/views/admin/suscripciones.php` (nueva)
  - Vista completa con tarjetas y tablas
  - 2 modales (crear/editar y histórico)
- ✅ `application/views/admin/planes.php` (existía, ya funcional)
- ✅ `application/views/template/sidebar_admin.php`
  - Enlace a Suscripciones agregado

### **JavaScript:**

- ✅ `assets/js/admin.js`
  - 14 funciones nuevas para suscripciones
  - 5 endpoints nuevos en objeto `api`
  - Cálculo de días restantes
  - Renderizado de histórico

### **Configuración:**

- ✅ `application/config/routes.php`
  - Ruta `admin/suscripciones_view` agregada

### **Documentación:**

- ✅ `docs/GESTION_PLANES_SUSCRIPCIONES.md` (este archivo)

---

## 🧪 Pruebas Recomendadas

### **Test 1: Crear Plan**

1. Ir a `/admin/planes_view`
2. Click "Nuevo Plan"
3. Llenar: nombre "Premium", precio 299, límites 50/500, sin ads
4. Guardar
5. ✅ Verificar: plan aparece en tabla

### **Test 2: Editar Plan**

1. Click "Editar" en plan creado
2. Cambiar precio a 399
3. Guardar
4. ✅ Verificar: precio se actualizó

### **Test 3: Crear Suscripción**

1. Ir a `/admin/suscripciones_view`
2. Click "Nueva Suscripción"
3. Seleccionar tenant y plan
4. Fechas: hoy hasta +30 días
5. Estado: activa
6. Guardar
7. ✅ Verificar: suscripción aparece en tabla
8. ✅ Verificar: días restantes muestra ~30 días en amarillo

### **Test 4: Ver Histórico**

1. En tabla de suscripciones, click botón "🕒"
2. ✅ Verificar: modal se abre
3. ✅ Verificar: muestra info del tenant
4. ✅ Verificar: tabla muestra suscripciones con nombres de planes

### **Test 5: Extender Suscripción**

1. Editar suscripción existente
2. Cambiar fecha fin a +60 días desde hoy
3. Guardar
4. ✅ Verificar: días restantes actualiza a ~60 días
5. ✅ Verificar: badge cambia a verde

### **Test 6: Estadísticas**

1. Crear 3 suscripciones:
   - Una con 60 días (activa)
   - Una con 15 días (próxima a vencer)
   - Una con fecha fin en el pasado (expirada)
2. ✅ Verificar tarjetas:
   - Activas: 1
   - Próximas a vencer: 1
   - Expiradas: 1

### **Test 7: Eliminar Suscripción**

1. Click "Eliminar" en una suscripción
2. Confirmar en SweetAlert2
3. ✅ Verificar: suscripción desaparece
4. ✅ Verificar: estadísticas se actualizan

### **Test 8: Validación de Datos**

1. Intentar crear suscripción sin seleccionar tenant
2. ✅ Verificar: error de validación
3. Intentar crear con fecha fin anterior a fecha inicio
4. ✅ Verificar: (opcional) advertencia o error

---

## 🚀 Próximas Mejoras (Pendientes)

### **Validaciones adicionales:**

- Validar que fecha fin > fecha inicio
- Validar que no haya solapamiento de suscripciones activas
- Advertir si tenant ya tiene suscripción activa

### **Automatizaciones:**

- Tarea cron para actualizar estados de suscripciones vencidas
- Email automático al tenant cuando faltan 7 días para vencer
- Email al admin cuando hay suscripciones vencidas

### **Reportes:**

- Gráfico de ingresos por mes (precio \* suscripciones)
- Gráfico de distribución de planes
- Reporte de churn (cancelaciones)
- Proyección de ingresos recurrentes (MRR)

### **Funcionalidades avanzadas:**

- Renovación automática de suscripciones
- Upgrade/downgrade de plan con prorrateo
- Periodo de prueba (trial)
- Cupones y descuentos
- Facturas automáticas en PDF

### **UX:**

- Calendario visual de suscripciones
- Timeline de histórico del tenant
- Alertas en dashboard principal
- Filtros avanzados en tabla
- Exportar suscripciones a CSV/Excel

---

## 📞 Soporte

Para dudas o mejoras de esta funcionalidad, revisar:

- `docs/GESTION_TENANTS.md` - Gestión de tenants
- `docs/REFACTORING_MVC.md` - Arquitectura general
- `docs/API_DOCUMENTATION.md` - Endpoints disponibles

---

**Última actualización:** 18 de Octubre de 2025  
**Estado del módulo:** ✅ **Completo y funcional**
