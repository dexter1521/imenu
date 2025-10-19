# Dashboard Administrativo - iMenu SaaS

## 📊 Descripción General

El Dashboard Administrativo es el centro de comando del panel de administración de iMenu, proporcionando una vista consolidada de todas las métricas clave del negocio en un solo lugar. Permite a los administradores monitorear la salud del sistema, el rendimiento financiero, la actividad de los tenants y las tendencias de crecimiento.

---

## 🎯 Características Principales

### 1. **KPIs en Tiempo Real**

- 8 tarjetas de métricas principales con indicadores visuales
- Indicadores de crecimiento con flechas y porcentajes
- Colores semánticos (verde=éxito, amarillo=advertencia, rojo=error)
- Actualización mediante botón de refresh

### 2. **Visualización de Datos**

- Gráfica de línea dual (Chart.js) con 12 meses de historial
- Eje Y izquierdo: Ingresos en dólares ($)
- Eje Y derecho: Cantidad de pagos exitosos
- Curvas suavizadas para mejor legibilidad

### 3. **Análisis de Tendencias**

- Top 5 planes más populares con barras de progreso
- Métricas de retención de clientes
- Proyecciones de ingresos basadas en promedios
- Comparativas mes actual vs mes anterior

### 4. **Resúmenes Detallados**

- Resumen de Tenants (activos, suspendidos, nuevos)
- Resumen de Ingresos (total, mensual, proyecciones)
- Resumen de Pedidos (totales, promedios, estados)

---

## 🛠️ Arquitectura Técnica

### **Stack Tecnológico**

- **Backend**: PHP 7.4+ / CodeIgniter 3
- **Frontend**: Bootstrap 4 (SB Admin 2)
- **Visualización**: Chart.js 3.9.1
- **AJAX**: jQuery
- **Database**: MySQL 5.7+

### **Flujo de Datos**

```
┌─────────────┐
│  dashboard  │  Vista (PHP)
└──────┬──────┘
       │
       ▼
┌─────────────────┐
│   admin.js      │  JavaScript
│ fetchDashboard  │
│     Stats()     │
└──────┬──────────┘
       │ AJAX GET
       ▼
┌─────────────────────┐
│ Admin::dashboard_   │  Controlador
│      stats()        │
└──────┬──────────────┘
       │
       ├─► Tenant_model::get_dashboard_stats()
       ├─► Plan_model::get_dashboard_stats()
       ├─► Pago_model::get_revenue_stats()
       ├─► Pedido_model::get_global_stats()
       └─► Suscripcion_model::get_dashboard_stats()
       │
       ▼
┌─────────────────┐
│  JSON Response  │
└─────────────────┘
```

---

## 📡 API Endpoints

### **GET /admin/dashboard**

Renderiza la vista principal del dashboard.

**Autenticación**: Requerida (sesión admin)

**Response**: HTML View

---

### **GET /admin/dashboard_stats**

Endpoint AJAX que retorna todas las estadísticas consolidadas del sistema.

**Autenticación**: Requerida (sesión admin)

**Response Type**: JSON

**Estructura de Respuesta**:

```json
{
	"tenants": {
		"total": 150,
		"activos": 142,
		"suspendidos": 8,
		"nuevos_mes": 12,
		"nuevos_semana": 3
	},
	"planes": {
		"total": 4,
		"premium": 2,
		"basico": 2
	},
	"planes_populares": [
		{
			"id": "3",
			"nombre": "Plan Premium",
			"precio_mensual": "49.99",
			"total_tenants": 85
		},
		{
			"id": "1",
			"nombre": "Plan Básico",
			"precio_mensual": "19.99",
			"total_tenants": 50
		}
	],
	"ingresos": {
		"total": "45234.50",
		"mes_actual": "4250.00",
		"mes_anterior": "3890.00",
		"crecimiento_porcentaje": 9.25,
		"promedio_diario": "141.67"
	},
	"pagos": {
		"total": 456,
		"exitoso": 423,
		"pendiente": 28,
		"rechazado": 5
	},
	"pedidos": {
		"total": 8934,
		"mes_actual": 782,
		"ultima_semana": 165,
		"por_estado": {
			"pendiente": 45,
			"en_preparacion": 23,
			"completado": 714
		},
		"promedio_diario": 26.1
	},
	"suscripciones": {
		"total": 150,
		"activas": 142,
		"expirando_pronto": 8,
		"expiradas": 0,
		"nuevas_mes": 12
	},
	"grafica_ingresos": [
		{
			"mes": "Nov 2024",
			"ingresos": "3450.00",
			"pagos_exitosos": 38
		},
		{
			"mes": "Dic 2024",
			"ingresos": "3890.00",
			"pagos_exitosos": 42
		},
		{
			"mes": "Ene 2025",
			"ingresos": "4250.00",
			"pagos_exitosos": 45
		}
		// ... 12 meses en total
	],
	"metricas_generales": {
		"total_usuarios_sistema": 450,
		"tasa_retencion": 94.67,
		"ingreso_promedio_por_tenant": "29.93",
		"proyeccion_mensual": "4250.00"
	}
}
```

**Códigos de Estado**:

- `200 OK`: Éxito
- `401 Unauthorized`: No autenticado
- `403 Forbidden`: No es admin
- `500 Internal Server Error`: Error del servidor

---

## 🗂️ Estructura de Archivos

