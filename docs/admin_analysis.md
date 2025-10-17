# Análisis del Admin y plan de integración

**Fecha:** 16 de octubre de 2025

Este documento resume el análisis de los controladores existentes relevantes al panel admin, las dependencias y la forma segura de integrar un frontend admin (JS) sin pisar lógicas ya implementadas en `MY_Controller` y otros controladores.

---

## 1. Mapeo de controladores y métodos

### `MY_Controller` (application/core/MY_Controller.php)

- Controlador base. Comportamientos clave:
  - Valida autenticación en el constructor para todos los métodos excepto `login` (usa `_verify_auth()` que depende de `current_tenant_id()`).
  - Provee helpers de renderizado: `render_template()`, `render_admin_template()`, `render_view()` que cargan las vistas y los templates (`header`, `sidebar(_admin)`, `topbar`, `footer`).
  - Manejo de errores API con `_api_error($code,$msg)` que emite JSON y detiene la ejecución.
  - `validate_view_access()` limita las vistas permitidas por controlador a partir de `$allowed_views`.

> Implicación: la lógica de autenticación, renderizado y errores está centralizada aquí. Evitar duplicarla en controladores hijos.

---

### `Admin` (application/controllers/Admin.php)

- Hereda de `MY_Controller`, por tanto requiere autenticación.
- `allowed_views = ['tenants_view','planes_view','pagos_view']`.
- Vistas (render_admin_template):
  - `tenants_view()` → `admin/tenants`
  - `planes_view()` → `admin/planes`
  - `pagos_view()` → `admin/pagos`
- Endpoints JSON (API):
  - `tenants()` → GET lista tenants (JSON `{ok:true,data:...}`)
  - `tenant_create()` → POST crea tenant (inserta `tenants` y `ajustes`)
  - `planes()` → GET lista planes
  - `plan_create()` → POST crea plan
  - `pagos()` → GET lista pagos (ordenados por fecha)

> Notas: las respuestas devuelven JSON simples; en algunos endpoints faltan validaciones de entrada más estrictas.

---

### `AdminPanel` (application/controllers/AdminPanel.php)

- Método:
  - `login()` → carga la vista `admin/login` (no usa `render_admin_template` en este archivo).

---

### `App` (application/controllers/App.php)

- Panel del restaurante (owner/staff). Funcionalidades clave:
  - Vistas del panel: `dashboard_view`, `categorias_view`, `productos_view`, `ajustes_view`.
  - CRUD: `categorias()`, `categoria_create()`, `categoria_update()`, `categoria_delete()`, `productos()`, `producto_create()`, `producto_update()`, `producto_delete()`.
  - Pedidos: `pedidos()`, `pedido_create()`, `pedido()`, `pedido_update_estado()`, `pedido_delete()`.
  - Usuarios y permisos: `usuarios_list()`, `usuario_create()`, `usuario_update()`, `usuario_delete()`, `permisos_get()`, `permisos_update()`.
  - Lógica importante: `enforce_limits($tenant_id, $tipo)` que aplica límites por plan (categorías/productos).

> Implicación: `App` contiene reglas de negocio críticas (límites, permisos, notificaciones). No deben ser eludidas por el frontend ni duplicadas.

---

### `Auth` (application/controllers/Auth.php)

- `login()` → valida email/password y emite JWT con `jwt_issue()`.
- `logout()` → placeholder (no invalidación persistente del token).

> El frontend Admin debe obtener y usar este token (`Authorization: Bearer <token>`).

---

### `PublicUser` (application/controllers/PublicUser.php)

- `menu($slug)`, `api_menu()` → entrega datos públicos del menú.
- `crear_pedido()` → endpoint público para crear pedidos y devolver `whatsapp_url`.

> No modificar la lógica de pedidos al integrar admin.

---

## 2. Observaciones importantes

