# Sistema de Autenticación JWT - iMenu SaaS

## 📋 Descripción General

iMenu utiliza un sistema de autenticación basado en **JSON Web Tokens (JWT)** almacenados en cookies HTTP-only. Este sistema proporciona autenticación stateless, segura y escalable para el panel de administración SaaS y los paneles de tenants individuales.

---

## 🎯 Características Principales

- ✅ **Stateless**: No requiere sesiones del lado del servidor
- ✅ **Seguro**: Tokens firmados con secreto HMAC SHA-256
- ✅ **HTTP-only Cookies**: Previene ataques XSS
- ✅ **Multi-rol**: Soporta admin, owner, manager, empleado
- ✅ **Multi-tenant**: Aislamiento de datos por tenant_id
- ✅ **Expiración automática**: Tokens válidos por 8 horas
- ✅ **Middleware centralizado**: Validación en AuthHook

---

## 🔐 Arquitectura del Sistema

### **Flujo de Autenticación Completo**

```
┌──────────────────────────────────────────────────────────────────┐
│                    FLUJO DE AUTENTICACIÓN JWT                     │
└──────────────────────────────────────────────────────────────────┘

1. LOGIN
   Usuario → Frontend → POST /admin/auth/login
                        POST /tenant/auth/login
                              │
                              ▼
                    ┌─────────────────────┐
                    │  AdminAuth.php      │
                    │  TenantAuth.php     │
                    └──────────┬──────────┘
                              │
                              ▼
                    ┌─────────────────────┐
                    │  User_model         │
                    │  verify_password()  │
                    └──────────┬──────────┘
                              │
                    ┌─────────▼──────────┐
                    │  Validación OK?    │
                    └─────────┬──────────┘
                         YES  │  NO → 401
                              ▼
                    ┌─────────────────────┐
                    │  jwt_issue()        │
                    │  - user_id          │
                    │  - tenant_id        │
                    │  - rol              │
                    │  - nombre           │
                    │  - exp: 8h          │
                    └──────────┬──────────┘
                              │
                              ▼
                    ┌─────────────────────┐
                    │  Set-Cookie:        │
                    │  imenu_token=...    │
                    │  HttpOnly           │
                    │  SameSite=Strict    │
                    └──────────┬──────────┘
                              │
                              ▼
                         Response JSON
                         { ok: true,
                           rol: "admin",
                           tenant_id: 5 }

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

2. REQUEST PROTEGIDO
   Usuario → Frontend → GET /admin/dashboard
                              │
                              ▼
                    ┌─────────────────────┐
                    │  CodeIgniter        │
                    │  pre_controller     │
                    └──────────┬──────────┘
                              │
                              ▼
                    ┌─────────────────────┐
                    │  AuthHook           │
                    │  check_access()     │
                    └──────────┬──────────┘
                              │
                    ┌─────────▼──────────┐
                    │  Controlador       │
                    │  público?          │
                    └─────────┬──────────┘
                         NO   │  YES → Continuar
                              ▼
                    ┌─────────────────────┐
                    │  jwt_require()      │
                    │  Validar token      │
                    │  desde cookie       │
                    └──────────┬──────────┘
                              │
                    ┌─────────▼──────────┐
                    │  Token válido?     │
                    └─────────┬──────────┘
                         YES  │  NO → 401 Redirect
                              ▼
                    ┌─────────────────────┐
                    │  Validar ROL        │
                    │  y PERMISOS DB      │
                    └──────────┬──────────┘
                              │
                    ┌─────────▼──────────┐
                    │  Permisos OK?      │
                    └─────────┬──────────┘
                         YES  │  NO → 403
                              ▼
                    ┌─────────────────────┐
                    │  $CI->jwt = payload │
                    │  Disponible en      │
                    │  controlador        │
                    └──────────┬──────────┘
                              │
                              ▼
                    ┌─────────────────────┐
                    │  MY_Controller      │
                    │  constructor()      │
                    │  _init_common_data()│
                    └──────────┬──────────┘
                              │
                              ▼
                    ┌─────────────────────┐
                    │  Admin/App          │
                    │  Controller         │
                    │  dashboard()        │
                    └──────────┬──────────┘
                              │
                              ▼
                         Response HTML/JSON

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

3. LOGOUT
   Usuario → Frontend → POST /admin/auth/logout
                              │
                              ▼
                    ┌─────────────────────┐
                    │  AdminAuth.php      │
                    │  logout()           │
                    └──────────┬──────────┘
                              │
                              ▼
                    ┌─────────────────────┐
                    │  Set-Cookie:        │
                    │  imenu_token=""     │
                    │  Expires: Past      │
                    └──────────┬──────────┘
                              │
                              ▼
                         Response JSON
                         { ok: true }
```

