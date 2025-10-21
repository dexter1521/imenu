# 🧾 iMenu – Plataforma SaaS de Menús Digitales (CodeIgniter 3)

**iMenu** es una solución SaaS desarrollada con **CodeIgniter 3** y **Bootstrap (SB Admin 2)** que permite a restaurantes, cafés y negocios de alimentos gestionar sus menús digitales, recibir pedidos en línea y compartirlos mediante códigos QR.  
Incluye un **panel multi-tenant** para cada negocio y un **panel administrativo central (SaaS)** para el control global de suscripciones, pagos y planes.

---

## 🚀 Características Principales

### 🔐 Autenticación y Seguridad

- Inicio de sesión con **JWT** (JSON Web Token).
- Roles: `admin` (SaaS), `owner` (propietario del menú) y `staff` (colaborador).
- Permisos granulares configurables (productos, categorías, ajustes, estadísticas).
- Middleware de protección `jwt_require()` en todos los endpoints privados.

### 🧩 Módulos Principales

#### 🏢 Panel del Administrador SaaS (`/api/admin/*`)

- Gestión global de **tenants** (negocios registrados).
- CRUD completo de **planes** de suscripción.
- Visualización de **pagos** realizados.
- **Dashboard SaaS** (en desarrollo): estadísticas globales de uso, ingresos, tenants activos/inactivos.

#### 🍔 Panel del Tenant (`/api/app/*`)

- **Dashboard con métricas en tiempo real:** pedidos hoy, ingresos, productos activos, categorías.
- CRUD completo de **categorías** y **productos** con carga de imágenes.
- **Ajustes del restaurante** (4 secciones):
  - Información general (nombre, teléfono, email, dirección)
  - Personalización visual (logo, color primario, mostrar precios/imágenes)
  - Configuración regional (idioma, moneda, formato de precio, zona horaria)
  - Mensajes personalizados (bienvenida, notas del menú, mensaje de pedido)
  - Horarios de atención (7 días con apertura/cierre)
- **Gestión de staff:** invitaciones, permisos granulares (productos, categorías, ajustes, estadísticas).
- **Módulo de pedidos:** listado con filtros (estado, método de pago, fechas, cliente), acciones por estado.
- **Visualización de plan:** uso de recursos (categorías, productos, pedidos/mes) con barras de progreso.
- **Auto-refresh** en dashboard (cada 60 segundos).

#### 🌐 Módulo Público (`/r/{slug}`)

- Menú web público con diseño responsive.
- Permite realizar pedidos (nombre, teléfono, método de pago).
- Generación automática de mensaje de pedido vía **WhatsApp**.
- Opción de compartir por **QR** generado automáticamente.

---

## 🛠️ Arquitectura Técnica

### ⚙️ Backend

- **Framework:** CodeIgniter 3
- **Lenguaje:** PHP 7.2+
- **Base de datos:** MySQL / MariaDB
- **Librerías principales:**
  - `JWT.php` – generación y validación de tokens.
  - `auth_helper.php` – helpers para autenticación y roles.
  - `email` – envío de credenciales y notificaciones.
- **Modelos organizados:** `Tenant_model`, `Plan_model`, `Pago_model`, `User_model`, `Pedido_model`, etc.

### 🧱 Frontend

- **Framework visual:** SB Admin 2 (Bootstrap 4.6)
- **JavaScript:** Vanilla JS ES6+ (migrado de jQuery)
- **Bibliotecas:**
  - **SweetAlert2** – notificaciones y confirmaciones elegantes
  - **FontAwesome 5** – iconografía
- **Arquitectura modular:** Código JS separado en `assets/js/` (app.js, ajustes.js)
- **Diseño responsive** preparado para móviles (vistas `/views/public/*` y `/views/app/*`)

---

## 🧩 Estructura de Carpetas