```
application/
├── controllers/
│   └── Admin.php                    # Controlador principal
│       ├── dashboard()              # Vista del dashboard
│       └── dashboard_stats()        # API de estadísticas
│
├── models/
│   ├── Tenant_model.php             # Estadísticas de tenants
│   │   └── get_dashboard_stats()
│   ├── Plan_model.php               # Estadísticas de planes
│   │   ├── get_dashboard_stats()
│   │   └── get_most_popular($limit)
│   ├── Pago_model.php               # Estadísticas de ingresos
│   │   ├── get_revenue_stats()
│   │   ├── get_total_revenue()
│   │   ├── get_monthly_revenue($months)
│   │   └── get_stats($filters)
│   ├── Pedido_model.php             # Estadísticas de pedidos
│   │   └── get_global_stats()
│   └── Suscripcion_model.php        # Estadísticas de suscripciones
│       └── get_dashboard_stats()
│
├── views/
│   └── admin/
│       └── dashboard.php            # Vista principal del dashboard
│
└── config/
    └── routes.php                   # Rutas configuradas

assets/
└── js/
    └── admin.js                     # Lógica del dashboard
        ├── fetchDashboardStats()
        ├── renderDashboardStats()
        ├── updateKPICards()
        ├── renderIngresosChart()
        ├── renderPlanesPopulares()
        ├── updateResumenTenants()
        ├── updateResumenIngresos()
        └── updateResumenPedidos()
```

---

## 💾 Métodos del Modelo

### **Tenant_model::get_dashboard_stats()**

Obtiene estadísticas de tenants del sistema.

**Retorna**:

```php
[
    'total' => 150,           // Total de tenants
    'activos' => 142,         // Status = 'activo'
    'suspendidos' => 8,       // Status = 'suspendido'
    'nuevos_mes' => 12,       // Creados este mes
    'nuevos_semana' => 3      // Creados últimos 7 días
]
```

**Query SQL**:

```sql
SELECT
    COUNT(*) as total,
    SUM(CASE WHEN status = 'activo' THEN 1 ELSE 0 END) as activos,
    SUM(CASE WHEN status = 'suspendido' THEN 1 ELSE 0 END) as suspendidos,
    SUM(CASE WHEN MONTH(fecha_registro) = MONTH(NOW())
        AND YEAR(fecha_registro) = YEAR(NOW()) THEN 1 ELSE 0 END) as nuevos_mes,
    SUM(CASE WHEN fecha_registro >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        THEN 1 ELSE 0 END) as nuevos_semana
FROM tenants
```

---

### **Plan_model::get_dashboard_stats()**

Obtiene estadísticas de planes del sistema.

**Retorna**:

```php
[
    'total' => 4,            // Total de planes
    'premium' => 2,          // Planes con precio > 30
    'basico' => 2            // Planes con precio <= 30
]
```

---

### **Plan_model::get_most_popular($limit = 5)**

Obtiene los planes más populares por cantidad de tenants suscritos.

**Parámetros**:

- `$limit` (int): Cantidad máxima de planes a retornar (default: 5)

**Retorna**:

```php
[
    [
        'id' => '3',
        'nombre' => 'Plan Premium',
        'descripcion' => 'Funcionalidades avanzadas',
        'precio_mensual' => '49.99',
        'total_tenants' => 85
    ],
    // ... más planes
]
```

**Query SQL**:

```sql
SELECT
    p.id,
    p.nombre,
    p.descripcion,
    p.precio_mensual,
    COUNT(t.id) as total_tenants
FROM planes p
LEFT JOIN tenants t ON t.plan_id = p.id
GROUP BY p.id, p.nombre, p.descripcion, p.precio_mensual
ORDER BY total_tenants DESC
LIMIT ?
```

---

### **Pago_model::get_revenue_stats()**

Obtiene estadísticas financieras consolidadas.

**Retorna**:

```php
[
    'total' => '45234.50',              // Ingresos totales acumulados
    'mes_actual' => '4250.00',          // Ingresos del mes actual
    'mes_anterior' => '3890.00',        // Ingresos del mes anterior
    'crecimiento_porcentaje' => 9.25,   // % de crecimiento
    'promedio_diario' => '141.67'       // Promedio diario del mes
]
```

**Cálculo de Crecimiento**:

```php
$crecimiento = (($mes_actual - $mes_anterior) / $mes_anterior) * 100;
```

---

### **Pago_model::get_monthly_revenue($months = 12)**

Obtiene ingresos mensuales para gráficas históricas.

**Parámetros**:

- `$months` (int): Número de meses a incluir (default: 12)

**Retorna**:

```php
[
    [
        'mes' => 'Nov 2024',
        'ingresos' => '3450.00',
        'pagos_exitosos' => 38
    ],
    [
        'mes' => 'Dic 2024',
        'ingresos' => '3890.00',
        'pagos_exitosos' => 42
    ],
    // ... hasta 12 meses
]
```

**Query SQL**:

```sql
SELECT
    DATE_FORMAT(fecha_pago, '%b %Y') as mes,
    SUM(monto) as ingresos,
    COUNT(*) as pagos_exitosos
FROM pagos
WHERE
    estado = 'exitoso'
    AND fecha_pago >= DATE_SUB(NOW(), INTERVAL ? MONTH)
GROUP BY DATE_FORMAT(fecha_pago, '%Y-%m')
ORDER BY DATE_FORMAT(fecha_pago, '%Y-%m') ASC
```

---

### **Pedido_model::get_global_stats()**

Obtiene estadísticas globales de pedidos.

**Retorna**:

