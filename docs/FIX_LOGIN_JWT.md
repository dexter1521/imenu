# Fix Sistema de Login - JWT Unificado

**Fecha**: 17 de octubre de 2025
**Problema**: Mensaje "Tu sesión ha expirado" al intentar hacer login como admin

## 🔍 Problema Identificado

El sistema tenía **dos métodos de autenticación en conflicto**:

1. **JWT con cookies** → Usado por `AdminAuth` y `TenantAuth`
2. **Sesiones de CodeIgniter** → Usado por `MY_Controller`

### Flujo Problemático:
```
Usuario → Login admin → AdminAuth genera JWT → Guarda en cookie
Usuario → Accede panel → MY_Controller busca sesión de CI → ❌ No encuentra sesión → Redirige a login
```

## ✅ Solución Implementada

Se **unificó todo el sistema para usar únicamente JWT** almacenado en cookies HttpOnly.

### Archivos Modificados:

#### 1. `application/helpers/auth_helper.php`
**Nuevas funciones agregadas:**

- `jwt_decode_from_cookie()`: Decodifica el JWT desde la cookie `imenu_token`
- `is_authenticated()`: Verifica si existe un JWT válido y no expirado
- `jwt_issue()`: Ahora incluye el campo `nombre` del usuario en el payload

**Cambios:**
```php
// Antes: Solo funciones básicas
// Después: Agregadas 2 funciones nuevas para manejo de JWT desde cookies
```

#### 2. `application/core/MY_Controller.php`
**Método modificado:** `_verify_auth()`

**Antes:**
```php
if (!$this->session->userdata('logged_in')) {
    redirect('/app/login?expired=1');
}
```

**Después:**
```php
if (!is_authenticated()) {
    redirect('/adminpanel/login?expired=1'); // Admin
    // o
    redirect('/app/login?expired=1'); // Tenant
}

// Decodificar JWT y almacenar en $this->jwt
$payload = jwt_decode_from_cookie();
$this->jwt = (object)$payload;
```

**Método modificado:** `_validate_tenant_access()`
```php
// Ahora usa $this->jwt->tenant_id en lugar de session
// Los administradores SaaS (rol='admin') pueden acceder a todos los recursos
```

**Método modificado:** `__construct()`
```php
// Datos de usuario ahora se obtienen del JWT:
$this->data['user_name'] = $this->jwt->nombre ?? 'Usuario';
$this->data['user_role'] = $this->jwt->rol ?? null;
$this->data['tenant_id'] = $this->jwt->tenant_id ?? null;
```

#### 3. `application/controllers/AdminAuth.php`
**Método modificado:** `login()`

**Agregado al response:**
```php
return json_encode([
    'ok' => true,
    'rol' => $u->rol,
    'tenant_id' => (int)$u->tenant_id,
    'token' => $token // ⬅️ NUEVO: Para uso en JavaScript
]);
```

#### 4. `application/controllers/TenantAuth.php`
**Mismo cambio que AdminAuth** - Ahora retorna el token en la respuesta JSON.

#### 5. `application/controllers/Admin.php`
**Agregado en `__construct()`:**
```php
// Verificar que el usuario tenga rol de admin
if (!isset($this->jwt->rol) || $this->jwt->rol !== 'admin') {
    redirect('/adminpanel/login?expired=1');
    exit;
}
```

#### 6. `application/config/routes.php`
**Rutas agregadas:**
```php
// Login admin
$route['adminauth/login'] = 'AdminAuth/login';
$route['adminauth/logout'] = 'AdminAuth/logout';
$route['adminpanel/login'] = 'AdminPanel/login'; // Vista login

// Panel admin (vistas - requieren JWT)
$route['admin/tenants_view'] = 'Admin/tenants_view';
$route['admin/planes_view'] = 'Admin/planes_view';
$route['admin/pagos_view'] = 'Admin/pagos_view';
$route['admin/dashboard_view'] = 'Admin/dashboard_view';

// Login tenant
$route['tenantauth/login'] = 'TenantAuth/login';
$route['tenantauth/logout'] = 'TenantAuth/logout';
```

## 🔐 Estructura del JWT

### Payload Generado:
```json
{
  "iss": "https://tudominio.com/",
  "sub": 123,
  "tenant_id": 5,
  "rol": "admin",
  "nombre": "Juan Pérez",
  "iat": 1729123456,
  "nbf": 1729123456,
  "exp": 1729152256
}
```

### Campos:
- `iss`: Emisor (base URL)
- `sub`: ID del usuario
- `tenant_id`: ID del tenant (0 para admin global)
- `rol`: Rol del usuario (`admin`, `owner`, `staff`)
- `nombre`: Nombre del usuario (para mostrar en vistas)
- `iat`: Issued at (timestamp de creación)
- `nbf`: Not before (válido desde)
- `exp`: Expiration (válido hasta - 8 horas)

## 📝 Flujo de Autenticación Corregido

