<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
	<div>
		<button type="button" class="btn btn-sm btn-info mr-2" id="btn-ver-slug" title="Ver URL del menú público">
			<i class="fas fa-qrcode"></i> Ver URL Menú
		</button>
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

<script src="<?php echo base_url('assets/js/app-dashboard.js?v=' . time()); ?>"></script>