```php
[
    'total' => 8934,                    // Total de pedidos
    'mes_actual' => 782,                // Pedidos del mes
    'ultima_semana' => 165,             // Pedidos últimos 7 días
    'por_estado' => [                   // Distribución por estado
        'pendiente' => 45,
        'en_preparacion' => 23,
        'completado' => 714
    ],
    'promedio_diario' => 26.1           // Promedio diario del mes
]
```

---

### **Suscripcion_model::get_dashboard_stats()**

Obtiene estadísticas de suscripciones.

**Retorna**:

```php
[
    'total' => 150,              // Total de suscripciones
    'activas' => 142,            // Status = 'activa'
    'expirando_pronto' => 8,     // Expiran en próximos 7 días
    'expiradas' => 0,            // Status = 'expirada'
    'nuevas_mes' => 12           // Creadas este mes
]
```

**Query SQL**:

```sql
SELECT
    COUNT(*) as total,
    SUM(CASE WHEN status = 'activa' THEN 1 ELSE 0 END) as activas,
    SUM(CASE WHEN status = 'activa'
        AND fecha_fin BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
        THEN 1 ELSE 0 END) as expirando_pronto,
    SUM(CASE WHEN status = 'expirada' THEN 1 ELSE 0 END) as expiradas,
    SUM(CASE WHEN MONTH(fecha_inicio) = MONTH(NOW())
        AND YEAR(fecha_inicio) = YEAR(NOW()) THEN 1 ELSE 0 END) as nuevas_mes
FROM suscripciones
```

---

## 🎨 Componentes de la Vista

### **1. Tarjetas KPI (8 cards)**

#### **Tarjeta 1: Tenants Activos**

```html
<div class="card border-left-success shadow">
	<div class="card-body">
		<div class="row no-gutters align-items-center">
			<div class="col mr-2">
				<div class="text-xs font-weight-bold text-success text-uppercase mb-1">
					Tenants Activos
				</div>
				<div
					class="h5 mb-0 font-weight-bold text-gray-800"
					id="kpi-tenants-activos"
				>
					--
				</div>
			</div>
			<div class="col-auto">
				<i class="fas fa-store fa-2x text-gray-300"></i>
			</div>
		</div>
	</div>
</div>
```

**IDs de elementos**:

- `#kpi-tenants-activos`: Número de tenants activos
- `#kpi-ingresos-mes`: Ingresos del mes actual
- `#kpi-suscripciones-activas`: Suscripciones activas
- `#kpi-pedidos-mes`: Pedidos del mes
- `#kpi-pagos-exitosos`: Pagos exitosos totales
- `#kpi-pagos-pendientes`: Pagos pendientes
- `#kpi-tasa-retencion`: Tasa de retención %
- `#kpi-ingreso-promedio`: Ingreso promedio por tenant

---

### **2. Gráfica de Ingresos (Chart.js)**

```html
<div class="card shadow mb-4">
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-primary">
			Ingresos de los Últimos 12 Meses
		</h6>
	</div>
	<div class="card-body">
		<div class="chart-area">
			<canvas id="ingresosChart"></canvas>
		</div>
	</div>
</div>
```

**Configuración Chart.js**:

```javascript
new Chart(ctx, {
	type: "line",
	data: {
		labels: meses, // ['Nov 2024', 'Dic 2024', ...]
		datasets: [
			{
				label: "Ingresos ($)",
				yAxisID: "y",
				data: ingresos,
				borderColor: "rgb(78, 115, 223)",
				backgroundColor: "rgba(78, 115, 223, 0.05)",
				fill: true,
				tension: 0.4,
			},
			{
				label: "Pagos Exitosos",
				yAxisID: "y1",
				data: pagos,
				borderColor: "rgb(28, 200, 138)",
				backgroundColor: "rgba(28, 200, 138, 0.05)",
				fill: true,
				tension: 0.4,
			},
		],
	},
	options: {
		responsive: true,
		interaction: {
			mode: "index",
			intersect: false,
		},
		scales: {
			y: {
				type: "linear",
				display: true,
				position: "left",
				title: {
					display: true,
					text: "Ingresos ($)",
				},
			},
			y1: {
				type: "linear",
				display: true,
				position: "right",
				title: {
					display: true,
					text: "Cantidad de Pagos",
				},
				grid: {
					drawOnChartArea: false,
				},
			},
		},
	},
});
```

**Características**:

- **Dual Y-Axis**: Ingresos (izquierda) y Cantidad (derecha)
- **Curvas suavizadas**: `tension: 0.4`
- **Áreas rellenas**: `fill: true` con transparencia
- **Responsive**: Se adapta al tamaño del contenedor
- **Tooltips**: Formateo personalizado de moneda

---

### **3. Planes Populares**

```html
<div class="card shadow mb-4">
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-primary">Planes Más Populares</h6>
	</div>
	<div class="card-body" id="planes-populares-container">
		<!-- Contenido dinámico -->
	</div>
</div>
```

**Renderizado JavaScript**:

```javascript
function renderPlanesPopulares(planes) {
	const container = $("#planes-populares-container");
	container.empty();

	planes.forEach((plan, index) => {
		const porcentaje = (plan.total_tenants / totalTenants) * 100;
		const colorClass =
			index === 0 ? "success" : index === 1 ? "info" : "warning";

		const html = `
            <h4 class="small font-weight-bold">
                ${plan.nombre} 
                <span class="float-right">${plan.total_tenants} tenants</span>
            </h4>
            <div class="progress mb-4">
                <div class="progress-bar bg-${colorClass}" 
                     style="width: ${porcentaje}%">
                </div>
            </div>
        `;
		container.append(html);
	});
}
```