---

## 🗂️ Componentes del Sistema

### **1. Controladores de Autenticación**

#### **AdminAuth.php**

Maneja login/logout para el panel administrativo SaaS.

**Ubicación**: `application/controllers/AdminAuth.php`

**Endpoints**:

- `POST /admin/auth/login` - Login de administrador
- `POST /admin/auth/logout` - Logout de administrador

**Validaciones**:

- Solo permite usuarios con `rol = 'admin'`
- Verifica credenciales con `User_model`
- Emite JWT válido por 8 horas

**Código de Login**:

```php
public function login()
{
    $email = $this->input->post('email');
    $pass  = $this->input->post('password');

    // Validar credenciales
    $u = $this->user_model->get_by_email($email);

    if (!$u || !$this->user_model->verify_password($pass, $u->password)) {
        return $this->output->set_status_header(401)
            ->set_output(json_encode(['ok' => false, 'msg' => 'Credenciales inválidas']));
    }

    // Validar rol admin
    if ($u->rol !== 'admin') {
        return $this->output->set_status_header(403)
            ->set_output(json_encode(['ok' => false, 'msg' => 'No autorizado como admin']));
    }

    // Emitir JWT
    $token = jwt_issue($u->id, (int)$u->tenant_id, $u->rol, 60 * 60 * 8); // 8h

    // Establecer cookie HTTP-only
    $expire = time() + 60 * 60 * 8;
    setcookie('imenu_token', $token, $expire, '/', '', false, true);

    return $this->output->set_output(json_encode([
        'ok' => true,
        'rol' => $u->rol,
        'tenant_id' => (int)$u->tenant_id,
        'token' => $token
    ]));
}
```

---

#### **TenantAuth.php**

Maneja login/logout para paneles de tenants individuales.

**Ubicación**: `application/controllers/TenantAuth.php`

**Endpoints**:

- `POST /tenant/auth/login` - Login de usuarios de tenant
- `POST /tenant/auth/logout` - Logout de usuarios

**Validaciones**:

- NO permite usuarios con `rol = 'admin'` (deben usar AdminAuth)
- Verifica credenciales con `User_model`
- Emite JWT válido por 8 horas

**Diferencia con AdminAuth**:

```php
// TenantAuth rechaza admins
if (isset($u->rol) && $u->rol === 'admin') {
    return $this->output->set_status_header(403)
        ->set_output(json_encode(['ok' => false, 'msg' => 'Use el login de administrador']));
}
```

---

### **2. AuthHook - Middleware de Validación**

**Ubicación**: `application/hooks/AuthHook.php`

**Configuración**: Se ejecuta en el evento `pre_controller` (antes de cada controlador)

**Responsabilidades**:

1. Identificar rutas públicas (login, register, etc.)
2. Validar JWT en rutas protegidas
3. Verificar rol del usuario
4. Validar permisos en base de datos
5. Dejar payload del JWT disponible en `$CI->jwt`

**Código Principal**:

```php
public function check_access()
{
    $CI = &get_instance();
    $class  = strtolower($CI->router->fetch_class());
    $method = strtolower($CI->router->fetch_method());

    // Rutas públicas (no requieren auth)
    $public_controllers = ['publicuser', 'tenantauth', 'adminauth'];
    $public_methods     = ['login', 'register', 'forgot_password', 'api_menu'];

    if (in_array($class, $public_controllers) || in_array($method, $public_methods)) {
        return; // Permitir acceso sin validación
    }

    // Validar JWT
    jwt_require(); // Lanza 401 si token inválido

    // Validar rol
    $rol = current_role();
    if (!$rol) {
        show_error('No tienes rol asignado', 403);
    }

    // Admin tiene acceso total
    if ($rol === 'admin') {
        return;
    }

    // Validar permisos de base de datos para otros roles
    $user_id   = current_user_id();
    $tenant_id = current_tenant_id();

    $permRow = $CI->db
        ->get_where('permisos', ['user_id' => $user_id, 'tenant_id' => $tenant_id], 1)
        ->row();

    // Mapear controlador a permiso
    $permission_map = [
        'productos'    => 'can_products',
        'categorias'   => 'can_categories',
        'ajustes'      => 'can_adjustments',
        'pedidos'      => 'can_manage_orders',
        'reportes'     => 'can_view_stats',
        'dashboard'    => 'can_view_stats',
    ];

    if (isset($permission_map[$class])) {
        $perm_col = $permission_map[$class];

        if (!$permRow || (int)$permRow->$perm_col !== 1) {
            show_error("No tienes permiso para acceder a <b>{$class}</b>.", 403);
        }
    }
}
```

