# Gestión de Pagos y Facturación - Sistema iMenu

## Índice
1. [Descripción General](#descripción-general)
2. [Funcionalidades Implementadas](#funcionalidades-implementadas)
3. [Arquitectura y Diseño](#arquitectura-y-diseño)
4. [Backend - Modelo y Controlador](#backend---modelo-y-controlador)
5. [Frontend - Vista y JavaScript](#frontend---vista-y-javascript)
6. [API Endpoints](#api-endpoints)
7. [Filtros Avanzados](#filtros-avanzados)
8. [Exportación de Datos](#exportación-de-datos)
9. [Casos de Prueba](#casos-de-prueba)
10. [Seguridad](#seguridad)
11. [Mejoras Futuras](#mejoras-futuras)

---

## Descripción General

El módulo de **Gestión de Pagos y Facturación** permite al administrador del sistema:
- Visualizar todos los pagos registrados en el sistema
- Filtrar pagos por múltiples criterios (fecha, estado, tenant, método)
- Ver detalles completos de cada pago con información relacionada
- Exportar reportes a CSV o Excel
- Monitorear estadísticas en tiempo real (ingresos, estados)
- Asociar pagos con suscripciones y tenants

---

## Funcionalidades Implementadas

### ✅ Listado Completo de Pagos
- Tabla con 9 columnas: ID, Tenant, Concepto, Monto, Método, Referencia, Estado, Fecha, Acciones
- Renderizado dinámico con badges de colores según estado
- Información del tenant con nombre y slug
- Formato de moneda con 2 decimales

### ✅ Filtros Avanzados
- **Por Tenant**: Dropdown con todos los tenants del sistema
- **Por Estado**: Pagado, Pendiente, Fallido
- **Por Método de Pago**: Tarjeta, Transferencia, PayPal, Efectivo, Otro
- **Por Concepto**: Búsqueda por texto (LIKE en SQL)
- **Por Fecha**: Rango de fechas (inicio y fin)
- Botón para limpiar todos los filtros

### ✅ Detalles Completos del Pago
Modal con 3 secciones:
1. **Información del Pago**: Concepto, monto, método, referencia, estado, fecha, notas
2. **Información del Tenant**: Nombre, email, slug, estado (activo/suspendido)
3. **Suscripción Asociada** (si existe): Plan, precio, fechas, estado

### ✅ Estadísticas en Tiempo Real
4 tarjetas con métricas:
- **Ingresos del Mes**: Suma de pagos exitosos del mes actual
- **Pagos Procesados**: Total de pagos con estado "pagado"
- **Pagos Pendientes**: Total de pagos con estado "pendiente"
- **Pagos Fallidos**: Total de pagos con estado "fallido"

Las estadísticas se actualizan dinámicamente al aplicar filtros.

### ✅ Exportación de Datos
Dos formatos disponibles:
1. **CSV**: Compatible con Excel, incluye BOM UTF-8
2. **Excel (.xls)**: Con formato HTML, colores en estados, alineación

Los filtros activos se aplican automáticamente a la exportación.

---

## Arquitectura y Diseño

### Estructura de Archivos

```
application/
├── models/
│   └── Pago_model.php          (11 métodos: filtros, JOIN, stats, CRUD)
├── controllers/
│   └── Admin.php               (4 endpoints: pagos, pago_stats, pago_detail, pago_export)
├── views/
│   └── admin/
│       └── pagos.php           (Vista principal con filtros y modales)
└── config/
    └── routes.php              (3 rutas añadidas)

assets/
└── js/
    └── admin.js                (9 funciones nuevas para pagos)

docs/
└── GESTION_PAGOS_FACTURACION.md (Este documento)
```

### Base de Datos

Tabla `pagos`:
```sql
CREATE TABLE `pagos` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `suscripcion_id` int(11) DEFAULT NULL,
  `concepto` varchar(255) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo` varchar(50) DEFAULT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `status` enum('pagado','pendiente','fallido') DEFAULT 'pendiente',
  `fecha` datetime NOT NULL,
  `notas` text DEFAULT NULL,
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`suscripcion_id`) REFERENCES `suscripciones`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Relaciones:**
- `tenant_id` → `tenants.id` (CASCADE)
- `suscripcion_id` → `suscripciones.id` (SET NULL)

---

## Backend - Modelo y Controlador

### Pago_model.php

#### Métodos Implementados

##### 1. `get_with_filters($filters = [])`
Obtiene pagos con JOIN a tenants y filtros múltiples.

**Parámetros:**
```php
$filters = [
    'tenant_id' => 5,                  // int
    'status' => 'pagado',              // pagado|pendiente|fallido
    'metodo' => 'tarjeta',             // string
    'fecha_inicio' => '2025-01-01',    // Y-m-d
    'fecha_fin' => '2025-12-31',       // Y-m-d
    'concepto' => 'suscripción'        // string (búsqueda LIKE)
];
```

**Retorna:** Array de objetos con campos:
```php
[
    'id', 'tenant_id', 'suscripcion_id', 'concepto', 'monto', 
    'metodo', 'referencia', 'status', 'fecha', 'notas',
    'tenant_nombre', 'tenant_slug'  // JOIN
]
```

##### 2. `get_with_relations($id)`
Obtiene un pago con JOIN completo a tenants, suscripciones y planes.

**Retorna:** Objeto con 20+ campos:
```php
{
    // Pago
    'id', 'concepto', 'monto', 'metodo', 'referencia', 'status', 'fecha', 'notas',
    
    // Tenant
    'tenant_nombre', 'tenant_email', 'tenant_slug', 'tenant_activo',
    
    // Suscripción
    'suscripcion_id', 'suscripcion_inicio', 'suscripcion_fin', 'suscripcion_estatus',
    
    // Plan
    'plan_nombre', 'plan_precio'
}
```

##### 3. `get_stats($filters = [])`
Calcula estadísticas de pagos.

**Parámetros:** Mismos filtros que `get_with_filters()` (fecha, tenant)

**Retorna:**
```php
[
    'total_pagos' => 150,
    'total_ingresos' => 12500.00,
    'pagos_exitosos' => 140,
    'pagos_pendientes' => 5,
    'pagos_fallidos' => 5,
    'ingresos_mes' => 2300.00  // Mes actual
]
```

##### 4-11. Métodos Auxiliares
- `get_all()` - Todos los pagos sin filtros
- `where($field, $value)` - Chainable
- `order_by($field, $dir)` - Chainable
- `limit($limit, $offset)` - Chainable
- `get_results()` - Ejecutar query chainable
- `get_one()` - Un solo resultado
- `get($id)` - Pago por ID
- `insert($data)`, `update($id, $data)`, `delete($id)` - CRUD

### Admin.php (Controlador)

#### Endpoints Implementados

##### 1. `GET /admin/pagos`
Lista pagos con filtros opcionales.

**Query Parameters:**
```
?tenant_id=5
&status=pagado
&metodo=tarjeta
&fecha_inicio=2025-01-01
&fecha_fin=2025-12-31
&concepto=suscripcion
```

**Respuesta:**
```json
{
  "ok": true,
  "data": [
    {
      "id": 1,
      "tenant_id": 5,
      "tenant_nombre": "Restaurante El Buen Sabor",
      "tenant_slug": "el-buen-sabor",
      "concepto": "Pago de suscripción Plan Premium",
      "monto": "199.00",
      "metodo": "tarjeta",
      "referencia": "TRX-20250115-001",
      "status": "pagado",
      "fecha": "2025-01-15 14:30:00",
      "notas": "Pago procesado correctamente"
    }
  ]
}
```

##### 2. `GET /admin/pago_stats`
Estadísticas de pagos.

**Query Parameters:**
```
?fecha_inicio=2025-01-01
&fecha_fin=2025-12-31
&tenant_id=5
```

**Respuesta:**
```json
{
  "ok": true,
  "data": {
    "total_pagos": 150,
    "total_ingresos": 12500.00,
    "pagos_exitosos": 140,
    "pagos_pendientes": 5,
    "pagos_fallidos": 5,
    "ingresos_mes": 2300.00
  }
}
```

##### 3. `GET /admin/pago_detail/{id}`
Detalles completos de un pago con relaciones.

**Respuesta:**
```json
{
  "ok": true,
  "data": {
    "id": 1,
    "concepto": "Pago de suscripción Plan Premium",
    "monto": "199.00",
    "metodo": "tarjeta",
    "referencia": "TRX-20250115-001",
    "status": "pagado",
    "fecha": "2025-01-15 14:30:00",
    "notas": "Pago procesado",
    "tenant_nombre": "Restaurante El Buen Sabor",
    "tenant_email": "contacto@elbuensabor.com",
    "tenant_slug": "el-buen-sabor",
    "tenant_activo": "1",
    "suscripcion_id": "5",
    "suscripcion_inicio": "2025-01-01",
    "suscripcion_fin": "2025-02-01",
    "suscripcion_estatus": "activa",
    "plan_nombre": "Plan Premium",
    "plan_precio": "199.00"
  }
}
```

##### 4. `GET /admin/pago_export`
Exporta pagos a CSV o Excel.

**Query Parameters:**
```
?formato=csv               (csv|excel)
&tenant_id=5
&status=pagado
&metodo=tarjeta
&fecha_inicio=2025-01-01
&fecha_fin=2025-12-31
```

**Respuesta:** Descarga de archivo
- **CSV**: `pagos_2025-10-18_143000.csv` (UTF-8 con BOM)
- **Excel**: `pagos_2025-10-18_143000.xls` (HTML con estilos)

**Formato CSV:**
```csv
ID,Tenant,Slug,Concepto,Monto,Método,Referencia,Estado,Fecha,Notas
1,Restaurante El Buen Sabor,el-buen-sabor,Pago de suscripción,199.00,tarjeta,TRX-001,pagado,2025-01-15 14:30:00,Pago procesado
```

**Formato Excel:** HTML con colores de fondo en estados (verde=pagado, amarillo=pendiente, rojo=fallido)

---

## Frontend - Vista y JavaScript

### pagos.php (Vista)

#### Estructura

1. **Header con botón de exportación**
   ```html
   <button id="btn-export-pagos">
       <i class="fas fa-download"></i> Exportar Reporte
   </button>
   ```

2. **Tarjetas de estadísticas** (4 cards)
   - IDs dinámicos: `stat-ingresos-mes`, `stat-pagos-exitosos`, etc.
   - Se actualizan con JavaScript

3. **Card de filtros**
   - Form con 6 inputs: tenant, estado, método, concepto, fecha_inicio, fecha_fin
   - Botones: "Filtrar" y "Limpiar"

4. **Tabla de pagos**
   - Responsive con `table-responsive`
   - Hover effects con `table-hover`
   - Tbody con ID `pagos-tbody` (renderizado dinámico)

5. **Modal de detalles** (`#pagoDetalleModal`)
   - 3 cards con información (pago, tenant, suscripción)
   - Layout responsive con `modal-lg`

6. **Modal de exportación** (`#exportPagosModal`)
   - Select de formato (CSV/Excel)
   - Inputs de fecha opcionales
   - Info: "Se exportarán según filtros actuales"

### admin.js (JavaScript)

#### Funciones Implementadas

##### 1. `fetchPagos(filters = {})`
Obtiene pagos del servidor con filtros.

```javascript
await fetchPagos({
    tenant_id: '5',
    status: 'pagado',
    metodo: 'tarjeta',
    fecha_inicio: '2025-01-01',
    fecha_fin: '2025-12-31',
    concepto: 'suscripcion'
});
```

**Comportamiento:**
- Construye query string con URLSearchParams
- Llama a `api.pagos + '?' + queryString`
- Renderiza tabla con `renderPagos()`
- Actualiza stats con `fetchPagosStats()`

##### 2. `fetchPagosStats(filters = {})`
Obtiene y actualiza estadísticas.

```javascript
await fetchPagosStats({
    fecha_inicio: '2025-01-01',
    fecha_fin: '2025-12-31'
});
```

##### 3. `updatePagosStats(stats)`
Actualiza las 4 tarjetas de estadísticas.

```javascript
updatePagosStats({
    ingresos_mes: 2300.00,
    pagos_exitosos: 140,
    pagos_pendientes: 5,
    pagos_fallidos: 5
});
```

##### 4. `renderPagos(rows)`
Renderiza tabla con badges de colores.

**Badges:**
- `badge-success` → Estado "pagado" (verde)
- `badge-warning` → Estado "pendiente" (amarillo)
- `badge-danger` → Estado "fallido" (rojo)
- `badge-info` → Método de pago (azul)

**Formato:**
- Monto: `text-success font-weight-bold` con 2 decimales
- Tenant: Nombre en negrita + slug en gris pequeño
- Fecha: Formato DD/MM/YYYY

##### 5. `onVerPago(e)`
Maneja clic en botón "Ver detalles".

```javascript
btn.addEventListener('click', onVerPago);
```

**Flujo:**
1. Obtiene ID del botón (`data-id`)
2. Llama a `api.pago_detail(id)`
3. Abre modal con `mostrarDetallePago()`

##### 6. `mostrarDetallePago(pago)`
Popula y muestra modal de detalles.

**Lógica de badges:**
- Estado pago: Verde (pagado), Amarillo (pendiente), Rojo (fallido)
- Estado tenant: Verde (activo=1), Gris (suspendido=0)
- Estado suscripción: Verde/Amarillo/Rojo según estatus

**Tarjeta de suscripción:**
- Se oculta si `pago.suscripcion_id` es null
- Se muestra con info del plan si existe

##### 7. `loadTenantsForFilterSelect()`
Carga dropdown de tenants para filtros.

```javascript
<select id="filtro-tenant">
    <option value="">Todos los tenants</option>
    <option value="1">Restaurante ABC (abc)</option>
    <option value="2">Pizzería XYZ (xyz)</option>
</select>
```

##### 8. `aplicarFiltrosPagos(e)`
Maneja submit del formulario de filtros.

```javascript
form.addEventListener('submit', aplicarFiltrosPagos);
```

**Comportamiento:**
- Previene submit (`e.preventDefault()`)
- Recolecta valores de los 6 inputs
- Llama a `fetchPagos(filters)`

##### 9. `limpiarFiltrosPagos()`
Limpia todos los filtros y recarga.

```javascript
btn.addEventListener('click', limpiarFiltrosPagos);
```

**Comportamiento:**
- Resetea valores de los 6 inputs
- Llama a `fetchPagos()` sin filtros

##### 10. `exportarPagos(e)`
Maneja exportación de datos.

```javascript
form.addEventListener('submit', exportarPagos);
```

**Comportamiento:**
1. Recolecta formato (CSV/Excel)
2. Recolecta fechas opcionales del modal
3. Añade filtros activos del formulario principal
4. Construye URL con query string
5. Abre en nueva ventana: `window.open(url, '_blank')`
6. Cierra modal
7. Muestra mensaje de éxito

##### Helpers
- `formatDate(dateStr)` - Convierte 'YYYY-MM-DD' a 'DD/MM/YYYY'
- `getBadgeClass(status)` - Retorna clase Bootstrap según estado
- `escapeHtml(str)` - Previene XSS

---

## API Endpoints

### Resumen

| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| GET | `/admin/pagos` | Lista con filtros | Admin |
| GET | `/admin/pago_stats` | Estadísticas | Admin |
| GET | `/admin/pago_detail/{id}` | Detalles completos | Admin |
| GET | `/admin/pago_export` | Exportar CSV/Excel | Admin |

### Autenticación

Todos los endpoints validan rol de administrador mediante JWT:

```php
if (current_role() !== 'admin') {
    $this->_api_error(403, 'Acceso denegado');
    return;
}
```

### Códigos de Respuesta

| Código | Significado |
|--------|-------------|
| 200 | Éxito |
| 400 | Parámetros inválidos |
| 403 | Sin permisos (no admin) |
| 404 | Recurso no encontrado |
| 500 | Error del servidor |

---

## Filtros Avanzados

### Tipos de Filtros

#### 1. Filtro por Tenant
- **Campo:** `tenant_id`
- **Tipo:** Dropdown con todos los tenants
- **Backend:** `WHERE pagos.tenant_id = ?`

#### 2. Filtro por Estado
- **Campo:** `status`
- **Valores:** pagado, pendiente, fallido
- **Backend:** `WHERE pagos.status = ?`

#### 3. Filtro por Método
- **Campo:** `metodo`
- **Valores:** tarjeta, transferencia, paypal, efectivo, otro
- **Backend:** `WHERE pagos.metodo = ?`

#### 4. Filtro por Concepto
- **Campo:** `concepto`
- **Tipo:** Búsqueda por texto
- **Backend:** `WHERE pagos.concepto LIKE '%keyword%'`

#### 5. Filtro por Rango de Fechas
- **Campos:** `fecha_inicio`, `fecha_fin`
- **Formato:** YYYY-MM-DD
- **Backend:** 
  ```sql
  WHERE pagos.fecha >= 'fecha_inicio' 
    AND pagos.fecha <= 'fecha_fin'
  ```

### Combinación de Filtros

Los filtros se pueden combinar libremente:

**Ejemplo:** Pagos exitosos de un tenant en diciembre 2024
```
?tenant_id=5
&status=pagado
&fecha_inicio=2024-12-01
&fecha_fin=2024-12-31
```

---

## Exportación de Datos

### Formato CSV

**Características:**
- Encoding: UTF-8 con BOM (compatible con Excel)
- Separador: Coma (`,`)
- Headers: 10 columnas
- Nombre archivo: `pagos_YYYY-MM-DD_HHMMSS.csv`

**Headers:**
```
ID,Tenant,Slug,Concepto,Monto,Método,Referencia,Estado,Fecha,Notas
```

**Ejemplo:**
```csv
1,Restaurante ABC,abc,Suscripción Premium,199.00,tarjeta,TRX-001,pagado,2025-01-15 14:30:00,OK
2,Pizzería XYZ,xyz,Suscripción Básica,99.00,transferencia,REF-002,pendiente,2025-01-16 10:00:00,
```

### Formato Excel (.xls)

**Características:**
- Formato: HTML con XML para Excel
- Estilos: Colores de fondo en estados
- Headers: Fila azul con texto blanco
- Nombre archivo: `pagos_YYYY-MM-DD_HHMMSS.xls`

**Colores:**
- Header: `#4e73df` (azul)
- Pagado: `#28a745` (verde)
- Pendiente: `#ffc107` (amarillo)
- Fallido: `#dc3545` (rojo)

**Ventajas:**
- Visualización inmediata de estados
- Formato de moneda con alineación derecha
- Compatible con Microsoft Excel y LibreOffice Calc

### Uso Desde la Vista

1. Aplicar filtros deseados
2. Clic en "Exportar Reporte"
3. Seleccionar formato (CSV o Excel)
4. (Opcional) Ajustar rango de fechas
5. Clic en "Exportar"
6. Archivo se descarga automáticamente

---

## Casos de Prueba

### Caso 1: Listar Todos los Pagos
**Objetivo:** Ver lista completa sin filtros

**Pasos:**
1. Navegar a `/admin/pagos_view`
2. Verificar que se carguen los pagos en la tabla
3. Verificar badges de colores según estado
4. Verificar estadísticas en las 4 tarjetas

**Resultado Esperado:**
- Tabla con todos los pagos
- Stats calculadas correctamente
- Badges: Verde (pagado), Amarillo (pendiente), Rojo (fallido)

---

### Caso 2: Filtrar por Tenant
**Objetivo:** Ver solo pagos de un tenant específico

**Pasos:**
1. En filtros, seleccionar tenant "Restaurante ABC"
2. Clic en "Filtrar"
3. Verificar que solo se muestren pagos de ese tenant

**Resultado Esperado:**
- Tabla filtra correctamente
- Stats se actualizan (solo ese tenant)
- Columna "Tenant" muestra siempre el mismo

---

### Caso 3: Filtrar por Estado
**Objetivo:** Ver solo pagos pagados

**Pasos:**
1. En filtros, seleccionar estado "Pagado"
2. Clic en "Filtrar"
3. Verificar que todos tengan badge verde

**Resultado Esperado:**
- Tabla solo con pagos exitosos
- Stat "Pagos Procesados" = total en tabla
- Stats "Pendientes" y "Fallidos" = 0

---

### Caso 4: Filtrar por Rango de Fechas
**Objetivo:** Ver pagos de enero 2025

**Pasos:**
1. Fecha inicio: `2025-01-01`
2. Fecha fin: `2025-01-31`
3. Clic en "Filtrar"
4. Verificar fechas en columna "Fecha"

**Resultado Esperado:**
- Todos los pagos están entre el 1 y 31 de enero
- Stats reflejan solo ese mes

---

### Caso 5: Ver Detalles de un Pago
**Objetivo:** Abrir modal con información completa

**Pasos:**
1. Clic en botón "Ver" (ojo) de cualquier pago
2. Verificar modal con 3 secciones
3. Verificar que suscripción solo se muestre si existe

**Resultado Esperado:**
- Modal se abre
- Info del pago completa
- Info del tenant completa
- Tarjeta de suscripción visible solo si hay `suscripcion_id`

---

### Caso 6: Exportar a CSV
**Objetivo:** Descargar archivo CSV

**Pasos:**
1. Aplicar filtro (ej: estado=pagado)
2. Clic en "Exportar Reporte"
3. Seleccionar formato "CSV"
4. Clic en "Exportar"
5. Abrir archivo descargado en Excel

**Resultado Esperado:**
- Archivo descarga con nombre `pagos_*.csv`
- Se abre correctamente en Excel con tildes
- Solo contiene pagos filtrados

---

### Caso 7: Exportar a Excel
**Objetivo:** Descargar archivo Excel con formato

**Pasos:**
1. Sin filtros
2. Clic en "Exportar Reporte"
3. Seleccionar formato "Excel"
4. Clic en "Exportar"
5. Abrir archivo en Excel

**Resultado Esperado:**
- Archivo `.xls` descarga
- Header azul con texto blanco
- Estados con colores de fondo
- Montos alineados a la derecha

---

### Caso 8: Limpiar Filtros
**Objetivo:** Resetear todos los filtros

**Pasos:**
1. Aplicar varios filtros (tenant, estado, fechas)
2. Clic en "Limpiar"
3. Verificar que todos los inputs se vacíen
4. Verificar que la tabla vuelva a mostrar todo

**Resultado Esperado:**
- Todos los filtros en valor por defecto
- Tabla con todos los pagos
- Stats recalculadas

---

### Caso 9: Pago con Suscripción
**Objetivo:** Ver detalles de pago asociado a suscripción

**Pasos:**
1. Insertar pago con `suscripcion_id` válido
2. Clic en "Ver" detalles
3. Verificar tarjeta de suscripción visible

**Resultado Esperado:**
- Tarjeta "Suscripción Asociada" visible
- Info del plan (nombre, precio)
- Fechas inicio/fin
- Estado de la suscripción

---

### Caso 10: Pago sin Suscripción
**Objetivo:** Ver detalles de pago sin asociación

**Pasos:**
1. Insertar pago con `suscripcion_id = NULL`
2. Clic en "Ver" detalles
3. Verificar tarjeta de suscripción oculta

**Resultado Esperado:**
- Tarjeta "Suscripción Asociada" no visible
- Solo se muestran info de pago y tenant

---

## Seguridad

### Autenticación y Autorización

**1. Verificación de Rol**
```php
if (current_role() !== 'admin') {
    $this->_api_error(403, 'Solo admin puede acceder');
    return;
}
```
Todos los endpoints validan que el usuario tenga rol `admin`.

**2. JWT con HttpOnly Cookie**
- Token almacenado en cookie HttpOnly (no accesible desde JS)
- Validez: 8 horas
- Se valida en cada request al backend

### Prevención de Inyección SQL

**Uso de Query Builder de CodeIgniter:**
```php
$this->db->where('tenant_id', $tenant_id);  // Escaped
$this->db->like('concepto', $concepto);     // Escaped
```
Todos los inputs se escapan automáticamente.

### Prevención de XSS

**Frontend:**
```javascript
function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
```

**Backend (en exportación Excel):**
```php
echo htmlspecialchars($pago->concepto);
```

### Validación de Parámetros

**Validación de ID:**
```php
if (!$id) {
    $this->_api_error(400, 'ID de pago requerido');
    return;
}
```

**Validación de Formato:**
```php
if ($formato !== 'csv' && $formato !== 'excel') {
    $this->_api_error(400, 'Formato no soportado');
    return;
}
```

### CSRF Protection

CodeIgniter tiene CSRF habilitado por defecto en formularios POST.

Para AJAX, se puede habilitar token en header:
```javascript
headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
}
```

---

## Mejoras Futuras

### Funcionalidades Adicionales

1. **Crear/Editar Pagos desde Admin**
   - Modal con formulario para crear pago manual
   - Asociar automáticamente a suscripción
   - Enviar recibo por email

2. **Recibos PDF Descargables**
   - Generar PDF con librería TCPDF o mPDF
   - Incluir logo del sistema
   - Detalles del pago y tenant
   - Código QR con referencia

3. **Webhooks de Pasarelas**
   - Integración con Stripe, PayPal, MercadoPago
   - Actualizar estado automáticamente
   - Registrar intentos fallidos
   - Reintentos automáticos

4. **Reportes Avanzados**
   - Gráficas con Chart.js (ingresos por mes)
   - Comparativa mes a mes
   - Proyección de ingresos
   - Top tenants por ingresos

5. **Recordatorios Automáticos**
   - Email a tenants con pagos pendientes
   - Cron job diario
   - Suspender tenant si lleva X días pendiente

6. **Conciliación Bancaria**
   - Importar movimientos del banco (CSV)
   - Match automático con referencias
   - Marcar como conciliados

7. **Facturación Electrónica**
   - Generar facturas fiscales (México: CFDI)
   - Integración con PAC (Proveedor Autorizado Certificación)
   - Timbrado automático

8. **Multi-moneda**
   - Soporte para USD, MXN, EUR
   - Tipos de cambio automáticos
   - Conversión en reportes

9. **Descuentos y Cupones**
   - Aplicar cupones a pagos
   - Descuentos por volumen
   - Historial de promociones

10. **Refunds (Devoluciones)**
    - Crear pagos de reembolso
    - Asociar con pago original
    - Actualizar estadísticas

### Optimizaciones Técnicas

1. **Paginación**
   - Limitar tabla a 50 registros por página
   - Controles de navegación
   - DataTables con AJAX

2. **Cache**
   - Cachear stats con Redis/Memcached
   - TTL de 5 minutos
   - Invalidar al crear/editar pago

3. **Búsqueda Full-Text**
   - Índice full-text en `concepto` y `notas`
   - Búsqueda más rápida

4. **Logs de Auditoría**
   - Registrar quién exportó qué
   - Tabla `audit_logs`
   - Incluir IP y timestamp

5. **Tests Automatizados**
   - PHPUnit para backend
   - Jest para frontend
   - Cobertura > 80%

---

## Anexos

### Estructura de Datos Completa

**Objeto Pago (con relaciones):**
```json
{
  "id": 1,
  "tenant_id": 5,
  "suscripcion_id": 3,
  "concepto": "Pago de suscripción Plan Premium - Enero 2025",
  "monto": "199.00",
  "metodo": "tarjeta",
  "referencia": "TRX-20250115-001-ABC",
  "status": "pagado",
  "fecha": "2025-01-15 14:30:45",
  "notas": "Pago procesado correctamente mediante Stripe. Recibo enviado al email del tenant.",
  "tenant_nombre": "Restaurante El Buen Sabor",
  "tenant_email": "contacto@elbuensabor.com",
  "tenant_slug": "el-buen-sabor",
  "tenant_activo": "1",
  "suscripcion_id": "3",
  "suscripcion_inicio": "2025-01-01",
  "suscripcion_fin": "2025-02-01",
  "suscripcion_estatus": "activa",
  "plan_nombre": "Plan Premium",
  "plan_precio": "199.00"
}
```

### Códigos de Estado HTTP

| Código | Nombre | Uso |
|--------|--------|-----|
| 200 | OK | Request exitoso |
| 400 | Bad Request | Parámetros inválidos |
| 401 | Unauthorized | No autenticado |
| 403 | Forbidden | Sin permisos (no admin) |
| 404 | Not Found | Pago no encontrado |
| 500 | Internal Server Error | Error del servidor |

### Glosario

- **Tenant**: Restaurante/negocio que usa el sistema
- **Suscripción**: Periodo de acceso al sistema
- **Plan**: Nivel de servicio (Básico, Premium, etc.)
- **Concepto**: Descripción del pago
- **Referencia**: Código único del pago (de pasarela)
- **Badge**: Etiqueta de color en UI
- **Modal**: Ventana emergente
- **JOIN**: Combinación de tablas en SQL
- **Query String**: Parámetros en URL (`?key=value`)
- **CSV**: Comma-Separated Values (Excel)
- **BOM**: Byte Order Mark (para UTF-8 en Excel)

---

## Contacto y Soporte

Para más información sobre este módulo:

- Documentación técnica: `/docs/`
- Código fuente: `/application/` y `/assets/js/`
- Tests: `/tests/` (pendiente)

---

**Versión del documento:** 1.0  
**Fecha:** 18 de octubre de 2025  
**Autor:** Sistema iMenu - Módulo de Pagos y Facturación
