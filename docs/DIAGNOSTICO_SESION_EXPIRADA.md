# 🔧 Diagnóstico: "Tu sesión ha expirado" Después del Login

## 📅 Fecha: 20 de octubre de 2025

---

## 🔴 Problema Actual

**Síntomas**:

1. ✅ Login exitoso (credenciales válidas)
2. ✅ Cookie `imenu_token` se establece
3. ✅ LocalStorage tiene `imenu_role` y `imenu_tenant`
4. ❌ Mensaje: "Tu sesión ha expirado. Por favor inicia sesión de nuevo."
5. ❌ URL muestra: `adminpanel/login?expired=1`

---

## 🔍 Análisis del Problema

Este comportamiento indica que:

1. **Login funciona** → Token se genera y cookie se establece ✅
2. **Redirect ocurre** → JavaScript redirige a `/admin/dashboard` ✅
3. **Validación falla** → AuthHook o Admin.php rechaza el token ❌
4. **Redirect a login** → Con parámetro `?expired=1` ❌

### Posibles Causas:

#### **Causa A: Token se decodifica mal**

`jwt_require()` intenta acceder a `$payload['rol']` pero `$payload` es un objeto, no array.

**Solución aplicada**: Convertir payload a array con `json_decode(json_encode($payload), true)`

#### **Causa B: Secret key diferente**

El token se firma con una clave en `AdminAuth::login()` pero se decodifica con otra en `jwt_require()`.

**Verificar**: Que `jwt_secret()` y `JWT::__construct()` usen la misma clave.

#### **Causa C: Timing de cookie**

El navegador no tiene la cookie disponible cuando hace GET a `/admin/dashboard`.

**Solución aplicada**: `setTimeout(100ms)` antes del redirect.

---

## ✅ Fixes Aplicados

### **Fix 1: Corregir `jwt_require()` en auth_helper.php**

**Problema**: Intentaba acceder a `$payload['rol']` cuando `$payload` es objeto.

**Cambio**:

```php
// ANTES
$payload = $CI->jwt->decode($token);
if (!in_array($payload['rol'], $roles)) { // ← Error: $payload es objeto

// DESPUÉS
$jwt_lib = $CI->jwt;  // Guardar referencia a librería
$payload = $jwt_lib->decode($token);
$payload = json_decode(json_encode($payload), true); // ← Convertir a array
if (!isset($payload['rol']) || !in_array($payload['rol'], $roles)) {
```

**Archivo modificado**: `application/helpers/auth_helper.php`

---

## 🧪 Pasos de Diagnóstico

### **Paso 1: Verificar Secret Key**

Ambas claves deben ser IDÉNTICAS:

**Ubicación 1**: `application/libraries/JWT.php` (línea 22)

```php
$this->secret_key = 'CHANGE_ME_SUPER_SECRET_32CHARS_MINIMO';
```

**Ubicación 2**: `application/helpers/auth_helper.php` (función `jwt_secret()`, línea ~8)

```php
return 'CHANGE_ME_SUPER_SECRET_32CHARS_MINIMO';
```

**Verificación**:

```bash
cd d:\htdocs\imenu
grep -n "CHANGE_ME_SUPER_SECRET" application/libraries/JWT.php application/helpers/auth_helper.php
```

**Esperado**: Ambas líneas deben mostrar la MISMA cadena.

---

### **Paso 2: Usar Herramienta de Diagnóstico**

Abre en el navegador:

```
http://localhost/imenu/diagnostico_jwt.html
```

**Qué verificar**:

1. ✅ Cookie `imenu_token` existe
2. ✅ LocalStorage tiene `imenu_role` = 'admin'
3. ✅ Click en "Verificar Token":
   - Debe mostrar payload con `rol: admin`
   - Debe mostrar "Token VÁLIDO (no expirado)"
   - Si muestra "Token EXPIRADO" → problema de fecha/hora

---

### **Paso 3: Test Manual con cURL**

**Test 1: Login**

```bash
curl -c cookies.txt -X POST http://localhost/imenu/adminauth/login \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "email=admin@imenu.com&password=tu_password"
```

**Esperado**:

```json
{
	"ok": true,
	"rol": "admin",
	"tenant_id": 2,
	"token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**Test 2: Verificar cookie guardada**

```bash
cat cookies.txt
```

**Esperado**: Debe contener línea con `imenu_token`

**Test 3: Acceder a dashboard con cookie**

```bash
curl -b cookies.txt http://localhost/imenu/admin/dashboard -i
```

**Esperado**:

- ✅ Status `200 OK` → Token válido
- ❌ Status `302 Found` con Location `adminpanel/login?expired=1` → Token rechazado

---

### **Paso 4: Revisar Logs de PHP**

**Ubicación**: `application/logs/log-2025-10-20.php`

**Buscar errores**:

```bash
tail -f application/logs/log-2025-10-20.php | grep -i "error\|exception\|token"
```

**Errores comunes**:

- `Token inválido: ...` → Problema de decodificación
- `Trying to access array offset on value of type stdClass` → Problema de tipo de datos
- `Call to undefined method` → Problema con librería JWT

---

## 🔧 Soluciones por Problema

### **Problema 1: "Token inválido: Expired token"**

**Causa**: Reloj del servidor desincronizado.

**Solución**:

```bash
# Verificar fecha/hora del servidor
date

