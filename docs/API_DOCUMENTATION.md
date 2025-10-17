# API Documentation - iMenu

## Autenticación

Todos los endpoints requieren autenticación JWT. Incluye el token en el header:

```
Authorization: Bearer <jwt_token>
```

## Endpoints de Pedidos

### 1. Listar Pedidos

**GET** `/app/pedidos`

**Parámetros de consulta (opcionales):**

- `estado`: Filtrar por estado (pendiente, preparando, listo, entregado, cancelado)
- `fecha_inicio`: Fecha inicio (YYYY-MM-DD)
- `fecha_fin`: Fecha fin (YYYY-MM-DD)
- `cliente`: Buscar por nombre de cliente
- `metodo_pago`: Filtrar por método de pago
- `limit`: Límite de resultados (max 100, default 50)
- `offset`: Offset para paginación (default 0)
- `orden`: Orden de resultados (asc/desc, default desc)
- `order_by`: Campo de ordenamiento (creado_en, total, estado, nombre_cliente)

**Respuesta exitosa:**

```json
{
	"ok": true,
	"data": [
		{
			"id": 1,
			"nombre_cliente": "Juan Pérez",
			"telefono_cliente": "123456789",
			"total": 25.5,
			"metodo_pago": "efectivo",
			"estado": "pendiente",
			"total_items": 3,
			"creado_en": "2024-01-15 10:30:00"
		}
	],
	"pagination": {
		"total": 150,
		"limit": 50,
		"offset": 0,
		"has_more": true
	}
}
```

### 2. Crear Pedido

**POST** `/app/pedido_create`

**Parámetros:**

- `nombre_cliente` (requerido): Nombre del cliente
- `telefono_cliente` (opcional): Teléfono del cliente
- `metodo_pago` (opcional): Método de pago (default: efectivo)
- `items` (requerido): JSON array con items del pedido

**Ejemplo de items:**

```json
[
	{
		"producto_id": 1,
		"cantidad": 2
	},
	{
		"producto_id": 3,
		"cantidad": 1
	}
]
```

**Respuesta exitosa:**

```json
{
	"ok": true,
	"id": 123
}
```

### 3. Obtener Detalle de Pedido

**GET** `/app/pedido/{id}`

**Respuesta exitosa:**

```json
{
	"ok": true,
	"data": {
		"id": 123,
		"nombre_cliente": "Juan Pérez",
		"telefono_cliente": "123456789",
		"total": 25.5,
		"metodo_pago": "efectivo",
		"estado": "pendiente",
		"creado_en": "2024-01-15 10:30:00",
		"items": [
			{
				"id": 1,
				"producto_id": 5,
				"nombre": "Pizza Margherita",
				"cantidad": 1,
				"precio_unit": 15.0,
				"subtotal": 15.0
			}
		]
	}
}
```

### 4. Actualizar Estado de Pedido

**POST** `/app/pedido_update_estado/{id}`

**Parámetros:**

- `estado` (requerido): Nuevo estado (pendiente, preparando, listo, entregado, cancelado)

**Respuesta exitosa:**

```json
{
	"ok": true,
	"msg": "Estado actualizado"
}
```

### 5. Eliminar Pedido

**POST** `/app/pedido_delete/{id}` o **DELETE** `/app/pedido_delete/{id}`

⚠️ **Nota:** Solo usuarios con rol 'owner' pueden eliminar pedidos

**Respuesta exitosa:**

```json
{
	"ok": true,
	"msg": "Pedido eliminado"
}
```

### 6. Exportar Pedidos

**GET** `/app/pedidos_export`

**Parámetros de consulta:**

- `formato`: Formato de exportación (csv, json, excel)
- Todos los filtros de listar pedidos también aplican

**Respuesta:** Descarga del archivo exportado

## Endpoints de Configuración

### 7. Configuración de Notificaciones

**GET/POST** `/app/notificaciones_config`

⚠️ **Nota:** Solo usuarios con rol 'owner' pueden configurar notificaciones

**GET - Obtener configuración actual:**

```json
{
	"ok": true,
	"data": {
		"notif_email": "admin@restaurant.com",
		"notif_webhook": "https://webhook.site/abc123",
		"notif_whatsapp": "+1234567890"
	}
}
```

**POST - Actualizar configuración:**
**Parámetros:**

- `notif_email`: Email para notificaciones
- `notif_webhook`: URL webhook para notificaciones
- `notif_whatsapp`: Número WhatsApp (formato internacional)

## Códigos de Error

- **400 Bad Request:** Parámetros inválidos o faltantes
- **401 Unauthorized:** Token JWT inválido o faltante
- **403 Forbidden:** Permisos insuficientes
- **404 Not Found:** Recurso no encontrado
- **405 Method Not Allowed:** Método HTTP no permitido
- **500 Internal Server Error:** Error del servidor

## Notificaciones Automáticas

El sistema envía notificaciones automáticas cuando se crea un nuevo pedido:

1. **Email:** Si se configura un email de notificación
2. **Webhook:** Si se configura una URL de webhook
3. **WhatsApp:** Pendiente de implementación

### Formato de Webhook

```json
{
  "event": "new_order",
  "tenant_id": 1,
  "tenant_name": "Mi Restaurante",
  "order": {
    "id": 123,
    "cliente": "Juan Pérez",
    "telefono": "123456789",
    "total": 25.50,
    "metodo_pago": "efectivo",
    "estado": "pendiente",
    "items": [...],
    "created_at": "2024-01-15T10:30:00Z"
  },
  "timestamp": "2024-01-15T10:30:05Z"
}
```

## Seguridad

- Todas las operaciones están limitadas al tenant actual
- Validación estricta de entrada de datos
- Límites de rate limiting para prevenir abuso
- Logs de auditoría para todas las operaciones críticas

## Límites y Consideraciones

- Máximo 50 items por pedido
- Máximo 100 resultados por consulta
- Máximo 1000 registros por exportación
- Timeout de webhook: 10 segundos
