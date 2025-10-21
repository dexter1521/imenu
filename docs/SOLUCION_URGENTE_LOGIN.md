# 🚨 SOLUCIÓN URGENTE: Error "Token inválido o sin permisos" en Login Admin

## 📅 Fecha: 20 de octubre de 2025

---

## 🔴 Problema Reportado

Al intentar acceder a `/adminpanel/login` o al hacer login:

```
SweetAlert muestra:
"Error Token inválido o sin permisos"
```

El usuario no puede ni siquiera cargar la página de login.

---

## ✅ CAUSA RAÍZ ENCONTRADA

**AuthHook estaba bloqueando la página de login** porque `AdminPanel` no estaba en la lista de controladores públicos.

### Flujo del Error:

```
1. Usuario navega a /adminpanel/login
   ↓
2. AuthHook::check_access() se ejecuta (pre_controller)
   ↓
3. Verifica si 'adminpanel' está en $public_controllers
   $public_controllers = ['publicuser', 'tenantauth', 'adminauth'];
   ↓
4. 'adminpanel' NO está en la lista ❌
   ↓
5. AuthHook intenta validar JWT con jwt_require()
   ↓
6. No hay cookie imenu_token (usuario no ha hecho login aún)
   ↓
7. show_error('Token inválido o expirado: Falta Bearer token', 401)
   ↓
8. Usuario ve error y no puede acceder
```

---

## ✅ SOLUCIÓN APLICADA

### **Archivo**: `application/hooks/AuthHook.php`

```php
// ANTES (BLOQUEABA LOGIN)
$public_controllers = ['publicuser', 'tenantauth', 'adminauth'];

// DESPUÉS (CORREGIDO)
$public_controllers = ['publicuser', 'tenantauth', 'adminauth', 'adminpanel'];
```

**Cambio**: Agregado `'adminpanel'` a la lista de controladores públicos.

**Razón**: La página de login (`AdminPanel::login()`) debe ser accesible SIN autenticación, porque ahí es donde el usuario ingresa sus credenciales para OBTENER el token.

---

## 🧪 TESTING

### **Paso 1: Limpiar Navegador**

```
1. Abrir DevTools (F12)
2. Application → Clear Storage → Clear site data
   O simplemente cerrar y abrir el navegador
```

### **Paso 2: Acceder a Login**

```
1. Ir a: http://localhost/imenu/adminpanel/login
```

**Resultado Esperado**:
- ✅ La página de login carga correctamente
- ✅ Se muestra el formulario de email/password
- ✅ NO hay error "Token inválido o sin permisos"

### **Paso 3: Hacer Login**

```
1. Ingresar credenciales:
   Email: admin@imenu.com
   Password: tu_password

2. Click en "Iniciar sesión"
```

**Resultado Esperado**:
- ✅ SweetAlert muestra éxito (o al menos no error de token)
- ✅ Redirect a http://localhost/imenu/admin/dashboard
- ✅ Dashboard carga correctamente
- ✅ Se puede navegar sin problemas

### **Paso 4: Verificar Cookie**

```
En DevTools:
Application → Cookies → localhost

Debe aparecer:
- Name: imenu_token
- Value: eyJ0eXA...
- HttpOnly: ✓
- SameSite: Strict
```

---

## 🔄 Flujo Correcto Después del Fix

```
┌─────────────────────────────────────────────────────────────┐
│              FLUJO DE LOGIN CORRECTO                        │
└─────────────────────────────────────────────────────────────┘

1. Usuario navega a /adminpanel/login
   ↓
2. AuthHook::check_access() se ejecuta
   ↓
3. Verifica si 'adminpanel' está en $public_controllers
   $public_controllers = ['publicuser', 'tenantauth', 'adminauth', 'adminpanel'];
   ↓
4. 'adminpanel' SÍ está en la lista ✅
   ↓
5. AuthHook retorna inmediatamente (sin validar JWT)
   return; // no validar nada aquí
   ↓
6. AdminPanel::login() se ejecuta
   ↓
7. Renderiza vista admin/login.php
   ↓
8. Usuario ve formulario de login ✅
   ↓
   
9. Usuario ingresa credenciales y envía formulario
   ↓
10. JavaScript hace POST a /adminauth/login
    ↓
11. AdminAuth::login() valida credenciales
    ↓
12. Emite JWT y establece cookie
    Set-Cookie: imenu_token=eyJ0eXA...; HttpOnly; SameSite=Strict
    ↓
13. Retorna JSON: {ok: true, rol: 'admin', ...}
    ↓
14. JavaScript guarda metadata en localStorage
    ↓
15. JavaScript espera 100ms
    setTimeout(() => { window.location.href = '/admin/dashboard'; }, 100);
    ↓
16. Navegador procesa cookie
    ↓
17. Redirect a /admin/dashboard
    ↓
18. AuthHook valida JWT desde cookie ✅
    ↓
19. Admin::dashboard() renderiza vista ✅
    ↓
20. Usuario ve dashboard correctamente ✅
```