# Si está mal, sincronizar (requiere admin)
net stop w32time
net start w32time
w32tm /resync
```

---

### **Problema 2: "Trying to access array offset on value of type stdClass"**

**Causa**: `$payload` es objeto, no array.

**Solución**: Ya aplicada en `jwt_require()` - convertir a array.

**Verificar que esté aplicada**:

```php
// En application/helpers/auth_helper.php, línea ~103
$payload = json_decode(json_encode($payload), true);
```

---

### **Problema 3: Secret key diferente**

**Causa**: Clave en `JWT.php` diferente a `auth_helper.php`.

**Solución**:

```bash
# Editar application/libraries/JWT.php
# Cambiar línea 22:
$this->secret_key = 'MI_CLAVE_SECRETA_SUPER_LARGA_32_CHARS_MIN';

# Editar application/helpers/auth_helper.php
# Cambiar línea ~8 (función jwt_secret):
return 'MI_CLAVE_SECRETA_SUPER_LARGA_32_CHARS_MIN';

# DEBEN SER IDÉNTICAS
```

---

### **Problema 4: Cookie no HttpOnly visible en JS**

**Causa**: Cookie HttpOnly no es accesible desde `document.cookie`.

**Diagnóstico**:

```javascript
// En consola del navegador:
console.log(document.cookie);
// Si NO muestra imenu_token → Es HttpOnly (correcto)

// Verificar en DevTools:
// F12 → Application → Cookies → localhost
// Debe aparecer imenu_token con HttpOnly ✓
```

**Solución**: Esto es correcto. HttpOnly previene XSS.

---

## 📊 Checklist de Verificación

Después de aplicar los fixes:

- [ ] `auth_helper.php` convierte payload a array (línea ~103)
- [ ] Secret key idéntica en `JWT.php` y `auth_helper.php`
- [ ] `diagnostico_jwt.html` muestra token válido
- [ ] Cookie `imenu_token` existe en DevTools
- [ ] LocalStorage tiene `imenu_role` = 'admin'
- [ ] cURL test retorna `200 OK` al acceder a dashboard
- [ ] No hay errores en `application/logs/`
- [ ] Login exitoso → Dashboard carga sin redirect

---

## 🚀 Pasos para Probar AHORA

### **1. Limpiar navegador**

```
Ctrl + Shift + Delete → Borrar cookies y caché
```

### **2. Abrir diagnóstico**

```
http://localhost/imenu/diagnostico_jwt.html
```

### **3. Hacer login**

```
http://localhost/imenu/adminpanel/login
Email: admin@imenu.com
Password: tu_password
```

### **4. Verificar resultado**

**SI FUNCIONA** ✅:

- Dashboard carga
- No hay redirect
- Puedes navegar

**SI FALLA** ❌:

- Vuelve a `diagnostico_jwt.html`
- Click en "Verificar Token"
- Comparte captura con:
  - Payload del token
  - Status (válido/expirado)
  - Resultado de "Test Auth API"

---

## 📝 Archivos Modificados

1. ✅ `application/hooks/AuthHook.php`

   - Agregado 'adminpanel' a controladores públicos

2. ✅ `application/helpers/auth_helper.php`

   - Función `jwt_require()` ahora convierte payload a array
   - Verifica existencia de `$payload['rol']` antes de acceder

3. ✅ `application/controllers/Admin.php`

   - Usa `current_role()` helper en lugar de `$this->jwt->rol`

4. ✅ `assets/js/login-admin.js`

   - Delay de 100ms antes de redirect

5. ✅ `diagnostico_jwt.html` (nuevo)

   - Herramienta de diagnóstico visual

6. ✅ `test_jwt.php` (nuevo)
   - Script de prueba de encode/decode

---

## 📞 Información para Reportar

Si el problema persiste, proporciona:

### **Información Básica**:

- Versión de PHP: `<?php echo PHP_VERSION; ?>`
- Navegador y versión
- Sistema operativo

### **Capturas**:

1. DevTools → Network → Request a `login` (POST)
   - Response Headers (debe tener `Set-Cookie: imenu_token=...`)
2. DevTools → Application → Cookies → localhost
   - Screenshot de cookie `imenu_token`
3. `diagnostico_jwt.html` → "Verificar Token"
   - Payload completo
   - Status (válido/expirado)

### **Logs**:

```bash
# Últimas 50 líneas del log
tail -50 application/logs/log-2025-10-20.php
```

---

**Creado**: 20 de octubre de 2025  
**Problema**: Token se establece pero sesión expira inmediatamente  
**Fix Principal**: Convertir payload a array en `jwt_require()`  
**Estado**: 🧪 En testing