---

### **4. Resumen de Tenants**

```html
<div class="card shadow mb-4">
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-primary">Resumen de Tenants</h6>
	</div>
	<div class="card-body">
		<h4 class="small font-weight-bold">
			Tenants Activos
			<span class="float-right" id="tenants-activos-pct">--</span>
		</h4>
		<div class="progress mb-4">
			<div class="progress-bar bg-success" id="tenants-activos-bar"></div>
		</div>

		<h4 class="small font-weight-bold">
			Tenants Suspendidos
			<span class="float-right" id="tenants-suspendidos-pct">--</span>
		</h4>
		<div class="progress mb-4">
			<div class="progress-bar bg-danger" id="tenants-suspendidos-bar"></div>
		</div>

		<p class="mb-1">
			<strong>Nuevos este mes:</strong>
			<span id="tenants-nuevos-mes">--</span>
		</p>
		<p class="mb-0">
			<strong>Nuevos esta semana:</strong>
			<span id="tenants-nuevos-semana">--</span>
		</p>
	</div>
</div>
```

---

### **5. Resumen de Ingresos**

```html
<div class="card shadow mb-4">
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-primary">Resumen de Ingresos</h6>
	</div>
	<div class="card-body">
		<p class="mb-2">
			<strong>Total Acumulado:</strong>
			<span class="text-success" id="ingresos-total">$--</span>
		</p>
		<p class="mb-2">
			<strong>Mes Actual:</strong>
			<span id="ingresos-mes-actual">$--</span>
		</p>
		<p class="mb-2">
			<strong>Mes Anterior:</strong>
			<span id="ingresos-mes-anterior">$--</span>
		</p>
		<p class="mb-2">
			<strong>Crecimiento:</strong>
			<span id="ingresos-crecimiento">--</span>
		</p>
		<hr />
		<p class="mb-2">
			<strong>Promedio Diario:</strong>
			<span id="ingresos-promedio-diario">$--</span>
		</p>
		<p class="mb-0">
			<strong>Proyección Mensual:</strong>
			<span class="text-info" id="ingresos-proyeccion">$--</span>
		</p>
	</div>
</div>
```

**Indicador de Crecimiento**:

```javascript
if (crecimiento >= 0) {
	html = `<i class="fas fa-arrow-up text-success"></i> +${crecimiento.toFixed(
		2
	)}%`;
} else {
	html = `<i class="fas fa-arrow-down text-danger"></i> ${crecimiento.toFixed(
		2
	)}%`;
}
$("#ingresos-crecimiento").html(html);
```

---

### **6. Resumen de Pedidos**

```html
<div class="card shadow mb-4">
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-primary">Resumen de Pedidos</h6>
	</div>
	<div class="card-body">
		<p class="mb-2">
			<strong>Total en el Sistema:</strong>
			<span id="pedidos-total">--</span>
		</p>
		<p class="mb-2">
			<strong>Mes Actual:</strong>
			<span id="pedidos-mes-actual">--</span>
		</p>
		<p class="mb-2">
			<strong>Última Semana:</strong>
			<span id="pedidos-ultima-semana">--</span>
		</p>
		<hr />
		<p class="mb-0">
			<strong>Promedio Diario:</strong>
			<span id="pedidos-promedio-diario">--</span> pedidos/día
		</p>
	</div>
</div>
```

---

## 🔧 Funciones JavaScript

### **fetchDashboardStats()**

Obtiene los datos del dashboard desde el endpoint AJAX.

```javascript
function fetchDashboardStats() {
	$.ajax({
		url: api.dashboard_stats,
		type: "GET",
		dataType: "json",
		success: function (response) {
			if (response.success) {
				renderDashboardStats(response.data);
			} else {
				Swal.fire("Error", response.message, "error");
			}
		},
		error: function (xhr) {
			console.error("Error al cargar dashboard:", xhr);
			Swal.fire("Error", "No se pudieron cargar las estadísticas", "error");
		},
	});
}
```

---

### **renderDashboardStats(stats)**

Función orquestadora que actualiza todos los componentes del dashboard.

```javascript
function renderDashboardStats(stats) {
	updateKPICards(stats);
	renderIngresosChart(stats.grafica_ingresos);
	renderPlanesPopulares(stats.planes_populares);
	updateResumenTenants(stats.tenants);
	updateResumenIngresos(stats.ingresos);
	updateResumenPedidos(stats.pedidos);
}
```

---

### **updateKPICards(stats)**

Actualiza las 8 tarjetas KPI con los datos recibidos.

```javascript
function updateKPICards(stats) {
	// Tenants Activos
	$("#kpi-tenants-activos").text(stats.tenants.activos);

	// Ingresos del Mes
	$("#kpi-ingresos-mes").text(
		"$" + parseFloat(stats.ingresos.mes_actual).toFixed(2)
	);

	// Suscripciones Activas
	$("#kpi-suscripciones-activas").text(stats.suscripciones.activas);

	// Pedidos del Mes
	$("#kpi-pedidos-mes").text(stats.pedidos.mes_actual);

	// Pagos Exitosos
	$("#kpi-pagos-exitosos").text(stats.pagos.exitoso);

	// Pagos Pendientes
	$("#kpi-pagos-pendientes").text(stats.pagos.pendiente);

	// Tasa de Retención
	const retencion = stats.metricas_generales.tasa_retencion;
	$("#kpi-tasa-retencion").text(retencion.toFixed(2) + "%");

	// Ingreso Promedio
	const promedio = stats.metricas_generales.ingreso_promedio_por_tenant;
	$("#kpi-ingreso-promedio").text("$" + parseFloat(promedio).toFixed(2));
}
```