---

## 📊 Comparación

| Situación | Antes del Fix | Después del Fix |
|-----------|---------------|-----------------|
| Acceso a `/adminpanel/login` | ❌ Error "Token inválido" | ✅ Carga formulario |
| AuthHook valida login page | ❌ Sí (incorrecto) | ✅ No (es público) |
| Usuario puede hacer login | ❌ No | ✅ Sí |
| Dashboard carga después de login | ❌ No | ✅ Sí |

---

## 🔐 Controladores Públicos Actualizados

Después del fix, estos controladores NO requieren autenticación:

```php
$public_controllers = [
    'publicuser',  // Menú público para clientes
    'tenantauth',  // Login de tenants
    'adminauth',   // Login de admin (API endpoint)
    'adminpanel'   // Página de login de admin (vista HTML)
];
```

**Importante**: `AdminAuth` es el endpoint API que procesa el login (POST), mientras que `AdminPanel` es la página que muestra el formulario HTML.

---

## ⚡ Resumen Ejecutivo

### Problema:
AuthHook bloqueaba la página de login porque `AdminPanel` no estaba en la whitelist de controladores públicos.

### Solución:
Agregar `'adminpanel'` a `$public_controllers` en `AuthHook.php`.

### Archivos Modificados:
1. ✅ `application/hooks/AuthHook.php` (1 línea cambiada)

### Próximos Pasos:
1. Limpiar cookies/localStorage
2. Abrir `/adminpanel/login`
3. Verificar que carga sin error
4. Hacer login
5. Verificar que dashboard carga

---

## 🆘 Si Aún Hay Problemas

### Error 1: "Token inválido" después del login

**Posible causa**: Cookie no se estableció correctamente.

**Debug**:
```javascript
// En DevTools Console después del login:
document.cookie
// Debe mostrar: "imenu_token=eyJ0eXA..."

// Si no aparece, verificar en Network tab:
// Request a /adminauth/login
// Response Headers debe incluir:
// Set-Cookie: imenu_token=...
```

**Solución**: Aumentar timeout en `login-admin.js` de 100ms a 300ms.

---

### Error 2: "No autorizado como admin"

**Posible causa**: Usuario no tiene rol 'admin' en la base de datos.

**Debug**:
```sql
SELECT id, email, rol, tenant_id FROM users WHERE email = 'tu_email@example.com';
```

**Solución**: Actualizar rol del usuario:
```sql
UPDATE users SET rol = 'admin' WHERE email = 'tu_email@example.com';
```

---

### Error 3: Redirect loop infinito

**Posible causa**: AuthHook redirige al login, pero login redirige a dashboard que redirige al login...

**Debug**: Revisar logs de PHP en `application/logs/`.

**Solución**: Verificar que AuthHook NO esté validando AdminPanel.

---

## ✅ Checklist de Verificación

Después de aplicar el fix:

- [ ] `application/hooks/AuthHook.php` incluye `'adminpanel'` en `$public_controllers`
- [ ] Cookies limpiadas
- [ ] `/adminpanel/login` carga sin error
- [ ] Formulario de login visible
- [ ] Login exitoso (JSON retornado)
- [ ] Cookie `imenu_token` establecida
- [ ] Redirect a dashboard
- [ ] Dashboard carga correctamente
- [ ] Navegación entre páginas funciona

---

**Última actualización**: 20 de octubre de 2025  
**Estado**: ✅ Solucionado  
**Prioridad**: 🚨 CRÍTICA  
**Archivos Afectados**: 1  
**Líneas Cambiadas**: 1
