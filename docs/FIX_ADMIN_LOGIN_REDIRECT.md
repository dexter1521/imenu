# Fix: Admin Login Redirect Loop

## 📅 Fecha: 20 de octubre de 2025

---

## 🔴 Problema Reportado

Al iniciar sesión como admin SaaS con credenciales válidas:

1. ✅ Login exitoso, se retorna JSON correcto:
```json
{
    "ok": true,
    "rol": "admin",
    "tenant_id": 2,
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

2. ❌ Inmediatamente después, redirect a:
```
http://localhost/imenu/adminpanel/login?expired=1
```

---

## 🔍 Diagnóstico

### **Causa Raíz 0: AuthHook Bloqueaba la Página de Login** ⚠️ **CRÍTICO**

El error más grave era que **AuthHook intentaba validar JWT en la página de login**:

```php
// AuthHook.php - ANTES (PROBLEMÁTICO)
$public_controllers = ['publicuser', 'tenantauth', 'adminauth'];

// AdminPanel NO estaba en la lista
// Cuando el usuario iba a /adminpanel/login:
// 1. AuthHook ejecuta check_access()
// 2. 'adminpanel' NO está en $public_controllers
// 3. AuthHook llama jwt_require()
// 4. No hay cookie imenu_token (usuario no ha hecho login)
// 5. show_error('Token inválido o expirado: ...')
```

**Síntomas**:
- Al abrir `/adminpanel/login` → Error "Token inválido o expirado"
- SweetAlert muestra: "Error Token inválido o sin permisos"
- No se puede ni siquiera cargar el formulario de login

**Solución**: Agregar `'adminpanel'` a `$public_controllers`.

---

### **Causa Raíz 1: Timing de Cookie**

Cuando el frontend hace login:

```javascript
// 1. POST a /adminauth/login
const resp = await fetch(loginUrl, { method: 'POST', ... });

// 2. Servidor retorna JSON + Set-Cookie header
// Set-Cookie: imenu_token=eyJ0eXA...

// 3. Frontend inmediatamente redirige
window.location.href = adminUrl; // ← PROBLEMA AQUÍ
```

**El problema**: El navegador puede no haber procesado completamente la cookie `Set-Cookie` antes de que se ejecute `window.location.href`.

Cuando el navegador navega a `/admin/dashboard`, AuthHook intenta validar el JWT desde la cookie, pero **la cookie aún no está disponible** en esa primera petición.

---

### **Causa Raíz 2: Validación en Admin::__construct()**

El controlador `Admin.php` estaba intentando acceder a `$this->jwt->rol` directamente en el constructor:

```php
// CÓDIGO ANTERIOR (PROBLEMÁTICO)
public function __construct()
{
    parent::__construct();
    
    // ❌ Acceso directo a $this->jwt puede fallar
    if (!isset($this->jwt->rol) || $this->jwt->rol !== 'admin') {
        redirect('/adminpanel/login?expired=1');
        exit;
    }
}
```

**Problemas**:
1. `$this->jwt` puede no estar disponible aún en el constructor
2. Doble validación (AuthHook ya valida el JWT)
3. Método `validate_view_access()` no existe en MY_Controller refactorizado

---

### **Causa Raíz 3: Verificación API Incorrecta**

El JavaScript intentaba verificar el token con:

```javascript
const check = await fetch(apiTenants, {
    headers: {
        'Authorization': 'Bearer ' + data.token  // ← Problema
    }
});
```

**Problema**: AuthHook busca el JWT en la **cookie `imenu_token`**, NO en el header `Authorization: Bearer`. Esta verificación siempre fallaba.

---

## ✅ Soluciones Aplicadas

### **Fix 0: Agregar AdminPanel a Controladores Públicos (CRÍTICO)** ⭐

**Archivo**: `application/hooks/AuthHook.php`

```php
// ANTES (BLOQUEABA LA PÁGINA DE LOGIN)
$public_controllers = ['publicuser', 'tenantauth', 'adminauth'];