---

### **renderIngresosChart(data)**

Renderiza la gráfica de ingresos con Chart.js (dual-axis).

```javascript
function renderIngresosChart(data) {
	const ctx = document.getElementById("ingresosChart").getContext("2d");

	// Destruir gráfica anterior si existe
	if (window.ingresosChartInstance) {
		window.ingresosChartInstance.destroy();
	}

	// Extraer datos
	const meses = data.map((item) => item.mes);
	const ingresos = data.map((item) => parseFloat(item.ingresos));
	const pagos = data.map((item) => parseInt(item.pagos_exitosos));

	// Crear nueva gráfica
	window.ingresosChartInstance = new Chart(ctx, {
		type: "line",
		data: {
			labels: meses,
			datasets: [
				{
					label: "Ingresos ($)",
					yAxisID: "y",
					data: ingresos,
					borderColor: "rgb(78, 115, 223)",
					backgroundColor: "rgba(78, 115, 223, 0.05)",
					borderWidth: 2,
					fill: true,
					tension: 0.4,
				},
				{
					label: "Pagos Exitosos",
					yAxisID: "y1",
					data: pagos,
					borderColor: "rgb(28, 200, 138)",
					backgroundColor: "rgba(28, 200, 138, 0.05)",
					borderWidth: 2,
					fill: true,
					tension: 0.4,
				},
			],
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			interaction: {
				mode: "index",
				intersect: false,
			},
			plugins: {
				legend: {
					display: true,
					position: "top",
				},
				tooltip: {
					callbacks: {
						label: function (context) {
							let label = context.dataset.label || "";
							if (label) {
								label += ": ";
							}
							if (context.parsed.y !== null) {
								if (context.datasetIndex === 0) {
									label += "$" + context.parsed.y.toFixed(2);
								} else {
									label += context.parsed.y;
								}
							}
							return label;
						},
					},
				},
			},
			scales: {
				y: {
					type: "linear",
					display: true,
					position: "left",
					title: {
						display: true,
						text: "Ingresos ($)",
					},
					ticks: {
						callback: function (value) {
							return "$" + value.toFixed(0);
						},
					},
				},
				y1: {
					type: "linear",
					display: true,
					position: "right",
					title: {
						display: true,
						text: "Cantidad de Pagos",
					},
					grid: {
						drawOnChartArea: false,
					},
				},
			},
		},
	});
}
```

---

### **renderPlanesPopulares(planes)**

Renderiza los planes más populares con barras de progreso.

```javascript
function renderPlanesPopulares(planes) {
	const container = $("#planes-populares-container");
	container.empty();

	if (!planes || planes.length === 0) {
		container.html('<p class="text-muted">No hay datos disponibles</p>');
		return;
	}

	const totalTenants = planes.reduce((sum, plan) => {
		return sum + parseInt(plan.total_tenants);
	}, 0);

	planes.forEach((plan, index) => {
		const porcentaje =
			totalTenants > 0
				? ((parseInt(plan.total_tenants) / totalTenants) * 100).toFixed(1)
				: 0;

		const colorClass =
			index === 0
				? "success"
				: index === 1
				? "info"
				: index === 2
				? "warning"
				: "secondary";

		const html = `
            <h4 class="small font-weight-bold">
                ${plan.nombre} 
                <span class="float-right">${plan.total_tenants} tenants</span>
            </h4>
            <div class="progress mb-4">
                <div class="progress-bar bg-${colorClass}" 
                     role="progressbar" 
                     style="width: ${porcentaje}%"
                     aria-valuenow="${porcentaje}" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                </div>
            </div>
        `;
		container.append(html);
	});
}
```

---

### **updateResumenTenants(tenants)**

Actualiza el resumen de tenants con barras de progreso.

```javascript
function updateResumenTenants(tenants) {
	const total = parseInt(tenants.total);
	const activos = parseInt(tenants.activos);
	const suspendidos = parseInt(tenants.suspendidos);

	// Calcular porcentajes
	const pctActivos = total > 0 ? ((activos / total) * 100).toFixed(1) : 0;
	const pctSuspendidos =
		total > 0 ? ((suspendidos / total) * 100).toFixed(1) : 0;

	// Actualizar textos
	$("#tenants-activos-pct").text(`${activos} (${pctActivos}%)`);
	$("#tenants-suspendidos-pct").text(`${suspendidos} (${pctSuspendidos}%)`);

	// Actualizar barras
	$("#tenants-activos-bar").css("width", pctActivos + "%");
	$("#tenants-suspendidos-bar").css("width", pctSuspendidos + "%");

	// Actualizar contadores
	$("#tenants-nuevos-mes").text(tenants.nuevos_mes);
	$("#tenants-nuevos-semana").text(tenants.nuevos_semana);
}
```

---

### **updateResumenIngresos(ingresos)**

Actualiza el resumen de ingresos con indicadores de crecimiento.