**Rutas Públicas** (no requieren autenticación):

- `PublicUser` - Menú público de cada tenant
- `TenantAuth` - Login/logout de tenants
- `AdminAuth` - Login/logout de admin
- Método `login` en cualquier controlador
- Método `register` en cualquier controlador
- Método `forgot_password` en cualquier controlador
- Método `api_menu` - API pública de menú

---

### **3. MY_Controller - Controlador Base**

**Ubicación**: `application/core/MY_Controller.php`

**Responsabilidades**:

- Inicializar datos comunes para vistas
- Proporcionar métodos auxiliares de renderizado
- Proporcionar helpers para acceder a datos del JWT
- **NO valida autenticación** (delegada a AuthHook)

**Inicialización**:

```php
protected function _init_common_data()
{
    $this->data['page_title'] = 'iMenu';

    // AuthHook ya validó el JWT y lo dejó en $this->jwt
    if (isset($this->jwt) && is_object($this->jwt)) {
        $this->data['user_name'] = $this->jwt->nombre ?? 'Usuario';
        $this->data['user_role'] = $this->jwt->rol ?? null;
        $this->data['tenant_id'] = $this->jwt->tenant_id ?? null;
        $this->data['user_id'] = $this->jwt->sub ?? null;
    } else {
        // Fallback para rutas públicas
        $this->data['user_name'] = 'Invitado';
        $this->data['user_role'] = null;
        $this->data['tenant_id'] = null;
        $this->data['user_id'] = null;
    }
}
```

**Métodos Auxiliares**:

```php
// Obtener ID del usuario actual
protected function _current_user_id()
{
    return isset($this->jwt->sub) ? (int)$this->jwt->sub : 0;
}

// Obtener ID del tenant actual
protected function _current_tenant_id()
{
    return isset($this->jwt->tenant_id) ? (int)$this->jwt->tenant_id : 0;
}

// Obtener rol actual
protected function _current_role()
{
    return isset($this->jwt->rol) ? $this->jwt->rol : null;
}

// Validar acceso a recurso del tenant
protected function _validate_tenant_access($resource_tenant_id)
{
    $current_tenant = $this->_current_tenant_id();

    // Admin puede acceder a todo
    if ($this->_current_role() === 'admin') {
        return true;
    }

    if ((int)$resource_tenant_id !== $current_tenant) {
        $this->_api_error(403, 'Acceso denegado al recurso solicitado.');
        return false;
    }

    return true;
}
```

---

### **4. Helper: auth_helper.php**

**Ubicación**: `application/helpers/auth_helper.php`

**Funciones Disponibles**:

#### **jwt_secret()**

Retorna la clave secreta para firmar tokens.

```php
function jwt_secret()
{
    return 'CHANGE_ME_SUPER_SECRET_32CHARS_MINIMO';
}
```

⚠️ **IMPORTANTE**: Cambiar este valor en producción y guardarlo en variable de entorno.

---

#### **jwt_from_request()**

Extrae el token JWT del request (cookie o header Authorization).

```php
function jwt_from_request()
{
    // 1. Buscar en header Authorization
    $possible = [
        'HTTP_AUTHORIZATION',
        'Authorization',
        'REDIRECT_HTTP_AUTHORIZATION'
    ];

    foreach ($possible as $k) {
        if (!empty($_SERVER[$k])) {
            $hdr = $_SERVER[$k];
            if (preg_match('/Bearer\s+(\S+)/i', $hdr, $m)) {
                return trim($m[1]);
            }
        }
    }

    // 2. Fallback: buscar en cookie
    if (isset($_COOKIE['imenu_token'])) {
        return trim($_COOKIE['imenu_token']);
    }

    return null;
}
```