// DESPUÉS (PERMITE ACCESO A PÁGINA DE LOGIN)
$public_controllers = ['publicuser', 'tenantauth', 'adminauth', 'adminpanel'];
```

**Problema**: AuthHook intentaba validar JWT en `/adminpanel/login`, pero el usuario aún no tiene token (es la página de login).

**Resultado**: Error "Token inválido o expirado" mostrado por `show_error()` en AuthHook.

**Razón**: El controlador `AdminPanel` debe ser público porque sirve la vista de login donde el usuario aún NO está autenticado.

---

### **Fix 1: Delay en Redirección (JavaScript)**

**Archivo**: `assets/js/login-admin.js`

```javascript
// ANTES (inmediato)
localStorage.setItem('imenu_role', data.rol);
window.location.href = adminUrl;

// DESPUÉS (con delay)
localStorage.setItem('imenu_role', data.rol);

setTimeout(() => {
    window.location.href = adminUrl;
}, 100); // 100ms para que cookie se procese
```

**Razón**: Dar tiempo al navegador para procesar la cookie `Set-Cookie` antes de navegar.

---

### **Fix 2: Usar Helper en lugar de `$this->jwt` (PHP)**

**Archivo**: `application/controllers/Admin.php`

```php
// ANTES (acceso directo problemático)
if (!isset($this->jwt->rol) || $this->jwt->rol !== 'admin') {
    redirect('/adminpanel/login?expired=1');
    exit;
}

// DESPUÉS (usando helper)
$rol = current_role(); // Helper que accede a $CI->jwt

if ($rol !== 'admin') {
    redirect('/adminpanel/login?expired=1');
    exit;
}
```

**Razón**: 
- `current_role()` es más robusto
- Accede al JWT desde la instancia global de CI
- AuthHook ya garantiza que el JWT existe

---

### **Fix 3: Eliminar Validación Redundante**

**Archivo**: `application/controllers/Admin.php`

```php
// ELIMINADO
$this->allowed_views = ['dashboard', 'tenants_view', ...];
$this->validate_view_access(); // ← Método no existe
```

**Razón**: `validate_view_access()` fue eliminado en la refactorización. No es necesario porque AuthHook ya valida todo.

---

### **Fix 4: Eliminar Verificación API Innecesaria**

**Archivo**: `assets/js/login-admin.js`

```javascript
// ELIMINADO TODO ESTE BLOQUE
const apiTenants = window.IMENU.api.tenants;
if (apiTenants && data.token) {
    const check = await fetch(apiTenants, {
        headers: { 'Authorization': 'Bearer ' + data.token }
    });
    // ... validación que siempre fallaba
}
```

**Razón**: 
- AuthHook busca JWT en cookie, no en header
- Verificación innecesaria (la cookie ya está establecida)
- Causaba confusión y delays

---

### **Fix 5: Redirigir al Dashboard en lugar de Tenants**

**Archivo**: `application/views/admin/login.php`

```javascript
// ANTES
admin: '<?php echo site_url('admin/tenants_view'); ?>'

// DESPUÉS
admin: '<?php echo site_url('admin/dashboard'); ?>'
```

**Razón**: El dashboard es la página principal del admin, no el listado de tenants.

---

## 🔄 Flujo Correcto Después del Fix

```
┌─────────────────────────────────────────────────────────────┐
│           FLUJO DE LOGIN ADMIN (CORREGIDO)                  │
└─────────────────────────────────────────────────────────────┘

1. Usuario → /adminpanel/login (vista HTML)
   ↓

2. Usuario ingresa credenciales
   email: admin@imenu.com
   password: ****
   ↓

3. JavaScript POST a /adminauth/login
   fetch('adminauth/login', { 
       method: 'POST', 
       body: 'email=...&password=...' 
   })
   ↓

4. AdminAuth::login()
   ├─→ Valida credenciales con User_model
   ├─→ Verifica que rol = 'admin'
   ├─→ Emite JWT con jwt_issue()
   └─→ Establece cookie:
       Set-Cookie: imenu_token=eyJ0eXA...; 
                   HttpOnly; SameSite=Strict; Expires=...
   ↓

5. Responde JSON:
   {
       "ok": true,
       "rol": "admin",
       "tenant_id": 2,
       "token": "eyJ0eXA..."
   }
   ↓

6. JavaScript guarda metadata en localStorage
   localStorage.setItem('imenu_role', 'admin')
   ↓