```javascript
function updateResumenIngresos(ingresos) {
	const total = parseFloat(ingresos.total);
	const mesActual = parseFloat(ingresos.mes_actual);
	const mesAnterior = parseFloat(ingresos.mes_anterior);
	const crecimiento = parseFloat(ingresos.crecimiento_porcentaje);
	const promedioDiario = parseFloat(ingresos.promedio_diario);

	// Actualizar montos
	$("#ingresos-total").text("$" + total.toFixed(2));
	$("#ingresos-mes-actual").text("$" + mesActual.toFixed(2));
	$("#ingresos-mes-anterior").text("$" + mesAnterior.toFixed(2));
	$("#ingresos-promedio-diario").text("$" + promedioDiario.toFixed(2));

	// Proyección (promedio diario × 30)
	const proyeccion = promedioDiario * 30;
	$("#ingresos-proyeccion").text("$" + proyeccion.toFixed(2));

	// Indicador de crecimiento
	let crecimientoHTML = "";
	if (crecimiento >= 0) {
		crecimientoHTML = `<i class="fas fa-arrow-up text-success"></i> +${crecimiento.toFixed(
			2
		)}%`;
	} else {
		crecimientoHTML = `<i class="fas fa-arrow-down text-danger"></i> ${crecimiento.toFixed(
			2
		)}%`;
	}
	$("#ingresos-crecimiento").html(crecimientoHTML);
}
```

---

### **updateResumenPedidos(pedidos)**

Actualiza el resumen de pedidos del sistema.

```javascript
function updateResumenPedidos(pedidos) {
	$("#pedidos-total").text(pedidos.total.toLocaleString());
	$("#pedidos-mes-actual").text(pedidos.mes_actual);
	$("#pedidos-ultima-semana").text(pedidos.ultima_semana);

	const promedio = parseFloat(pedidos.promedio_diario);
	$("#pedidos-promedio-diario").text(promedio.toFixed(1));
}
```

---

## 🎯 Casos de Uso

### **Caso 1: Monitoreo Diario del Negocio**

**Actor**: Administrador del Sistema

**Flujo**:

1. Administrador inicia sesión en el panel
2. Dashboard se carga automáticamente como página principal
3. Revisa las 8 tarjetas KPI para obtener vista general
4. Identifica métricas que requieren atención (ej: pagos pendientes altos)
5. Navega a la sección específica para tomar acción

**Resultado**: Administrador tiene visibilidad completa del estado del negocio en < 30 segundos

---

### **Caso 2: Análisis de Tendencias de Ingresos**

**Actor**: Director Financiero

**Flujo**:

1. Accede al dashboard
2. Revisa la gráfica de ingresos de 12 meses
3. Identifica patrones estacionales (ej: diciembre tiene pico de ingresos)
4. Compara ingresos con cantidad de pagos para detectar cambios en ticket promedio
5. Usa la proyección mensual para planificación financiera

**Resultado**: Decisiones financieras basadas en datos históricos y proyecciones

---

### **Caso 3: Evaluación de Popularidad de Planes**

**Actor**: Gerente de Producto

**Flujo**:

1. Accede al dashboard
2. Revisa la sección "Planes Más Populares"
3. Identifica que Plan Premium tiene 85 tenants (57% del total)
4. Plan Básico tiene 50 tenants (33% del total)
5. Decide enfocar marketing en upselling de Plan Básico a Premium

**Resultado**: Estrategia de producto optimizada basada en adopción real

---

### **Caso 4: Detección de Problemas de Retención**

**Actor**: Gerente de Éxito del Cliente

**Flujo**:

1. Accede al dashboard
2. Nota que tasa de retención bajó de 96% a 94.67%
3. Revisa que 8 tenants están suspendidos
4. Revisa sección de suscripciones y detecta 8 expirando pronto
5. Toma acción proactiva contactando esos tenants

**Resultado**: Prevención de churn mediante intervención temprana

---

### **Caso 5: Evaluación de Crecimiento**

**Actor**: CEO / Fundador

**Flujo**:

1. Accede al dashboard semanalmente
2. Revisa "Nuevos este mes" y "Nuevos esta semana"
3. Compara crecimiento mes actual (12 nuevos) vs mes anterior
4. Revisa indicador de crecimiento de ingresos (+9.25%)
5. Evalúa si se están cumpliendo objetivos de crecimiento

**Resultado**: Monitoreo efectivo de KPIs de crecimiento del negocio

---

## 🔐 Seguridad y Permisos

### **Autenticación Requerida**

- Todas las vistas y endpoints requieren sesión activa
- Validación mediante `AuthHook` en CodeIgniter
- Redirección automática a login si no está autenticado

### **Autorización de Rol**

- Solo usuarios con rol `admin` pueden acceder
- Validación en constructor de `Admin.php`:

```php
if ($this->session->userdata('role') !== 'admin') {
    redirect('admin/login');
}
```

### **Protección de Datos**

- No se exponen datos sensibles en frontend (contraseñas, tokens)
- Consultas SQL usan Query Builder (prevención de SQL injection)
- Sanitización de inputs en filtros

### **Rate Limiting**

- No implementado actualmente
- **Recomendación**: Agregar límite de requests para prevenir abuso

---

## 🚀 Optimizaciones de Performance

### **1. Consultas Optimizadas**

- Uso de índices en campos de fecha (`fecha_pago`, `fecha_registro`)
- Agregaciones en SQL en lugar de PHP
- LEFT JOIN solo cuando es necesario

### **2. Caché de Datos**

- **No implementado actualmente**
- **Recomendación**: Cachear stats por 5-15 minutos usando Memcached/Redis

### **3. Carga Asíncrona**

- AJAX para cargar datos sin bloquear la UI
- Chart.js se carga solo si el canvas existe

### **4. Gestión de Memoria**

- Destrucción de instancia de Chart.js antes de crear nueva
- Previene memory leaks en navegador

