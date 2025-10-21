<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
	<div>
		<small class="text-muted">Última actualización: <span id="last-update">--</span></small>
	</div>
</div>

<!-- Content Row - Estadísticas Principales -->
<div class="row">
	<!-- Pedidos de Hoy -->
	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-primary shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
							Pedidos de Hoy</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800" id="pedidos-hoy">
							<span class="spinner-border spinner-border-sm"></span>
						</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Ingresos de Hoy -->
	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-success shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-success text-uppercase mb-1">
							Ingresos de Hoy</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800" id="ingresos-hoy">
							<span class="spinner-border spinner-border-sm"></span>
						</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Productos Activos -->
	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-info shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-info text-uppercase mb-1">Productos Activos</div>
						<div class="row no-gutters align-items-center">
							<div class="col-auto">
								<div class="h5 mb-0 mr-3 font-weight-bold text-gray-800" id="productos-activos">
									<span class="spinner-border spinner-border-sm"></span>
								</div>
							</div>
							<div class="col">
								<small class="text-muted" id="productos-limite"></small>
							</div>
						</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-box fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Categorías -->
	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-warning shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
							Categorías</div>
						<div class="row no-gutters align-items-center">
							<div class="col-auto">
								<div class="h5 mb-0 mr-3 font-weight-bold text-gray-800" id="total-categorias">
									<span class="spinner-border spinner-border-sm"></span>
								</div>
							</div>
							<div class="col">
								<small class="text-muted" id="categorias-limite"></small>
							</div>
						</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-list fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Content Row - Plan y Suscripción -->
<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow">
			<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
				<h6 class="m-0 font-weight-bold text-primary">Estado de Plan y Suscripción</h6>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-4">
						<div class="text-center">
							<i class="fas fa-crown fa-3x text-warning mb-2"></i>
							<h5 class="font-weight-bold" id="plan-nombre">
								<span class="spinner-border spinner-border-sm"></span>
							</h5>
							<p class="text-muted">Plan Actual</p>
						</div>
					</div>
					<div class="col-md-4">
						<div class="text-center">
							<i class="fas fa-calendar-check fa-3x text-info mb-2"></i>
							<h5 class="font-weight-bold" id="dias-restantes">
								<span class="spinner-border spinner-border-sm"></span>
							</h5>
							<p class="text-muted">Días Restantes</p>
						</div>
					</div>
					<div class="col-md-4">
						<div class="text-center">
							<i class="fas fa-check-circle fa-3x text-success mb-2"></i>
							<h5 class="font-weight-bold">
								<span class="badge badge-pill" id="suscripcion-estado">
									<span class="spinner-border spinner-border-sm"></span>
								</span>
							</h5>
							<p class="text-muted">Estado Suscripción</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Content Row - Pedidos Recientes -->
