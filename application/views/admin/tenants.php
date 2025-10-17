<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-800">Gestión de Tenants</h1>
	<a href="#" id="btn-new-tenant" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
		<i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Tenant
	</a>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-primary">Lista de Tenants</h6>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
				<thead>
					<tr>
						<th>ID</th>
						<th>Nombre</th>
						<th>Slug</th>
						<th>Plan</th>
						<th>Estado</th>
						<th>Fecha Creación</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody id="tenants-tbody">
					<!-- filas generadas dinámicamente por assets/js/admin.js -->
				</tbody>
			</table>
		</div>
	</div>
</div>

	<!-- Modal: Crear/Editar Tenant -->
	<div class="modal fade" id="tenantModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form id="tenant-form">
					<div class="modal-header">
						<h5 class="modal-title">Crear Tenant</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="tenant-nombre">Nombre</label>
							<input type="text" class="form-control" id="tenant-nombre" name="nombre" required>
						</div>
						<div class="form-group">
							<label for="tenant-slug">Slug (opcional)</label>
							<input type="text" class="form-control" id="tenant-slug" name="slug">
						</div>
						<div class="form-group">
							<label for="tenant-whatsapp">WhatsApp</label>
							<input type="text" class="form-control" id="tenant-whatsapp" name="whatsapp">
						</div>
						<div class="form-group form-check">
							<input type="checkbox" class="form-check-input" id="tenant-activo" name="activo" checked>
							<label class="form-check-label" for="tenant-activo">Activo</label>
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

	<script src="/assets/js/admin.js"></script>
<!-- Stats Cards -->
<div class="row">
	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-primary shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
							Total Tenants</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800">2</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-building fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-success shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-success text-uppercase mb-1">
							Tenants Activos</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800">2</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-check-circle fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-info shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-info text-uppercase mb-1">
							Ingresos Mensuales</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800">$199.00</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
							Planes Pro</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800">1</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-crown fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