**Orden de Búsqueda**:

1. Header `Authorization: Bearer <token>`
2. Cookie `imenu_token`

---

#### **jwt_issue($uid, $tenant_id, $rol, $ttl)**

Emite un nuevo token JWT.

**Parámetros**:

- `$uid` (int): ID del usuario
- `$tenant_id` (int): ID del tenant (0 para admin global)
- `$rol` (string): Rol del usuario (admin, owner, manager, empleado)
- `$ttl` (int): Tiempo de vida en segundos (default: 3600 = 1 hora)

**Retorna**: String del token JWT

**Payload Generado**:

```php
$payload = [
    'iss' => base_url(),           // Emisor
    'sub' => $uid,                 // Subject (user ID)
    'tenant_id' => $tenant_id,     // ID del tenant
    'rol' => $rol,                 // Rol del usuario
    'nombre' => 'Juan Pérez',      // Nombre del usuario (desde DB)
    'iat' => time(),               // Issued at
    'nbf' => time(),               // Not before
    'exp' => time() + $ttl         // Expiration time
];
```

**Uso**:

```php
// Login exitoso, emitir token válido por 8 horas
$token = jwt_issue($user->id, $user->tenant_id, $user->rol, 60 * 60 * 8);
```

---

#### **jwt_require($roles)**

Valida que existe un JWT válido y opcionalmente verifica el rol.

**Parámetros**:

- `$roles` (string|array): Roles permitidos (opcional)

**Comportamiento**:

- Extrae token con `jwt_from_request()`
- Decodifica y valida firma
- Verifica que no haya expirado
- Valida rol si se especifica
- Almacena payload en `$CI->jwt`
- Retorna `401` si token inválido
- Retorna `403` si rol no autorizado

**Uso en AuthHook**:

```php
jwt_require(); // Solo validar que existe token

jwt_require('admin'); // Solo admins

jwt_require(['admin', 'owner']); // Admins y owners
```

---

#### **current_user_id()**

Obtiene el ID del usuario actual desde el JWT.

```php
function current_user_id()
{
    $CI = &get_instance();
    return isset($CI->jwt->sub) ? (int)$CI->jwt->sub : 0;
}
```

---

#### **current_tenant_id()**

Obtiene el ID del tenant actual desde el JWT.

```php
function current_tenant_id()
{
    $CI = &get_instance();
    return isset($CI->jwt->tenant_id) ? (int)$CI->jwt->tenant_id : 0;
}
```

---

#### **current_role()**

Obtiene el rol actual desde el JWT.

```php
function current_role()
{
    $CI = &get_instance();
    return isset($CI->jwt->rol) ? $CI->jwt->rol : null;
}
```

---

#### **jwt_decode_from_cookie()**

Decodifica el JWT desde la cookie y retorna el payload como array.

```php
function jwt_decode_from_cookie()
{
    $token = jwt_from_request();
    if (!$token) {
        return null;
    }

    try {
        $CI = &get_instance();
        $CI->load->library('JWT');

        $payload = $CI->jwt->decode($token);
        return json_decode(json_encode($payload), true);
    } catch (Exception $e) {
        log_message('error', 'Error decodificando JWT: ' . $e->getMessage());
        return null;
    }
}
```

**Retorna**:

```php
[
    'iss' => 'http://localhost/imenu/',
    'sub' => 42,
    'tenant_id' => 5,
    'rol' => 'owner',
    'nombre' => 'Juan Pérez',
    'iat' => 1729425600,
    'nbf' => 1729425600,
    'exp' => 1729454400
]
```

---

#### **is_authenticated()**

Verifica si el usuario tiene un JWT válido y no expirado.

```php
function is_authenticated()
{
    $payload = jwt_decode_from_cookie();

    if (!$payload) {
        return false;
    }

    // Verificar expiración
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        return false;
    }

    return true;
}
```

**Uso**:

```php
if (!is_authenticated()) {
    redirect('/app/login');
}
```

---

### **5. Librería JWT**

**Ubicación**: `application/libraries/JWT.php`