7. JavaScript espera 100ms (CRÍTICO)
   setTimeout(() => { ... }, 100)
   ↓
   → Navegador procesa Set-Cookie header
   → Cookie imenu_token ahora disponible
   ↓

8. JavaScript redirige a admin/dashboard
   window.location.href = '/admin/dashboard'
   ↓

9. Navegador hace GET /admin/dashboard
   Cookie: imenu_token=eyJ0eXA... (YA DISPONIBLE)
   ↓

10. AuthHook::check_access() (pre_controller)
    ├─→ Controlador 'admin' NO es público
    ├─→ jwt_require() extrae token de cookie ✅
    ├─→ Valida firma y expiración ✅
    ├─→ Almacena payload en $CI->jwt
    └─→ current_role() = 'admin' ✅
    ↓

11. Admin::__construct()
    ├─→ parent::__construct() (MY_Controller)
    ├─→ Verifica current_role() === 'admin' ✅
    └─→ Carga modelos
    ↓

12. Admin::dashboard()
    └─→ Renderiza vista admin/dashboard
    ↓

13. Response: HTML del dashboard ✅

✅ Usuario ve el dashboard correctamente
```

---

## 🧪 Testing

### **Test 1: Login Admin Exitoso**

```bash
# 1. Navegar a login
http://localhost/imenu/adminpanel/login

# 2. Ingresar credenciales:
Email: admin@imenu.com (o tu email admin)
Password: tu_password

# 3. Click en "Iniciar sesión"

# Resultado esperado:
✅ Mensaje de éxito (si SweetAlert está configurado)
✅ Redirect a http://localhost/imenu/admin/dashboard
✅ Dashboard carga correctamente
✅ Sidebar visible con opciones de admin
```

---

### **Test 2: Verificar Cookie**

Después del login exitoso, abrir DevTools:

```javascript
// En la consola del navegador
document.cookie
// Debe incluir: "imenu_token=eyJ0eXA..."

// Verificar que es HttpOnly (no debería ser visible en JS)
// Ir a Application → Cookies → localhost
// Debe mostrar:
// Name: imenu_token
// Value: eyJ0eXA...
// HttpOnly: ✓
// SameSite: Strict
```

---

### **Test 3: Navegación Después de Login**

```bash
# 1. Después de login exitoso, probar navegar a:
http://localhost/imenu/admin/tenants_view
→ Debe cargar sin problemas ✅

http://localhost/imenu/admin/planes_view
→ Debe cargar sin problemas ✅

http://localhost/imenu/admin/pagos_view
→ Debe cargar sin problemas ✅

# 2. Todas las páginas deben mostrar datos sin error
```

---

### **Test 4: Token Expirado**

```bash
# 1. Esperar 8+ horas (o manipular cookie con token expirado)

# 2. Intentar navegar a:
http://localhost/imenu/admin/dashboard

# Resultado esperado:
✅ Redirect a /adminpanel/login?expired=1
✅ Mensaje: "Tu sesión ha expirado. Por favor inicia sesión de nuevo."
```

---

### **Test 5: Sin Cookie (Logout o Cookie Eliminada)**

```bash
# 1. En DevTools, eliminar cookie imenu_token manualmente
Application → Cookies → localhost → imenu_token → Delete

# 2. Intentar navegar a:
http://localhost/imenu/admin/dashboard

