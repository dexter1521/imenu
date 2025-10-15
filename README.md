1. Arquitectura (multi-tenant simple)

Dominio principal: imenu.com.mx

Subdominios por cliente: tacoslupita.imenu.com.mx (cada cliente tendra su propio subdominio)

QR estable: imenu.com.mx/r/{slug} → resuelve al menú del negocio.

Multitenancy: tenant_id en todas las tablas de datos.

2. Modelo de datos (mínimo viable)
   tenants(id, nombre, slug, subdominio, logo_url, color_primario, color_secundario, whatsapp, activo, plan_id, created_at)
   users(id, tenant_id, nombre, email, pass_hash, rol['owner','staff','admin'], activo, created_at)
   planes(id, nombre, precio_mensual, limite_categorias, limite_items, ads, created_at)

categorias(id, tenant_id, nombre, orden, activo)
productos(id, tenant_id, categoria_id, nombre, descripcion, precio, img_url, orden, activo, destacado)
ajustes(id, tenant_id, idioma, moneda, formato_precio, notas, show_precios, show_imgs)

suscripciones(id, tenant_id, plan_id, inicio, fin, estatus['trial','active','overdue'])
pagos(id, tenant_id, monto, concepto, referencia, metodo, fecha, status)

3. Funcionalidades del MVP (Semana 1)

Público: vista de menú por categorías, búsqueda, botón WhatsApp por producto/negocio, tema por colores, soporte a fotos, precios y notas.

Panel restaurante (owner/staff): CRUD categorías y productos, orden manual, activar/desactivar, subir imágenes.

Admin SaaS: gestión de tenants, planes, límites y estatus.

Onboarding rápido: alta de negocio → genera slug + QR en PNG.

SEO/Lite: URL limpias y metatags básicos por tenant.

4. Límites por plan (ejemplo)

Free: 5 categorías / 50 productos, “hecho con iMenu”, sin subdominio.

Pro ($199/mes): 20/300, sin marca, subdominio, WhatsApp por ítem.

Plus ($299/mes): ilimitado razonable, subdominio + analíticas básicas.

5. Flujo de rutas (público y panel)

Público:

GET /r/{slug} → menú (detecta tenant por slug).

GET /r/{slug}/q/{categoria?} → ancla a categoría.

Panel:

GET /app/login

GET /app/dashboard

CRUD /app/categorias, CRUD /app/productos, /app/ajustes

Admin SaaS:

GET /admin/tenants, /admin/planes, /admin/pagos

6. Stack – Elige uno (te dejo ambos listos)
   A) Rápido para vender ya: CodeIgniter 3 + Bootstrap/JS

Estructura:

application/controllers: Public.php (rutas /r), App.php (panel), Admin.php

application/models: Tenant_model, Categoria_model, Producto_model, Plan_model, Suscripcion_model

application/helpers: tenant_helper (resolver tenant por slug/subdominio), qr_helper (generar QR)

Middleware simple: filtro por rol en constructor de controladores del panel.

Auth: Ion Auth o auth propio sencillo (bcrypt).

Storage imágenes: /uploads/{tenant_id}/... o MinIO/S3 más adelante.

Cache: CI Cache driver (file) en páginas públicas por tenant_id.

Migrations: SQL planos (rápido).

Ventaja: estás facturando en 1–2 semanas.

B) Escalable/SaaS clásico: Laravel + Vue 3 (Quasar opcional)

Laravel: Jetstream/Fortify para Auth, Policies por tenant_id, Queues para thumbnails, Cashier (si piensas Stripe).

Vue 3: SPA para panel; público SSR simple con Blade (carga muy rápida).

Storage: public/storage/tenants/{id}/... (S3 más adelante).

Ventaja: crecimiento ordenado, planes/pagos integrados más fácil.

Por el momento se arranco con CI3 (A) para salir y vender esta semana. 
Dejando Laravel/Vue (B) como refactor v2 cuando tengas 30–50 clientes.

7. Esquema de control de acceso

users.rol:

owner: todo su tenant.

staff: CRUD limitado (sin facturación/planes).

admin: global (SaaS).

Siempre filtra por tenant_id en modelos (helpers para whereTenant()).

8. Generación de QR

Contenido: https://imenu.com.mx/r/{slug}

En el alta del tenant: genera y guarda PNG (/uploads/{tenant}/qr.png) para que lo impriman.

9. Entregables de la Semana (roadmap express)

Día 1–2

Proyecto CI3, BD y migrations, seeds de planes.

Modelos base y helper resolveTenant(slug); Public::menu($slug).

Día 3

CRUD categorías/productos con orden drag, subida de imágenes.

Tema por color (CSS vars por tenant).

Día 4

Admin SaaS: alta/baja tenant, asignar plan, generar QR, límites por plan (políticas en modelo).

Día 5

Página pública optimizada (cache 5 min por tenant).

Exporta QR y plantilla PDF A6 para imprimir.

Día 6

Trial 14 días (campo en suscripciones), aviso en panel.

Página de precios + onboarding (form simple).

Día 7

Hardening básico (rate limit login, tamaño imagen, mime types).

Demo y primer cliente piloto.

10. Lógica de límites por plan (pseudo)

En Producto_model::create()/Categoria_model::create() contar registros por tenant_id.

Si supera el límite del plan → error controlado y CTA “sube de plan”.

11) Venta y precios (rápido)

$799 instalación (incluye alta, QR y carga inicial de 30 productos).

$199/mes (hosting, soporte, cambios menores).

Promo lanzamiento: 3 meses a $399 (pago único), luego $199/mes.

Upsell: fotos pro del menú, mini-landing, Google Business, pedidos por WhatsApp.