```javascript
if (window.ingresosChartInstance) {
	window.ingresosChartInstance.destroy();
}
```

---

## 📊 Métricas Calculadas

### **1. Tasa de Retención**

```php
$tasa_retencion = ($tenants_activos / $tenants_total) * 100;
```

**Interpretación**:

- > 95%: Excelente retención
- 90-95%: Buena retención
- 85-90%: Retención aceptable
- < 85%: Requiere atención inmediata

---

### **2. Ingreso Promedio por Tenant**

```php
$ingreso_promedio = $ingresos_mes_actual / $tenants_activos;
```

**Uso**:

- Benchmark para evaluar estrategias de pricing
- Identificar oportunidades de upselling
- Calcular LTV (Lifetime Value)

---

### **3. Crecimiento de Ingresos**

```php
$crecimiento = (($mes_actual - $mes_anterior) / $mes_anterior) * 100;
```

**Interpretación**:

- Positivo: Negocio en crecimiento
- Negativo: Requiere análisis de causa raíz
- Cerca de 0: Estancamiento, evaluar nuevas estrategias

---

### **4. Proyección Mensual**

```php
$proyeccion = $promedio_diario * 30;
```

**Uso**:

- Planificación financiera
- Proyecciones de flujo de caja
- Evaluación de objetivos mensuales

---

### **5. Promedio Diario de Pedidos**

```php
$promedio_diario = $pedidos_mes_actual / $dias_transcurridos_mes;
```

**Uso**:

- Identificar días pico
- Planificación de recursos (staff, inventario)
- Detectar anomalías en actividad

---

## 🎨 Códigos de Color

### **Tarjetas KPI**

- **Verde (`border-left-success`)**: Métricas positivas (tenants activos, pagos exitosos)
- **Azul (`border-left-primary`)**: Métricas financieras (ingresos)
- **Cyan (`border-left-info`)**: Métricas de suscripciones y retención
- **Amarillo (`border-left-warning`)**: Métricas que requieren monitoreo (pendientes)

### **Barras de Progreso**

- **success**: Primer lugar / Estado positivo
- **info**: Segundo lugar
- **warning**: Tercer lugar / Advertencia
- **danger**: Estado negativo (suspendidos, rechazados)
- **secondary**: Otros

### **Indicadores de Crecimiento**

- **text-success + arrow-up**: Crecimiento positivo
- **text-danger + arrow-down**: Decrecimiento

---

## 🧪 Testing

### **Test Manual Checklist**

#### **Carga Inicial**

- [ ] Dashboard carga sin errores de consola
- [ ] Todas las 8 tarjetas KPI muestran datos
- [ ] Gráfica de Chart.js se renderiza correctamente
- [ ] Planes populares se muestran con barras
- [ ] Los 3 resúmenes detallados tienen datos

#### **Datos Correctos**

- [ ] Total de tenants coincide con base de datos
- [ ] Ingresos del mes coinciden con suma de pagos exitosos
- [ ] Gráfica muestra 12 meses de datos
- [ ] Porcentajes de barras suman 100%
- [ ] Fechas de "nuevos este mes" son del mes actual

#### **Interactividad**

- [ ] Botón "Actualizar" recarga los datos
- [ ] Hover en gráfica muestra tooltips
- [ ] Responsive: Se adapta a móvil/tablet
- [ ] Navegación del sidebar funciona

#### **Performance**

- [ ] Carga inicial < 2 segundos
- [ ] No hay memory leaks al recargar múltiples veces
- [ ] Gráfica se actualiza sin flickering

---

### **Test Automatizado (Propuesta)**

```php
// tests/AdminDashboardTest.php

class AdminDashboardTest extends CITestCase {

    public function test_dashboard_requires_authentication() {
        $this->session->set_userdata('logged_in', false);
        $response = $this->get('admin/dashboard');
        $this->assertRedirect('admin/login');
    }

    public function test_dashboard_stats_returns_json() {
        $this->loginAsAdmin();
        $response = $this->ajax('GET', 'admin/dashboard_stats');
        $this->assertResponseCode(200);
        $this->assertIsJson($response);
    }

    public function test_dashboard_stats_has_all_sections() {
        $this->loginAsAdmin();
        $response = $this->ajax('GET', 'admin/dashboard_stats');
        $data = json_decode($response, true);

        $this->assertArrayHasKey('tenants', $data['data']);
        $this->assertArrayHasKey('planes', $data['data']);
        $this->assertArrayHasKey('ingresos', $data['data']);
        $this->assertArrayHasKey('pagos', $data['data']);
        $this->assertArrayHasKey('pedidos', $data['data']);
        $this->assertArrayHasKey('suscripciones', $data['data']);
        $this->assertArrayHasKey('grafica_ingresos', $data['data']);
    }

    public function test_grafica_ingresos_has_12_months() {
        $this->loginAsAdmin();
        $response = $this->ajax('GET', 'admin/dashboard_stats');
        $data = json_decode($response, true);

        $grafica = $data['data']['grafica_ingresos'];
        $this->assertCount(12, $grafica);
    }

    public function test_tasa_retencion_calculation() {
        $this->loginAsAdmin();
        $response = $this->ajax('GET', 'admin/dashboard_stats');
        $data = json_decode($response, true);

        $tenants = $data['data']['tenants'];
        $tasa = $data['data']['metricas_generales']['tasa_retencion'];

        $expected = ($tenants['activos'] / $tenants['total']) * 100;
        $this->assertEquals($expected, $tasa, 'Tasa de retención mal calculada', 0.01);
    }
}
```