```
imenu/
├── application/
│   ├── controllers/
│   │   ├── Admin.php           # Controlador SaaS
│   │   ├── AdminAuth.php       # Autenticación admin
│   │   ├── AdminPanel.php      # Panel administrativo
│   │   ├── App.php             # Controlador Tenant (1000+ líneas)
│   │   ├── TenantAuth.php      # Autenticación tenant
│   │   ├── TenantPanel.php     # Panel tenant
│   │   └── PublicUser.php      # Menú público / pedidos
│   ├── models/
│   │   ├── Tenant_model.php
│   │   ├── Plan_model.php
│   │   ├── Pago_model.php
│   │   ├── Pedido_model.php
│   │   ├── User_model.php
│   │   ├── Permission_model.php
│   │   ├── Categoria_model.php
│   │   ├── Producto_model.php
│   │   └── Ajustes_model.php
│   ├── views/
│   │   ├── app/                # Panel del tenant
│   │   │   ├── dashboard.php
│   │   │   ├── categorias.php
│   │   │   ├── productos.php
│   │   │   ├── pedidos.php
│   │   │   ├── usuarios.php
│   │   │   ├── plan.php
│   │   │   └── ajustes.php
│   │   ├── admin/              # Panel SaaS
│   │   ├── public/             # Menú público
│   │   └── template/           # Layouts compartidos
│   ├── helpers/
│   │   ├── auth_helper.php     # JWT y roles
│   │   └── tenant_helper.php   # Resolución de tenants
│   ├── hooks/
│   │   ├── AuthHook.php        # Protección de rutas
│   │   └── TenantHook.php      # Aislamiento multi-tenant
│   └── config/
│       └── routes.php          # Rutas organizadas por sección
├── assets/
│   └── js/
│       ├── app.js              # Helpers + Categories + Products
│       └── ajustes.js          # Módulo de ajustes completo
├── db/
│   ├── notifications_schema.sql
│   ├── add_subscription_fields.sql
│   └── ajustes_schema.sql
├── docs/                       # Documentación técnica
└── install.php                 # Instalador inicial
```

---

## 💾 Instalación y Configuración

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/dexter1521/imenu.git
   cd imenu
   ```
2. Configurar base de datos

Edita install.php para definir tus credenciales (DB_HOST, DB_USER, DB_PASS, etc.).

Ejecuta en navegador:

http://localhost/imenu/install.php

Este script creará automáticamente las tablas, el tenant demo y el usuario administrador.

3. Configurar CodeIgniter

Edita application/config/config.php → define tu $config['base_url'].

Edita application/config/database.php → conecta tu base MySQL.

**4. Eliminar el instalador por seguridad**

```bash
rm install.php  # Linux/Mac
del install.php # Windows
```

**5. Ejecutar scripts SQL adicionales**

Para habilitar todas las funcionalidades del dashboard y ajustes:

```bash
# Campos de suscripción y tabla de planes
mysql -u root -p imenu < db/add_subscription_fields.sql

# Tabla de ajustes del restaurante
mysql -u root -p imenu < db/ajustes_schema.sql
```

**6. Acceder al sistema**

**Admin SaaS:**

```
URL: http://localhost/imenu/admin/login
Usuario: admin@imenu.com
Contraseña: admin123
```

**Tenant demo:**

```
URL Panel: http://localhost/imenu/app/login
Usuario: owner@demo.com
Contraseña: demo123