Wrapper de la librería Firebase JWT para integración con CodeIgniter.

**Métodos**:

- `encode($payload)` - Codifica payload en JWT
- `decode($token)` - Decodifica y valida JWT

**Instalación**:

```bash
composer require firebase/php-jwt
```

---

## 🔒 Estructura del Token JWT

### **Payload del Token**

```json
{
	"iss": "http://localhost/imenu/",
	"sub": 42,
	"tenant_id": 5,
	"rol": "owner",
	"nombre": "Juan Pérez",
	"iat": 1729425600,
	"nbf": 1729425600,
	"exp": 1729454400
}
```

**Campos**:

- `iss` (Issuer): URL base de la aplicación
- `sub` (Subject): ID del usuario (Primary Key en tabla `users`)
- `tenant_id`: ID del tenant al que pertenece el usuario
- `rol`: Rol del usuario (`admin`, `owner`, `manager`, `empleado`)
- `nombre`: Nombre completo del usuario (para mostrar en UI)
- `iat` (Issued At): Timestamp de emisión
- `nbf` (Not Before): Timestamp desde el cual es válido
- `exp` (Expiration): Timestamp de expiración

### **Ejemplo de Token Completo**

```
eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3RcL2ltZW51XC8iLCJzdWIiOjQyLCJ0ZW5hbnRfaWQiOjUsInJvbCI6Im93bmVyIiwibm9tYnJlIjoiSnVhbiBQw6lyZXoiLCJpYXQiOjE3Mjk0MjU2MDAsIm5iZiI6MTcyOTQyNTYwMCwiZXhwIjoxNzI5NDU0NDAwfQ.K5Xz2YqW8vN1jPm3LdRfT6hGsA9bC4eU7iO0pQwV2xY
```

**Estructura**:

1. **Header** (Base64): `{"typ":"JWT","alg":"HS256"}`
2. **Payload** (Base64): `{"iss":"http://localhost/imenu/",...}`
3. **Signature**: HMAC-SHA256(header + payload, secret)

---

## 🍪 Configuración de Cookies

### **Cookie HTTP-only**

```php
$cookie_name = 'imenu_token';
$expire = time() + 60 * 60 * 8; // 8 horas
$path = '/';
$domain = ''; // Auto-detectar
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
$httponly = true;

setcookie($cookie_name, $token, $expire, $path, $domain, $secure, $httponly);
```

**Cabecera HTTP Generada**:

```
Set-Cookie: imenu_token=eyJ0eXAiOiJKV1QiLCJhbGc...;
            Expires=Sun, 20 Oct 2025 16:30:00 GMT;
            Path=/;
            HttpOnly;
            SameSite=Strict;
            Secure
```

**Atributos**:

- `HttpOnly`: Previene acceso desde JavaScript (protección contra XSS)
- `SameSite=Strict`: Previene CSRF
- `Secure`: Solo se envía por HTTPS (producción)
- `Path=/`: Disponible en toda la aplicación
- `Expires`: 8 horas desde la emisión

---

## 🎭 Roles y Permisos

### **Roles del Sistema**

| Rol        | Descripción                  | Acceso                          |
| ---------- | ---------------------------- | ------------------------------- |
| `admin`    | Administrador SaaS global    | Panel admin + todos los tenants |
| `owner`    | Dueño del restaurante/tenant | Panel completo de su tenant     |
| `manager`  | Gerente del restaurante      | Gestión según permisos DB       |
| `empleado` | Empleado del restaurante     | Acceso limitado según permisos  |

### **Tabla de Permisos (Base de Datos)**

**Tabla**: `permisos`

**Columnas**:

```sql
CREATE TABLE permisos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tenant_id INT NOT NULL,
    can_products TINYINT(1) DEFAULT 0,
    can_categories TINYINT(1) DEFAULT 0,
    can_adjustments TINYINT(1) DEFAULT 0,
    can_manage_orders TINYINT(1) DEFAULT 0,
    can_view_stats TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_tenant (user_id, tenant_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);
```

**Mapeo Controlador → Permiso**:

```php
$permission_map = [
    'productos'    => 'can_products',
    'categorias'   => 'can_categories',
    'ajustes'      => 'can_adjustments',
    'pedidos'      => 'can_manage_orders',
    'reportes'     => 'can_view_stats',
    'dashboard'    => 'can_view_stats',
];
```