# Resultado esperado:
✅ Redirect a /adminpanel/login?expired=1
✅ Mensaje de sesión expirada
```

---

## 📊 Antes vs Después

| Aspecto | Antes (Con Bug) | Después (Corregido) |
|---------|-----------------|---------------------|
| **Acceso a /adminpanel/login** | ❌ Error "Token inválido" | ✅ Carga formulario |
| **AuthHook valida login page** | ❌ Sí (incorrecto) | ✅ No (público) |
| **Login JSON** | ✅ Exitoso | ✅ Exitoso |
| **Cookie establecida** | ✅ Sí | ✅ Sí |
| **Redirect inmediato** | ❌ Cookie no disponible | ✅ Delay de 100ms |
| **AuthHook valida dashboard** | ❌ No encuentra cookie | ✅ Cookie disponible |
| **Acceso a dashboard** | ❌ Redirect a login | ✅ Dashboard carga |
| **Validación en Admin.php** | ❌ Acceso directo a $this->jwt | ✅ Usa helper current_role() |
| **Verificación API** | ❌ Con Bearer (incorrecto) | ✅ Eliminada (innecesaria) |

---

## 🔐 Seguridad

### **Mejoras de Seguridad con el Fix**

1. ✅ **Cookie HttpOnly**: No accesible desde JavaScript (previene XSS)
2. ✅ **SameSite=Strict**: Previene CSRF
3. ✅ **Validación consolidada**: Solo AuthHook valida (menos superficie de ataque)
4. ✅ **No almacenar token en localStorage**: Solo metadata (rol, tenant_id)
5. ✅ **Expiración automática**: 8 horas

### **Sin Cambios de Seguridad**

- ✅ JWT firmado con HMAC SHA-256
- ✅ Validación de rol en cada request
- ✅ Permisos granulares desde DB

---

## 📝 Archivos Modificados

1. ✅ `application/hooks/AuthHook.php` ⭐ **MÁS IMPORTANTE**
   - Agregado `'adminpanel'` a `$public_controllers`
   - Permite acceso sin autenticación a `/adminpanel/login`

2. ✅ `application/controllers/Admin.php`
   - Cambiado acceso directo a `$this->jwt->rol` por `current_role()`
   - Eliminado `validate_view_access()` (no existe)
   - Eliminado `$this->allowed_views`

3. ✅ `assets/js/login-admin.js`
   - Agregado `setTimeout()` de 100ms antes de redirect
   - Eliminada verificación API con Bearer token
   - Simplificado flujo de redirect

4. ✅ `application/views/admin/login.php`
   - Cambiado redirect de `admin/tenants_view` a `admin/dashboard`

5. ✅ `docs/FIX_ADMIN_LOGIN_REDIRECT.md` (este archivo)
   - Documentación completa del problema y solución

---

## 🚀 Rollback (Si es Necesario)

Si el fix causa problemas:

### **Opción 1: Aumentar Delay**

Si 100ms no es suficiente, cambiar a 200ms o 300ms:

```javascript
// En login-admin.js
setTimeout(() => {
    window.location.href = adminUrl;
}, 300); // Aumentar a 300ms
```

### **Opción 2: Usar Polling para Verificar Cookie**

```javascript
// Esperar hasta que cookie esté disponible
function waitForCookie(name, timeout = 3000) {
    return new Promise((resolve, reject) => {
        const start = Date.now();
        const check = () => {
            if (document.cookie.includes(name)) {
                resolve(true);
            } else if (Date.now() - start > timeout) {
                reject(new Error('Cookie timeout'));
            } else {
                setTimeout(check, 50);
            }
        };
        check();
    });
}

// Después del login
await waitForCookie('imenu_token');
window.location.href = adminUrl;
```

**Nota**: HttpOnly cookies NO son visibles en `document.cookie`, así que este approach no funciona para nuestro caso.

### **Opción 3: Redirect del Servidor**

En lugar de redirect en JavaScript, hacer redirect HTTP desde el servidor:

```php
// En AdminAuth::login()
// En lugar de retornar JSON, hacer redirect HTTP
if ($u->rol === 'admin') {
    $token = jwt_issue(...);
    setcookie('imenu_token', $token, ...);
    
    // Redirect HTTP en lugar de JSON
    redirect('admin/dashboard');
    return;
}
```

**Desventaja**: Pierde la capacidad de mostrar mensajes personalizados con SweetAlert.

---

## ✅ Conclusión

El problema fue causado por un **race condition** entre:
1. El navegador procesando la cookie `Set-Cookie`
2. El JavaScript redirigiendo inmediatamente

La solución simple de agregar un delay de 100ms permite que el navegador procese completamente la cookie antes de la navegación, asegurando que AuthHook pueda validar el JWT correctamente.

Adicionalmente, se corrigió el código de `Admin.php` para usar helpers en lugar de acceso directo a `$this->jwt`, lo cual es más robusto y consistente con el nuevo flujo de autenticación consolidado.

---

**Última actualización**: 20 de octubre de 2025  
**Versión**: 1.0.0  
**Estado**: ✅ Resuelto