<div class="row">
	<div class="col-lg-12">
		<div class="card shadow mb-4">
			<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
				<h6 class="m-0 font-weight-bold text-primary">Pedidos Recientes</h6>
				<a href="<?= base_url('app/pedidos') ?>" class="btn btn-sm btn-primary">
					Ver Todos <i class="fas fa-arrow-right ml-1"></i>
				</a>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-hover" id="pedidos-recientes-table">
						<thead>
							<tr>
								<th>ID</th>
								<th>Cliente</th>
								<th>Total</th>
								<th>Estado</th>
								<th>Fecha</th>
								<th>Acciones</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="6" class="text-center">
									<span class="spinner-border spinner-border-sm"></span> Cargando...
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	// Dashboard vanilla JS
	(function() {
		'use strict';

		const BASE = (typeof window.IMENU_BASE_URL !== 'undefined' && window.IMENU_BASE_URL) ?
			window.IMENU_BASE_URL :
			'/imenu/';

		function appUrl(path) {
			path = path || '';
			if (path.charAt(0) === '/') path = path.slice(1);
			return BASE + 'app/' + path;
		}

		function formatCurrency(amount) {
			return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
		}

		function formatDate(dateStr) {
			const d = new Date(dateStr);
			return d.toLocaleDateString('es-MX', {
				year: 'numeric',
				month: 'short',
				day: 'numeric',
				hour: '2-digit',
				minute: '2-digit'
			});
		}

		function getEstadoBadge(estado) {
			const badges = {
				'pendiente': 'badge-warning',
				'en_proceso': 'badge-info',
				'completado': 'badge-success',
				'cancelado': 'badge-danger'
			};
			const labels = {
				'pendiente': 'Pendiente',
				'en_proceso': 'En Proceso',
				'completado': 'Completado',
				'cancelado': 'Cancelado'
			};
			return '<span class="badge ' + (badges[estado] || 'badge-secondary') + '">' +
				(labels[estado] || estado) + '</span>';
		}

		function loadDashboard() {
			fetch(appUrl('dashboard_data'), {
					method: 'GET',
					headers: {
						'Content-Type': 'application/json'
					}
				})
				.then(res => res.json())
				.then(resp => {
					if (!resp || !resp.ok) {
						console.error('Error al cargar dashboard');
						return;
					}

					// Actualizar estadísticas
					document.getElementById('pedidos-hoy').textContent = resp.stats.pedidos_hoy;
					document.getElementById('ingresos-hoy').textContent = formatCurrency(resp.stats.ingresos_hoy);
					document.getElementById('productos-activos').textContent = resp.stats.productos_activos;
					document.getElementById('total-categorias').textContent = resp.stats.total_categorias;

					// Límites
					if (resp.plan.limites.productos.limite) {
						document.getElementById('productos-limite').textContent =
							'/ ' + resp.plan.limites.productos.limite + ' máx';
					}

					if (resp.plan.limites.categorias.limite) {
						document.getElementById('categorias-limite').textContent =
							'/ ' + resp.plan.limites.categorias.limite + ' máx';
					}

					// Plan info
					document.getElementById('plan-nombre').textContent = resp.plan.nombre;

					if (resp.plan.dias_restantes !== null) {
						const dias = resp.plan.dias_restantes;
						let color = 'text-success';
						if (dias <= 7) color = 'text-danger';
						else if (dias <= 15) color = 'text-warning';
						document.getElementById('dias-restantes').innerHTML =
							'<span class="' + color + '">' + dias + '</span>';
					} else {
						document.getElementById('dias-restantes').textContent = 'N/A';
					}

					// Estado suscripción
					const activo = resp.plan.suscripcion_activa;
					const estadoEl = document.getElementById('suscripcion-estado');
					estadoEl.className = 'badge badge-pill ' + (activo ? 'badge-success' : 'badge-danger');
					estadoEl.textContent = activo ? 'Activa' : 'Inactiva';

					// Pedidos recientes
					const tbody = document.querySelector('#pedidos-recientes-table tbody');
					let html = '';

					if (resp.pedidos_recientes && resp.pedidos_recientes.length > 0) {
						resp.pedidos_recientes.forEach(p => {
							html += '<tr>' +
								'<td>#' + p.id + '</td>' +
								'<td>' + (p.nombre_cliente || 'Sin nombre') + '</td>' +
								'<td>' + formatCurrency(p.total) + '</td>' +
								'<td>' + getEstadoBadge(p.estado) + '</td>' +
								'<td>' + formatDate(p.creado_en) + '</td>' +
								'<td>' +
								'<a href="' + appUrl('pedidos/' + p.id) + '" class="btn btn-sm btn-info">Ver</a>' +
								'</td>' +
								'</tr>';
						});
					} else {
						html = '<tr><td colspan="6" class="text-center text-muted">No hay pedidos recientes</td></tr>';
					}

					tbody.innerHTML = html;

					// Actualizar timestamp
					document.getElementById('last-update').textContent =
						new Date().toLocaleTimeString('es-MX');
				})
				.catch(err => {
					console.error('Error cargando dashboard:', err);
					document.getElementById('pedidos-hoy').innerHTML =
						'<span class="text-danger">Error</span>';
				});
		}

		// Cargar al iniciar
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', loadDashboard);
		} else {
			loadDashboard();
		}

		// Actualizar cada 60 segundos
		setInterval(loadDashboard, 60000);
	})();
</script>
