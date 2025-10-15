# Lista de Tareas (TODO)

## 🔥 Crítico (para que funcione el sistema)

1. **Crear librería QrGenerator.php** | completado

   - Implementar la librería para generar códigos QR que falta en `application/libraries/`

2. **Crear librería JWT** | Completado

   - Implementar `application/libraries/JWT.php` para encode/decode de tokens

3. **Crear controlador Auth.php** | Completado

   - Implementar login/logout para las rutas `api/auth/login` definidas en `routes.php`

4. **Crear modelos faltantes** | Completado

   - Implementar `Permission_model.php` y `Pedido_model.php` que son referenciados en `App.php` pero no existen

5. **Crear tabla permisos** | Completado

   - Agregar tabla `permisos` en `install.php` para sistema granular de permisos

6. **Crear tabla pedidos** | Completado

   - Agregar tablas `pedidos` y `pedido_items` en `install.php`

7. **Configurar autoload.php** | Completado

   - Autocargar helpers (`auth`, `tenant`, `url`) y librerías necesarias (`database`, `session`)

8. **Configurar database.php** | Completado

   - Configurar credenciales de la base de datos en `application/config/database.php`

## 🎯 Importante (funcionalidades core) | Completado

9. **Implementar tenant_helper.php** | Completado

   - Agregar funciones para resolver tenant por slug/subdominio (está vacío)

10. **Crear vistas del panel App**

    - Implementar HTML para `dashboard.php`, `categorias.php`, `productos.php`, `ajustes.php`

11. **Crear vistas del panel Admin**
    - Implementar HTML para `tenants.php`, `planes.php`, `pagos.php`

## 💅 Nice to have

12. **Crear archivos estáticos**
    - Agregar Bootstrap CSS/JS y assets necesarios en carpeta `/assets`
