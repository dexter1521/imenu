<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-800">Gestión de Pagos</h1>
	<a href="#" id="btn-export-pagos" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
		<i class="fas fa-download fa-sm text-white-50"></i> Exportar Reporte
	</a>
</div>

<!-- Stats Row -->
<div class="row">
	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-success shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-success text-uppercase mb-1">
							Ingresos del Mes</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800" id="stat-ingresos-mes">$0.00</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-primary shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
							Pagos Procesados</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800" id="stat-pagos-exitosos">0</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-credit-card fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-warning shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
							Pagos Pendientes</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800" id="stat-pagos-pendientes">0</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-clock fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-danger shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
							Pagos Fallidos</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800" id="stat-pagos-fallidos">0</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-times-circle fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Filtros -->
<div class="card shadow mb-4">
	<div class="card-header py-3 d-flex justify-content-between align-items-center">
		<h6 class="m-0 font-weight-bold text-primary">Filtros de Búsqueda</h6>
		<button type="button" class="btn btn-sm btn-secondary" id="btn-limpiar-filtros">
			<i class="fas fa-eraser"></i> Limpiar
		</button>
	</div>
	<div class="card-body">
		<form class="row" id="filtros-form">
			<div class="col-md-3 mb-3">
				<label for="filtro-tenant">Tenant</label>
				<select class="form-control" id="filtro-tenant">
					<option value="">Todos los tenants</option>
					<!-- Opciones cargadas dinámicamente -->
				</select>
			</div>
			<div class="col-md-3 mb-3">
				<label for="filtro-estado">Estado</label>
				<select class="form-control" id="filtro-estado">
					<option value="">Todos los estados</option>
					<option value="pagado">Pagado</option>
					<option value="pendiente">Pendiente</option>
					<option value="fallido">Fallido</option>
				</select>
			</div>
			<div class="col-md-3 mb-3">
				<label for="filtro-metodo">Método de Pago</label>
				<select class="form-control" id="filtro-metodo">
					<option value="">Todos los métodos</option>
					<option value="tarjeta">Tarjeta</option>
					<option value="transferencia">Transferencia</option>
					<option value="paypal">PayPal</option>
					<option value="efectivo">Efectivo</option>
					<option value="otro">Otro</option>
				</select>
			</div>
			<div class="col-md-3 mb-3">
				<label for="filtro-concepto">Concepto</label>
				<input type="text" class="form-control" id="filtro-concepto" placeholder="Buscar...">
			</div>
			<div class="col-md-3 mb-3">
				<label for="filtro-fecha-inicio">Fecha Inicio</label>
				<input type="date" class="form-control" id="filtro-fecha-inicio">
			</div>
			<div class="col-md-3 mb-3">
				<label for="filtro-fecha-fin">Fecha Fin</label>
				<input type="date" class="form-control" id="filtro-fecha-fin">
			</div>
			<div class="col-md-6 mb-3 d-flex align-items-end">
				<button type="submit" class="btn btn-primary mr-2">
					<i class="fas fa-search"></i> Filtrar
				</button>
			</div>
		</form>
	</div>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-primary">Historial de Pagos</h6>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
				<thead>
					<tr>
						<th>ID</th>
						<th>Tenant</th>
						<th>Concepto</th>
						<th>Monto</th>
						<th>Método</th>
						<th>Referencia</th>
						<th>Estado</th>
						<th>Fecha</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody id="pagos-tbody">
					<!-- filas generadas dinámicamente por assets/js/admin.js -->
				</tbody>
			</table>
		</div>
	</div>
</div>

<!-- Modal: Detalles del Pago -->
<div class="modal fade" id="pagoDetalleModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Detalles del Pago #<span id="pago-detalle-id"></span></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<!-- Información del Pago -->
				<div class="card mb-3">
					<div class="card-header bg-primary text-white">
						<i class="fas fa-money-bill-wave"></i> Información del Pago
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-6">
								<p><strong>Concepto:</strong> <span id="pago-detalle-concepto"></span></p>
								<p><strong>Monto:</strong> <span id="pago-detalle-monto" class="text-success font-weight-bold"></span></p>
								<p><strong>Método:</strong> <span id="pago-detalle-metodo"></span></p>
								<p><strong>Referencia:</strong> <span id="pago-detalle-referencia"></span></p>
							</div>
							<div class="col-md-6">
								<p><strong>Estado:</strong> <span id="pago-detalle-estado"></span></p>
								<p><strong>Fecha:</strong> <span id="pago-detalle-fecha"></span></p>
								<p><strong>Notas:</strong> <span id="pago-detalle-notas"></span></p>
							</div>
						</div>
					</div>
				</div>

				<!-- Información del Tenant -->
				<div class="card mb-3">
					<div class="card-header bg-info text-white">
						<i class="fas fa-store"></i> Información del Tenant
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-6">
								<p><strong>Nombre:</strong> <span id="pago-detalle-tenant-nombre"></span></p>
								<p><strong>Email:</strong> <span id="pago-detalle-tenant-email"></span></p>
							</div>
							<div class="col-md-6">
								<p><strong>Slug:</strong> <span id="pago-detalle-tenant-slug"></span></p>
								<p><strong>Estado:</strong> <span id="pago-detalle-tenant-estado"></span></p>
							</div>
						</div>
					</div>
				</div>

				<!-- Información de la Suscripción -->
				<div class="card" id="pago-detalle-suscripcion-card" style="display: none;">
					<div class="card-header bg-warning text-white">
						<i class="fas fa-calendar-check"></i> Suscripción Asociada
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-6">
								<p><strong>Plan:</strong> <span id="pago-detalle-plan-nombre"></span></p>
								<p><strong>Precio:</strong> <span id="pago-detalle-plan-precio"></span></p>
							</div>
							<div class="col-md-6">
								<p><strong>Inicio:</strong> <span id="pago-detalle-suscripcion-inicio"></span></p>
								<p><strong>Fin:</strong> <span id="pago-detalle-suscripcion-fin"></span></p>
								<p><strong>Estado:</strong> <span id="pago-detalle-suscripcion-estado"></span></p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal: Exportar Pagos -->
<div class="modal fade" id="exportPagosModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="export-pagos-form">
				<div class="modal-header">
					<h5 class="modal-title">Exportar Pagos</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="alert alert-info">
						<i class="fas fa-info-circle"></i> Se exportarán los pagos según los filtros aplicados actualmente.
					</div>
					<div class="form-group">
						<label for="export-formato">Formato</label>
						<select id="export-formato" class="form-control" name="formato">
							<option value="csv">CSV (Excel compatible)</option>
							<option value="excel">Excel (.xls con formato)</option>
						</select>
					</div>
					<div class="form-group">
						<label for="export-fecha-inicio">Fecha inicio (opcional)</label>
						<input type="date" id="export-fecha-inicio" class="form-control" name="fecha_inicio">
					</div>
					<div class="form-group">
						<label for="export-fecha-fin">Fecha fin (opcional)</label>
						<input type="date" id="export-fecha-fin" class="form-control" name="fecha_fin">
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
					<button type="submit" class="btn btn-success">
						<i class="fas fa-download"></i> Exportar
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script src="<?php echo base_url('assets/js/admin.js?v=' . time()); ?>"></script>