### **Lógica de Validación**

```
┌─────────────────────────────────────────────────────────────┐
│                   VALIDACIÓN DE PERMISOS                     │
└─────────────────────────────────────────────────────────────┘

1. AuthHook extrae rol del JWT
   │
   ├─→ rol === 'admin'?
   │   └─→ YES: Acceso total (bypass permisos DB)
   │
   └─→ NO: Continuar validación
       │
       ├─→ Buscar fila en tabla permisos
       │   WHERE user_id = ? AND tenant_id = ?
       │   │
       │   ├─→ No existe fila?
       │   │   └─→ Denegar acceso (403)
       │   │
       │   └─→ Fila encontrada
       │       │
       │       ├─→ Mapear controlador a columna
       │       │   productos → can_products
       │       │   categorias → can_categories
       │       │   etc.
       │       │
       │       └─→ Verificar valor de columna
       │           │
       │           ├─→ = 1: Permitir acceso
       │           └─→ = 0: Denegar (403)
```

---

## 🔐 Seguridad

### **Medidas Implementadas**

1. **HTTP-only Cookies**

   - Los tokens no son accesibles desde JavaScript
   - Previene robo de tokens mediante XSS

2. **SameSite=Strict**

   - La cookie solo se envía en requests del mismo sitio
   - Previene ataques CSRF

3. **Firma HMAC SHA-256**

   - Los tokens están firmados criptográficamente
   - Cualquier modificación invalida el token

4. **Expiración Automática**

   - Tokens válidos por 8 horas
   - Requiere re-login después de expiración

5. **Validación en Cada Request**

   - AuthHook valida el token antes de cada acción
   - No se confía en datos del cliente

6. **Aislamiento Multi-tenant**

   - `tenant_id` en el payload del JWT
   - Validación automática de recursos por tenant

7. **Hashing de Contraseñas**
   - Contraseñas hasheadas con `password_hash()` (bcrypt)
   - Verificación con `password_verify()`

### **Recomendaciones Adicionales**

⚠️ **IMPLEMENTAR EN PRODUCCIÓN**:

1. **Cambiar jwt_secret()**

   ```php
   // Generar secreto aleatorio de 64 caracteres
   openssl_rand_pseudo_bytes(32);
   // Guardar en variable de entorno
   ```

2. **Habilitar HTTPS**

   - Cookie `Secure` requiere HTTPS
   - Sin HTTPS, el token viaja en texto plano

3. **Rate Limiting**

   - Limitar intentos de login (ej: 5 por minuto)
   - Prevenir ataques de fuerza bruta

4. **Refresh Tokens**

   - Implementar refresh tokens de larga duración
   - Permitir renovar access token sin re-login

5. **Logging de Seguridad**

   - Registrar todos los intentos de login
   - Alertas de actividad sospechosa

6. **Blacklist de Tokens**
   - Al hacer logout, invalidar el token actual
   - Guardar en Redis con TTL

---

## 🧪 Testing

### **Probar Login (Admin)**

```bash
curl -X POST http://localhost/imenu/admin/auth/login \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "email=admin@imenu.com&password=admin123" \
  -c cookies.txt
```

**Respuesta Esperada**:

```json
{
	"ok": true,
	"rol": "admin",
	"tenant_id": 0,
	"token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**Verificar Cookie**:

```bash
cat cookies.txt
# localhost	FALSE	/	FALSE	1729454400	imenu_token	eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

### **Probar Request Protegido**

```bash
curl -X GET http://localhost/imenu/admin/dashboard \
  -b cookies.txt
```

**Respuesta Esperada**: HTML del dashboard

---

### **Probar Request sin Token**

```bash
curl -X GET http://localhost/imenu/admin/dashboard
```

**Respuesta Esperada**: Redirect 302 a `/adminpanel/login?expired=1`

---

### **Probar Logout**

```bash
curl -X POST http://localhost/imenu/admin/auth/logout \
  -b cookies.txt \
  -c cookies_after_logout.txt
```

**Respuesta Esperada**:

```json
{
	"ok": true,
	"msg": "Sesión admin cerrada"
}
```

**Verificar Cookie Eliminada**:

```bash
cat cookies_after_logout.txt
# localhost	FALSE	/	FALSE	0	imenu_token	""
```