URL Menú Público: http://localhost/imenu/r/demo
```

---

## 📡 Endpoints Principales de la API

### Autenticación

| Método | Endpoint          | Descripción                        |
| ------ | ----------------- | ---------------------------------- |
| `POST` | `/api/auth/login` | Inicia sesión y devuelve token JWT |

### Panel Tenant

| Método   | Endpoint                      | Descripción                      |
| -------- | ----------------------------- | -------------------------------- |
| `GET`    | `/api/app/dashboard_data`     | Estadísticas del tenant          |
| `GET`    | `/api/app/categorias`         | Lista categorías                 |
| `POST`   | `/api/app/categoria`          | Crea una categoría               |
| `PUT`    | `/api/app/categoria/{id}`     | Actualiza categoría              |
| `DELETE` | `/api/app/categoria/{id}`     | Elimina categoría                |
| `GET`    | `/api/app/productos`          | Lista productos                  |
| `POST`   | `/api/app/producto`           | Crea un producto                 |
| `PUT`    | `/api/app/producto/{id}`      | Actualiza producto               |
| `DELETE` | `/api/app/producto/{id}`      | Elimina producto                 |
| `GET`    | `/api/app/ajustes`            | Obtiene configuración del tenant |
| `POST`   | `/api/app/ajustes`            | Actualiza configuración          |
| `GET`    | `/api/app/pedidos`            | Lista pedidos con filtros        |
| `POST`   | `/api/app/pedido/{id}/estado` | Actualiza estado de pedido       |
| `GET`    | `/api/app/usuarios`           | Lista staff del tenant           |
| `POST`   | `/api/app/usuario`            | Invita un staff con permisos     |
| `GET`    | `/api/app/plan_info`          | Información del plan y uso       |

### Menú Público

| Método | Endpoint                       | Descripción                          |
| ------ | ------------------------------ | ------------------------------------ |
| `GET`  | `/api/public/menu?slug={slug}` | Devuelve menú público JSON           |
| `POST` | `/api/public/pedido`           | Crea un pedido desde el menú público |

---

## 💳 Planes y Límites

| Plan | Precio  | Límite de Categorías | Límite de Productos | Ads |
| ---- | ------- | -------------------- | ------------------- | --- |
| Free | $0.00   | 5                    | 50                  | ✅  |
| Pro  | $199.00 | 20                   | 300                 | ❌  |

| Rol     | Descripción            | Accesos                           |
| ------- | ---------------------- | --------------------------------- |
| `admin` | Control global SaaS    | Todos los tenants, planes y pagos |
| `owner` | Propietario del tenant | CRUD completo dentro de su menú   |
| `staff` | Usuario del tenant     | Accesos limitados según permisos  |

---

## 🎯 Características Técnicas Destacadas

### 🔒 Seguridad

- **CSRF Protection** en todas las mutaciones (POST/PUT/DELETE)
- **JWT con refresh tokens** para sesiones seguras
- **Aislamiento multi-tenant** mediante hooks y middleware
- **Validación de entrada** en todos los endpoints
- **Sanitización** de datos antes de insertar en BD

### ⚡ Rendimiento

- **Auto-refresh selectivo** solo en vistas que lo necesitan
- **JavaScript modular** para carga optimizada
- **Validaciones del lado del cliente** antes de enviar al servidor
- **Caché de configuraciones** por tenant

### 🧪 Calidad de Código

- **Vanilla JavaScript** ES6+ (sin dependencias jQuery innecesarias)
- **Arquitectura MVC** estricta de CodeIgniter
- **Helpers reutilizables** para lógica común
- **Modelos con responsabilidad única**
- **Vistas separadas por rol** (admin/tenant/public)

### 📱 UX/UI

- **SweetAlert2** para todas las notificaciones
- **Confirmaciones** antes de acciones destructivas
- **Feedback visual** inmediato en formularios
- **Responsive design** mobile-first
- **Estados de carga** en botones y acciones

---

## 📚 Documentación Adicional

En la carpeta `/docs` encontrarás documentación técnica detallada:

- `API_DOCUMENTATION.md` – Endpoints completos con ejemplos
- `AUTENTICACION_JWT.md` – Flujo de autenticación y tokens
- `GESTION_TENANTS.md` – Aislamiento y multi-tenancy
- `GESTION_PLANES_SUSCRIPCIONES.md` – Sistema de planes
- `permissions-auth.md` – Sistema de permisos granulares

---

## 🚧 Roadmap

- [ ] Dashboard admin SaaS con métricas globales
- [ ] Sistema de pagos integrado (Stripe/PayPal)
- [ ] Notificaciones push en tiempo real
- [ ] App móvil para gestión de pedidos
- [ ] Sistema de cupones y descuentos
- [ ] Reportes y analytics avanzados
- [ ] Integración con delivery (Uber Eats, Rappi)
- [ ] Multi-idioma en menú público

---

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:

1. Fork del repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit de tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

---

## 📝 Licencia

Este proyecto está bajo licencia MIT. Ver archivo `LICENSE` para más detalles.

---

## 👨‍💻 Autor

**Dexter** - [@dexter1521](https://github.com/dexter1521)

---

## 🙏 Agradecimientos

- SB Admin 2 por el template base
- CodeIgniter por el framework robusto
- Firebase JWT PHP por la autenticación
- SweetAlert2 por las notificaciones elegantes
- Comunidad de desarrollo web por el apoyo constante