---

## 🐛 Troubleshooting

### **Problema 1: Dashboard no carga datos**

**Síntomas**:

- Tarjetas muestran `--`
- Console muestra error 500

**Soluciones**:

1. Verificar que el usuario tiene rol `admin`:

```sql
SELECT role FROM users WHERE email = 'admin@example.com';
```

2. Verificar logs de PHP:

```bash
tail -f application/logs/log-2025-10-19.php
```

3. Verificar conexión a base de datos en `database.php`

---

### **Problema 2: Gráfica no se renderiza**

**Síntomas**:

- Canvas está vacío
- Console muestra "Cannot read property 'getContext' of null"

**Soluciones**:

1. Verificar que Chart.js CDN está cargando:

```javascript
console.log(typeof Chart); // Debe ser 'function'
```

2. Verificar que el elemento existe:

```javascript
console.log(document.getElementById("ingresosChart")); // No debe ser null
```

3. Verificar que hay datos:

```javascript
console.log(stats.grafica_ingresos); // Debe ser array con 12 elementos
```

---

### **Problema 3: Datos desactualizados**

**Síntomas**:

- Números no coinciden con realidad
- Cambios recientes no se reflejan

**Soluciones**:

1. Limpiar caché del navegador (Ctrl + Shift + R)
2. Verificar que no hay caché en servidor
3. Hacer clic en botón "Actualizar"

---

### **Problema 4: Porcentajes incorrectos**

**Síntomas**:

- Barras de progreso no suman 100%
- Porcentajes negativos o > 100%

**Soluciones**:

1. Verificar división por cero:

```javascript
const porcentaje = total > 0 ? (parte / total) * 100 : 0;
```

2. Verificar tipos de datos:

```javascript
const total = parseInt(tenants.total); // Asegurar que es número
```

---

### **Problema 5: Memory leak en gráfica**

**Síntomas**:

- Navegador se vuelve lento después de múltiples recargas
- Uso de RAM aumenta constantemente

**Soluciones**:

1. Verificar que se destruye instancia anterior:

```javascript
if (window.ingresosChartInstance) {
	window.ingresosChartInstance.destroy();
}
```

2. No crear múltiples gráficas en el mismo canvas

---

## 🔮 Mejoras Futuras

### **Prioridad Alta**

1. **Caché de estadísticas**: Implementar Redis/Memcached para reducir carga de DB
2. **Filtros de fecha**: Permitir seleccionar rango personalizado (7 días, 30 días, 90 días)
3. **Export a PDF**: Generar reporte del dashboard para compartir
4. **Alertas automáticas**: Notificar cuando métricas críticas caen

### **Prioridad Media**

5. **Comparación de períodos**: Mostrar mes actual vs mes anterior lado a lado
6. **Drill-down**: Click en KPI para ir a vista detallada
7. **Gráficas adicionales**: Pie chart de distribución de planes, bar chart de pedidos por estado
8. **WebSockets**: Actualización en tiempo real sin refresh

### **Prioridad Baja**

9. **Dashboards personalizados**: Permitir a admin elegir qué métricas ver
10. **Modo oscuro**: Toggle para tema oscuro
11. **Exportar data**: Descargar datos de gráfica como CSV
12. **Predicciones ML**: Usar machine learning para proyectar ingresos futuros

---

## 📚 Referencias

### **Documentación Externa**

- [Chart.js Official Docs](https://www.chartjs.org/docs/latest/)
- [Bootstrap 4 Components](https://getbootstrap.com/docs/4.6/components/card/)
- [CodeIgniter 3 User Guide](https://codeigniter.com/userguide3/index.html)
- [SB Admin 2 Theme](https://startbootstrap.com/theme/sb-admin-2)

### **Documentación Interna**

- `GESTION_TENANTS.md` - Gestión de tenants
- `GESTION_PLANES_SUSCRIPCIONES.md` - Planes y suscripciones
- `GESTION_PAGOS_FACTURACION.md` - Pagos y facturación
- `API_DOCUMENTATION.md` - Documentación general de APIs
- `permissions-auth.md` - Sistema de autenticación

---

## 📝 Changelog

### **Versión 1.0.0** (19 octubre 2025)

- ✅ Implementación inicial del dashboard
- ✅ 8 tarjetas KPI con métricas principales
- ✅ Gráfica dual de ingresos con Chart.js
- ✅ Top 5 planes populares
- ✅ 3 resúmenes detallados (Tenants, Ingresos, Pedidos)
- ✅ Botón de actualización manual
- ✅ 5 modelos extendidos con métodos de estadísticas
- ✅ Endpoint consolidado `dashboard_stats`
- ✅ Responsive design para móvil/tablet
- ✅ Indicadores de crecimiento con flechas y colores
- ✅ Documentación técnica completa

---

## 👥 Equipo de Desarrollo

**Backend Developer**: Implementación de modelos y controladores
**Frontend Developer**: Vistas y JavaScript con Chart.js
**DBA**: Optimización de queries y esquema de base de datos
**QA Engineer**: Testing manual y automatizado
**Product Owner**: Definición de KPIs y métricas de negocio

---

## 📧 Soporte

Para reportar bugs o solicitar mejoras:

- **Issues**: GitHub Issues del proyecto
- **Email**: soporte@imenu.com
- **Slack**: Canal #admin-dashboard

---

**Última actualización**: 19 de octubre de 2025
**Versión del documento**: 1.0.0
**Autor**: Equipo de Desarrollo iMenu