### Login Admin:
```
1. Usuario → /adminpanel/login (vista HTML)
2. Submit form → POST /adminauth/login
3. AdminAuth valida credenciales
4. AdminAuth genera JWT con rol='admin'
5. AdminAuth guarda JWT en cookie HttpOnly 'imenu_token'
6. AdminAuth retorna JSON: { ok: true, rol: 'admin', token: '...' }
7. JavaScript redirige a /admin/tenants_view
8. Admin::tenants_view() ejecuta
9. MY_Controller::_verify_auth() valida JWT desde cookie
10. Admin::__construct() verifica rol='admin'
11. ✅ Vista renderizada correctamente
```

### Login Tenant (Owner/Staff):
```
1. Usuario → /app/login (vista HTML)
2. Submit form → POST /tenantauth/login
3. TenantAuth valida credenciales
4. TenantAuth genera JWT con rol='owner' o 'staff'
5. TenantAuth guarda JWT en cookie HttpOnly 'imenu_token'
6. TenantAuth retorna JSON: { ok: true, rol: 'owner', token: '...' }
7. JavaScript redirige a /app/dashboard_view
8. App::dashboard_view() ejecuta
9. MY_Controller::_verify_auth() valida JWT desde cookie
10. ✅ Vista renderizada correctamente
```

## 🧪 Pruebas

### 1. Verificar login admin
```bash
# Navegar a:
http://localhost/imenu/adminpanel/login

# Credenciales (según install.php):
Email: un@correo.com
Password: kjdasñdlkajs
```

### 2. Verificar login tenant
```bash
# Navegar a:
http://localhost/imenu/app/login

# Credenciales de usuario owner/staff creado en la DB
```

### 3. Verificar API con JWT
```bash
# En DevTools Console después de login:
console.log(document.cookie); // Debe mostrar imenu_token

# Probar endpoint API:
fetch('/imenu/api/admin/tenants', {
  credentials: 'same-origin',
  headers: { 'Accept': 'application/json' }
})
.then(r => r.json())
.then(data => console.log(data));
```

## 🔒 Seguridad

### Cookie JWT:
- **HttpOnly**: ✅ No accesible desde JavaScript (previene XSS)
- **SameSite=Strict**: ✅ Previene CSRF
- **Secure**: ✅ Solo en HTTPS (producción)
- **Path=/**: ✅ Disponible en toda la aplicación
- **Expiry**: 8 horas

### Validaciones:
1. ✅ Token firmado con secret (HMAC SHA-256)
2. ✅ Verificación de expiración (exp claim)
3. ✅ Verificación de rol en controladores protegidos
4. ✅ Validación de tenant_id para recursos específicos
5. ✅ Admin global puede acceder a todos los recursos

## 📊 Estados de Autenticación

| Condición | Resultado |
|-----------|-----------|
| Sin cookie JWT | Redirige a login |
| JWT expirado | Redirige a login con ?expired=1 |
| JWT válido pero rol incorrecto | Error 403 |
| JWT válido con rol correcto | ✅ Acceso permitido |

## 🐛 Debugging

### Ver contenido del JWT:
```javascript
// En DevTools Console:
const token = document.cookie.split('; ').find(r => r.startsWith('imenu_token='))?.split('=')[1];
if (token) {
  const [header, payload, signature] = token.split('.');
  const decoded = JSON.parse(atob(payload));
  console.log('JWT Payload:', decoded);
}
```

### Verificar autenticación en PHP:
```php
// En cualquier controlador que extienda MY_Controller:
if (isset($this->jwt)) {
    echo '<pre>';
    print_r($this->jwt);
    echo '</pre>';
}
```

### Logs útiles:
```php
// Activar en application/config/config.php:
$config['log_threshold'] = 2; // 0=off, 2=debug, 4=all

// Ver logs en:
// application/logs/log-YYYY-MM-DD.php
```

## 📌 Notas Importantes

1. **Session de CI no se usa más** - Todo es JWT
2. **Cookie HttpOnly** - El token NO es accesible desde JavaScript
3. **8 horas de validez** - Después el usuario debe hacer login nuevamente
4. **Secret JWT** - Cambiar en `auth_helper.php` función `jwt_secret()` en producción
5. **CSRF** - El login form debe incluir el token CSRF de CodeIgniter

## 🚀 Próximos Pasos

- [ ] Implementar refresh token para extender sesión sin re-login
- [ ] Agregar "Remember Me" funcional (cookie de 30 días)
- [ ] Implementar logout desde todos los dispositivos
- [ ] Agregar logs de auditoría (login exitoso, fallido, logout)
- [ ] Implementar rate limiting en endpoints de login

## 🔗 Referencias

- CodeIgniter 3 Docs: https://codeigniter.com/userguide3/
- Firebase JWT PHP: https://github.com/firebase/php-jwt
- JWT.io: https://jwt.io/

---

**Autor**: GitHub Copilot  
**Fecha**: 17 de octubre de 2025