---

## 📊 Flujo de Datos Completo

### **Caso 1: Login Exitoso de Admin**

```
1. Usuario envía credenciales
   POST /admin/auth/login
   { email: "admin@imenu.com", password: "admin123" }

2. AdminAuth valida credenciales
   - Busca usuario por email
   - Verifica password con bcrypt
   - Valida que rol = 'admin'

3. Se emite JWT
   jwt_issue(42, 0, 'admin', 28800)
   → "eyJ0eXAiOiJKV1QiLCJhbGc..."

4. Se establece cookie
   Set-Cookie: imenu_token=eyJ0eXAiOiJKV1QiLCJhbGc...;
               HttpOnly; SameSite=Strict; Expires=...

5. Response al cliente
   {
     "ok": true,
     "rol": "admin",
     "tenant_id": 0,
     "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
   }

6. Cliente almacena respuesta
   - Cookie se guarda automáticamente por el navegador
   - Frontend puede guardar rol/tenant_id en localStorage para UI
```

---

### **Caso 2: Request Protegido (Dashboard Admin)**

```
1. Usuario navega a /admin/dashboard
   GET /admin/dashboard
   Cookie: imenu_token=eyJ0eXAiOiJKV1QiLCJhbGc...

2. CodeIgniter inicia
   - Router identifica controlador: Admin
   - Router identifica método: dashboard

3. AuthHook::check_access() se ejecuta (pre_controller)

   3.1. Verificar si es ruta pública
        'admin' ∉ ['publicuser', 'tenantauth', 'adminauth']
        'dashboard' ∉ ['login', 'register', ...]
        → NO es pública, continuar validación

   3.2. Validar JWT
        jwt_require()
        → Extrae token de cookie
        → Decodifica y valida firma
        → Verifica que no haya expirado
        → Almacena payload en $CI->jwt

   3.3. Verificar rol
        current_role() → 'admin'
        → Admin tiene acceso total, permitir

   3.4. (Si no fuera admin, validaría permisos en DB)

4. Admin::__construct() se ejecuta
   - MY_Controller::__construct() se ejecuta primero
   - _init_common_data() extrae datos de $this->jwt
   - $this->data se llena con user_name, user_role, tenant_id

5. Admin::dashboard() se ejecuta
   - Carga modelos necesarios
   - Obtiene datos del dashboard
   - Renderiza vista con render_admin_template()

6. Response al cliente
   HTML completo del dashboard con datos inyectados
```

---

### **Caso 3: Request sin Autenticación**

```
1. Usuario navega a /admin/dashboard SIN cookie
   GET /admin/dashboard

2. AuthHook::check_access() se ejecuta

   2.1. Verificar si es ruta pública
        → NO es pública

   2.2. Validar JWT
        jwt_require()
        → jwt_from_request() retorna null (no hay cookie)
        → Lanza error 401
        → Response: {"ok": false, "msg": "Falta Bearer token"}
        → exit()

3. Request termina aquí, nunca llega al controlador
```

---

### **Caso 4: Token Expirado**

```
1. Usuario navega con token expirado
   GET /admin/dashboard
   Cookie: imenu_token=eyJ0eXAiOiJKV1QiLCJhbGc... (exp: 1729425600)
   Tiempo actual: 1729430000 (después de exp)

2. AuthHook::check_access() se ejecuta

   2.1. jwt_require()
        → jwt_from_request() extrae token de cookie
        → Intenta decodificar
        → Firebase\JWT\ExpiredException lanzada
        → Captura excepción
        → Response: {"ok": false, "msg": "Expired token"}
        → exit()

3. Request termina, frontend recibe 401
   - Frontend detecta 401
   - Redirige a /admin/login?expired=1
```

---

## 🔧 Configuración del Sistema

### **1. Autoload (application/config/autoload.php)**

```php
$autoload['libraries'] = array('database', 'form_validation', 'email', 'jwt');
$autoload['helper'] = array('url', 'file', 'auth', 'tenant_helper');
```

**Importante**:

- ❌ NO se carga `session` (no se usa para autenticación)
- ✅ Se carga `jwt` para usar en todas partes
- ✅ Helper `auth` autoload para funciones globales

---

### **2. Hooks (application/config/hooks.php)**

