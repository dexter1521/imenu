# 🚀 Quick Start - Dashboard Tenant

## Configuración Rápida (5 minutos)

### Paso 1: Base de Datos

```sql
-- Ejecutar este script SQL
SOURCE db/add_subscription_fields.sql;

-- O copiar y pegar en phpMyAdmin/HeidiSQL
```

### Paso 2: Datos de Prueba

```sql
-- Asignar plan "Básico" al tenant ID 1
UPDATE tenants
SET plan_id = 2,  -- 2 = Básico (ver tabla planes)
    suscripcion_activa = 1,
    suscripcion_fin = DATE_ADD(NOW(), INTERVAL 30 DAY)
WHERE id = 1;

-- Crear un pedido de prueba para hoy
INSERT INTO pedidos (tenant_id, nombre_cliente, telefono_cliente, total, estado, metodo_pago, creado_en)
VALUES
(1, 'Cliente Prueba 1', '5551234567', 250.00, 'pendiente', 'efectivo', NOW()),
(1, 'Cliente Prueba 2', '5559876543', 450.50, 'completado', 'tarjeta', NOW());
```

### Paso 3: Acceder al Dashboard

```
http://localhost/imenu/app/dashboard
```

## ✅ ¿Qué deberías ver?

### Card 1: Pedidos de Hoy

- Debería mostrar: **2** (los 2 pedidos de prueba)

### Card 2: Ingresos de Hoy

- Debería mostrar: **$700.50** (suma de 250.00 + 450.50)

### Card 3: Productos Activos

- Muestra el total de productos con `activo = 1`
- Límite: **/100 máx** (del plan Básico)

### Card 4: Categorías

- Muestra el total de categorías
- Límite: **/15 máx** (del plan Básico)

### Sección Plan

- **Plan Actual**: Básico
- **Días Restantes**: ~30 (en verde)
- **Estado**: Badge verde "Activa"

### Tabla Pedidos Recientes

- 2 filas con los pedidos creados
- Botones "Ver" funcionando

## 🎨 Colores de Días Restantes

| Días | Color       | Significado     |
| ---- | ----------- | --------------- |
| > 15 | 🟢 Verde    | Todo bien       |
| 8-15 | 🟡 Amarillo | Atención        |
| ≤ 7  | 🔴 Rojo     | Urgente renovar |

## 🔄 Auto-Refresh

El dashboard se actualiza automáticamente cada **60 segundos**.
Verás el timestamp de última actualización en la esquina superior derecha.

## 🧪 Pruebas Adicionales

### Probar Plan Sin Límites (Premium)

```sql
UPDATE tenants
SET plan_id = 4  -- 4 = Premium
WHERE id = 1;

-- Refrescar página: límites no deberían aparecer
```

### Probar Suscripción Expirada

```sql
UPDATE tenants
SET suscripcion_fin = DATE_SUB(NOW(), INTERVAL 5 DAY),
    suscripcion_activa = 0
WHERE id = 1;

-- Refrescar: badge "Inactiva" en rojo, días en 0
```

### Probar Sin Pedidos

```sql
DELETE FROM pedidos WHERE tenant_id = 1;

-- Refrescar: debe mostrar "0" pedidos, $0.00 ingresos
```

## 📱 Responsive

El dashboard funciona en:

- 💻 Desktop (4 columnas)
- 📱 Tablet (2 columnas)
- 📱 Mobile (1 columna apilada)

## ⚡ Performance

- Primera carga: ~300-500ms
- Auto-refresh: ~100-200ms (solo datos JSON)
- Sin jQuery: carga más rápida

## 🐛 Si algo no funciona...

1. **Abrir consola del navegador** (F12)
2. **Verificar errores** en Network/Console
3. **Comprobar endpoint**: `/app/dashboard_data` debe retornar JSON
4. **Validar datos**: La tabla `planes` debe tener 4 registros
5. **Verificar tenant**: Debe tener `plan_id` asignado

## 📞 Soporte

Revisar documentación completa en: `docs/DASHBOARD_TENANT.md`
