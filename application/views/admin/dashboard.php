<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
	<button id="btn-refresh-dashboard" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
		<i class="fas fa-sync-alt fa-sm text-white-50"></i> Actualizar
	</button>
</div>

<!-- KPIs Row 1: Tenants, Ingresos -->
<div class="row">
	<!-- Tenants Activos -->
	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-success shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-success text-uppercase mb-1">
							Tenants Activos</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800" id="kpi-tenants-activos">0</div>
						<div class="text-xs text-muted mt-1">
							<span id="kpi-tenants-total">0</span> totales
						</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-store fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Ingresos del Mes -->
	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-primary shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
							Ingresos del Mes</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800" id="kpi-ingresos-mes">$0.00</div>
						<div class="text-xs mt-1" id="kpi-crecimiento">
							<i class="fas fa-arrow-up"></i> 0%
						</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Suscripciones Activas -->
	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-info shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-info text-uppercase mb-1">
							Suscripciones Activas</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800" id="kpi-suscripciones-activas">0</div>
						<div class="text-xs text-warning mt-1">
							<i class="fas fa-exclamation-triangle"></i> <span id="kpi-suscripciones-expirando">0</span> expirando pronto
						</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-calendar-check fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Pedidos del Mes -->
	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-warning shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
							Pedidos del Mes</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800" id="kpi-pedidos-mes">0</div>
						<div class="text-xs text-muted mt-1">
							<span id="kpi-pedidos-total">0</span> totales
						</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- KPIs Row 2: Métricas adicionales -->
<div class="row">
	<!-- Pagos Exitosos -->
	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-success shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-success text-uppercase mb-1">
							Pagos Exitosos</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800" id="kpi-pagos-exitosos">0</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-check-circle fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Pagos Pendientes -->
	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-warning shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
							Pagos Pendientes</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800" id="kpi-pagos-pendientes">0</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-clock fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Tasa de Retención -->
	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-info shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-info text-uppercase mb-1">
							Tasa de Retención</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800" id="kpi-tasa-retencion">0%</div>
						<div class="text-xs text-muted mt-1">
							Tenants activos vs total
						</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-percentage fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Ingreso Promedio por Tenant -->
	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-primary shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
							Ingreso Promedio/Tenant</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800" id="kpi-ingreso-promedio">$0.00</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-chart-line fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Gráfica de Ingresos -->
<div class="row">
	<div class="col-xl-8 col-lg-7">
		<div class="card shadow mb-4">
			<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
				<h6 class="m-0 font-weight-bold text-primary">Ingresos Mensuales (Últimos 12 Meses)</h6>
			</div>
			<div class="card-body">
				<div class="chart-area">
					<canvas id="ingresosChart"></canvas>
				</div>
			</div>
		</div>
	</div>

	<!-- Planes Más Populares -->
	<div class="col-xl-4 col-lg-5">
		<div class="card shadow mb-4">
			<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
				<h6 class="m-0 font-weight-bold text-primary">Planes Más Populares</h6>
			</div>
			<div class="card-body">
				<div id="planes-populares-container">
					<!-- Contenido dinámico -->
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Estadísticas Detalladas -->
<div class="row">
	<!-- Resumen de Tenants -->
	<div class="col-xl-4 col-lg-5">
		<div class="card shadow mb-4">
			<div class="card-header py-3">
				<h6 class="m-0 font-weight-bold text-success">Resumen de Tenants</h6>
			</div>
			<div class="card-body">
				<div class="mb-3">
					<h4 class="small font-weight-bold">
						Tenants Activos
						<span class="float-right" id="tenants-activos-porcentaje">0%</span>
					</h4>
					<div class="progress mb-3">
						<div class="progress-bar bg-success" role="progressbar" id="tenants-activos-barra" style="width: 0%"></div>
					</div>
				</div>
				<div class="mb-3">
					<h4 class="small font-weight-bold">
						Tenants Suspendidos
						<span class="float-right" id="tenants-suspendidos-porcentaje">0%</span>
					</h4>
					<div class="progress mb-3">
						<div class="progress-bar bg-danger" role="progressbar" id="tenants-suspendidos-barra" style="width: 0%"></div>
					</div>
				</div>
				<div class="mt-4">
					<p class="text-muted small mb-1">
						<i class="fas fa-plus-circle text-success"></i>
						<strong id="tenants-nuevos-mes">0</strong> nuevos este mes
					</p>
					<p class="text-muted small mb-0">
						<i class="fas fa-calendar-week text-info"></i>
						<strong id="tenants-nuevos-semana">0</strong> últimos 7 días
					</p>
				</div>
			</div>
		</div>
	</div>

	<!-- Resumen de Ingresos -->
	<div class="col-xl-4 col-lg-5">
		<div class="card shadow mb-4">
			<div class="card-header py-3">
				<h6 class="m-0 font-weight-bold text-primary">Resumen de Ingresos</h6>
			</div>
			<div class="card-body">
				<p class="mb-2">
					<strong>Total acumulado:</strong><br>
					<span class="h4 text-success" id="ingresos-total">$0.00</span>
				</p>
				<hr>
				<p class="mb-2">
					<strong>Mes actual:</strong><br>
					<span class="h5" id="ingresos-mes-actual">$0.00</span>
				</p>
				<p class="mb-2">
					<strong>Mes anterior:</strong><br>
					<span class="h6 text-muted" id="ingresos-mes-anterior">$0.00</span>
				</p>
				<hr>
				<p class="mb-2">
					<strong>Promedio diario:</strong><br>
					<span class="text-info" id="ingresos-promedio-diario">$0.00</span>
				</p>
				<p class="mb-0">
					<strong>Proyección mensual:</strong><br>
					<span class="text-primary" id="ingresos-proyeccion">$0.00</span>
				</p>
			</div>
		</div>
	</div>

	<!-- Resumen de Pedidos -->
	<div class="col-xl-4 col-lg-5">
		<div class="card shadow mb-4">
			<div class="card-header py-3">
				<h6 class="m-0 font-weight-bold text-warning">Resumen de Pedidos</h6>
			</div>
			<div class="card-body">
				<p class="mb-2">
					<strong>Total del sistema:</strong><br>
					<span class="h4" id="pedidos-total-sistema">0</span>
				</p>
				<hr>
				<p class="mb-2">
					<strong>Este mes:</strong><br>
					<span class="h5" id="pedidos-mes-actual">0</span>
				</p>
				<p class="mb-2">
					<strong>Última semana:</strong><br>
					<span class="h6 text-muted" id="pedidos-ultima-semana">0</span>
				</p>
				<hr>
				<p class="mb-0">
					<strong>Promedio diario:</strong><br>
					<span class="text-info" id="pedidos-promedio-diario">0</span>
				</p>
			</div>
		</div>
	</div>
</div>

<!-- Incluir Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="<?php echo base_url('assets/js/admin.js?v=' . time()); ?>"></script>
