<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-800">Gestión de Suscripciones</h1>
	<a href="#" id="btn-new-suscripcion" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
		<i class="fas fa-plus fa-sm text-white-50"></i> Nueva Suscripción
	</a>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
	<div class="col-xl-4 col-md-6 mb-4">
		<div class="card border-left-success shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-success text-uppercase mb-1">Suscripciones Activas</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800" id="stat-activas">-</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-check-circle fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-xl-4 col-md-6 mb-4">
		<div class="card border-left-warning shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Próximas a Vencer</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800" id="stat-vencer">-</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-xl-4 col-md-6 mb-4">
		<div class="card border-left-danger shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Expiradas</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800" id="stat-expiradas">-</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-times-circle fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Tabla de Suscripciones -->
<div class="card shadow mb-4">
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-primary">Todas las Suscripciones</h6>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
				<thead>
					<tr>
						<th>ID</th>
						<th>Tenant</th>
						<th>Plan</th>
						<th>Inicio</th>
						<th>Fin</th>
						<th>Estado</th>
						<th>Días Restantes</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody id="suscripciones-tbody">
					<!-- Filas generadas dinámicamente -->
				</tbody>
			</table>
		</div>
	</div>
</div>

<!-- Modal: Crear/Editar Suscripción -->
<div class="modal fade" id="suscripcionModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="suscripcion-form">
				<input type="hidden" id="suscripcion-id" name="id">
				<div class="modal-header">
					<h5 class="modal-title" id="suscripcion-modal-title">Crear Suscripción</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="suscripcion-tenant">Tenant <span class="text-danger">*</span></label>
						<select class="form-control" id="suscripcion-tenant" name="tenant_id" required>
							<option value="">-- Seleccione un tenant --</option>
						</select>
					</div>
					<div class="form-group">
						<label for="suscripcion-plan">Plan <span class="text-danger">*</span></label>
						<select class="form-control" id="suscripcion-plan" name="plan_id" required>
							<option value="">-- Seleccione un plan --</option>
						</select>
					</div>
					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="suscripcion-inicio">Fecha Inicio <span class="text-danger">*</span></label>
							<input type="date" class="form-control" id="suscripcion-inicio" name="inicio" required>
						</div>
						<div class="form-group col-md-6">
							<label for="suscripcion-fin">Fecha Fin <span class="text-danger">*</span></label>
							<input type="date" class="form-control" id="suscripcion-fin" name="fin" required>
						</div>
					</div>
					<div class="form-group">
						<label for="suscripcion-estatus">Estado</label>
						<select class="form-control" id="suscripcion-estatus" name="estatus">
							<option value="activa">Activa</option>
							<option value="pendiente">Pendiente</option>
							<option value="expirada">Expirada</option>
							<option value="cancelada">Cancelada</option>
						</select>
					</div>
					<div class="alert alert-info">
						<small><strong>Nota:</strong> La suscripción se asociará al tenant y plan seleccionados. Asegúrese de que las fechas sean correctas.</small>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
					<button type="submit" class="btn btn-primary">Guardar</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Modal: Histórico de Suscripciones por Tenant -->
<div class="modal fade" id="historicoModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Histórico de Suscripciones</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div id="historico-tenant-info" class="mb-3">
					<!-- Información del tenant -->
				</div>
				<div class="table-responsive">
					<table class="table table-sm table-bordered">
						<thead>
							<tr>
								<th>ID</th>
								<th>Plan</th>
								<th>Inicio</th>
								<th>Fin</th>
								<th>Estado</th>
								<th>Precio</th>
							</tr>
						</thead>
						<tbody id="historico-tbody">
							<!-- Filas generadas dinámicamente -->
						</tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url('assets/js/admin.js?v=' . time()); ?>"></script>