- La autenticación y verificación de tenant se gestionan en `MY_Controller` y dependen de `current_tenant_id()` (helper). Evitar replicar estas validaciones.
- `render_admin_template()` ya apunta a `template/sidebar_admin` y las vistas `application/views/admin/*.php` están diseñadas con SB Admin 2: es preferible usar esa plantilla en vez de crear otra desde cero.
- Los endpoints de `Admin` devuelven JSON en formato `{ok:true,data:...}`; el frontend debe observar esa convención.
- Algunas rutas API carecen de validaciones robustas (por ejemplo `tenant_create()` no valida `nombre` vacío ni colisiones de `slug`). Recomendable endurecer backend más adelante.
- No se aprecia manejo CSRF en endpoints JSON; la seguridad dependerá de JWT + HTTPS.

---

## 3. Plan de integración front-end (no invasivo, recomendado)

Objetivo: añadir una capa JavaScript que consuma los endpoints existentes sin cambiar la lógica del servidor.

Pasos mínimos (riesgo bajo):

1. Crear `assets/js/admin.js` con funciones:

   - `fetchTenants()` → GET `/admin/tenants` y `renderTenants()` para poblar el `<tbody>` dinámico.
   - `fetchPlanes()` → GET `/admin/planes` y `renderPlanes()`.
   - `fetchPagos()` → GET `/admin/pagos` y `renderPagos()`.
   - `createTenant(payload)` → POST `/admin/tenant_create`.
   - `createPlan(payload)` → POST `/admin/plan_create`.
   - `showAlert(msg, type)` y manejo de loaders.
   - Si existe token en `localStorage`, añadir header `Authorization: Bearer <token>`.

2. Adaptar mínimamente vistas `application/views/admin/*.php`:

   - Reemplazar los `<tbody>` estáticos por `<tbody id="tenants-tbody"></tbody>` (y `planes-tbody`, `pagos-tbody`).
   - Añadir identificadores a botones (por ejemplo `#btn-new-tenant`) para enganchar eventos.
   - Incluir `assets/js/admin.js` al final de cada vista (o mejor: cargar desde el template admin footer condicionalmente).

3. Validaciones en frontend: campos requeridos, formatos básicos. No sustituir validación servidor.

4. Probar con token JWT obtenido desde `Auth::login()` en Postman / UI.

---

## 4. Riesgos y consideraciones

- Si pruebas en local sin JWT, `MY_Controller` bloqueará llamadas (requiere `current_tenant_id()`), por lo que para probar debes autenticar y almacenar token.
- `tenant_create` puede crear slugs duplicados si no se valida servidor-side; considerar añadir validación en backend antes de producción.
- La ausencia de CSRF en endpoints JSON obliga a reforzar la seguridad vía JWT y HTTPS.

---

## 5. Próximos pasos inmediatos (propuesta)

1. Crear `assets/js/admin.js` (implementación inicial de lectura y renderizado).
2. Modificar mínimamente `application/views/admin/tenants.php`, `planes.php`, `pagos.php` para enganchar el JS.
3. Probar en local: obtener token con `Auth::login()` y ejecutar `fetchTenants()` desde la consola o al cargar la vista.

---

## 6. Checklist rápido para QA manual

- [ ] Obtener JWT con `Auth::login()` y guardarlo en `localStorage`.
- [ ] Abrir la vista `admin/tenants` y confirmar que la tabla se llena desde `/admin/tenants`.
- [ ] Intentar crear un tenant desde UI y verificar respuesta del servidor.
- [ ] Listar `planes` y `pagos` desde sus vistas.
- [ ] Verificar que las respuestas con errores son manejadas por `admin.js`.

---

## 7. Documentación y seguimiento

- Mantener este archivo actualizado en `docs/admin_analysis.md`.
- Los cambios propuestos se registrarán en el TODO central (`manage_todo_list`).

---

Si confirmas, procedo a crear `assets/js/admin.js` y a adaptar las vistas (`tenants.php`, `planes.php`, `pagos.php`) para integrarlo. Si prefieres, puedo crear primero solo `admin.js` y esperar tu revisión antes de tocar las vistas.