```php
$hook['pre_controller'] = array(
    'class'    => 'AuthHook',
    'function' => 'check_access',
    'filename' => 'AuthHook.php',
    'filepath' => 'hooks'
);
```

**Evento**: `pre_controller` (antes de instanciar controlador)

---

### **3. Routes (application/config/routes.php)**

```php
// Autenticación
$route['admin/auth/login'] = 'AdminAuth/login';
$route['admin/auth/logout'] = 'AdminAuth/logout';
$route['tenant/auth/login'] = 'TenantAuth/login';
$route['tenant/auth/logout'] = 'TenantAuth/logout';

// Panel Admin
$route['admin/dashboard'] = 'Admin/dashboard';
$route['admin/tenants'] = 'Admin/tenants';
// ...

// Panel Tenant
$route['app/dashboard'] = 'App/dashboard';
$route['app/productos'] = 'App/productos';
// ...
```

---

## 🚨 Errores Comunes y Soluciones

### **Error: "Falta Bearer token"**

**Causa**: Cookie no se está enviando en el request

**Soluciones**:

1. Verificar que el login estableció la cookie correctamente
2. Verificar que el dominio de la cookie coincide
3. En desarrollo local, verificar que no haya conflictos de puerto
4. Revisar DevTools → Application → Cookies

---

### **Error: "Expired token"**

**Causa**: El token superó su TTL de 8 horas

**Soluciones**:

1. Hacer re-login
2. Implementar refresh token para renovar automáticamente
3. Aumentar TTL en desarrollo (no recomendado en producción)

---

### **Error: "Signature verification failed"**

**Causa**: El secreto usado para firmar no coincide con el de verificación

**Soluciones**:

1. Verificar que `jwt_secret()` retorna el mismo valor siempre
2. No cambiar el secreto mientras haya tokens activos
3. Si se cambia el secreto, todos los usuarios deben re-login

---

### **Error: "No tienes permiso para acceder a productos"**

**Causa**: El usuario no tiene `can_products = 1` en la tabla `permisos`

**Soluciones**:

1. Verificar que existe fila en `permisos` para ese user_id + tenant_id
2. Actualizar la fila: `UPDATE permisos SET can_products = 1 WHERE ...`
3. Si es un nuevo usuario, insertar fila con permisos apropiados

---

### **Error: Cookie no se guarda en navegador**

**Causa**: Configuración incorrecta de la cookie

**Soluciones**:

1. Verificar que `Path=/` está configurado
2. En HTTPS, asegurar que `Secure` flag está presente
3. Verificar que no hay error en el formato de `Set-Cookie`
4. Revisar CORS si frontend está en dominio diferente

---

## 📚 Referencias

### **Documentación Externa**

- [JWT.io](https://jwt.io/) - Debugger y especificación RFC 7519
- [Firebase PHP-JWT](https://github.com/firebase/php-jwt) - Librería usada
- [OWASP JWT Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/JSON_Web_Token_for_Java_Cheat_Sheet.html)

### **Documentación Interna**

- `API_DOCUMENTATION.md` - Documentación general de APIs
- `permissions-auth.md` - Sistema de permisos detallado
- `DASHBOARD_ADMIN.md` - Dashboard administrativo

---

## 📝 Changelog

### **Versión 2.0.0** (20 octubre 2025)

- ✅ Eliminada dependencia de sesiones PHP
- ✅ Consolidada validación en AuthHook (única fuente de verdad)
- ✅ MY_Controller simplificado (solo helpers, no auth)
- ✅ Documentación completa del sistema JWT
- ✅ Autoload limpio (solo JWT, sin session)

### **Versión 1.0.0** (Inicial)

- ✅ Implementación de JWT con cookies HTTP-only
- ✅ AdminAuth y TenantAuth separados
- ✅ AuthHook con validación de permisos DB
- ✅ Helper auth_helper.php con funciones globales

---

## 👥 Equipo

**Backend Developer**: Implementación de JWT y hooks
**Security Engineer**: Revisión de seguridad y recomendaciones
**DevOps**: Configuración de HTTPS y variables de entorno
**QA Engineer**: Testing de autenticación y edge cases

---

**Última actualización**: 20 de octubre de 2025  
**Versión del documento**: 2.0.0  
**Autor**: Equipo de Desarrollo iMenu
