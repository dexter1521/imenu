<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-800">Gestión de Planes</h1>
	<a href="#" id="btn-new-plan" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
		<i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Plan
	</a>
</div>

<!-- Planes Cards -->
<div class="row">
	<!-- Plan Free -->
	<div class="col-lg-4 mb-4">
		<div class="card border-left-info shadow h-100">
			<div class="card-header bg-info text-white">
				<h6 class="m-0 font-weight-bold">Plan Free</h6>
			</div>
			<div class="card-body">
				<div class="text-center">
					<h2 class="font-weight-bold">$0.00</h2>
					<p class="text-muted">por mes</p>
				</div>
				<ul class="list-unstyled">
					<li><i class="fas fa-check text-success"></i> 5 Categorías</li>
					<li><i class="fas fa-check text-success"></i> 50 Productos</li>
					<li><i class="fas fa-times text-danger"></i> Con publicidad</li>
					<li><i class="fas fa-check text-success"></i> Soporte básico</li>
				</ul>
				<div class="text-center">
					<a href="#" class="btn btn-outline-info">Editar</a>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal: Crear Plan -->
	<div class="modal fade" id="planModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form id="plan-form">
					<input type="hidden" name="id" id="plan-id">
					<div class="modal-header">
						<h5 class="modal-title">Crear Plan</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="plan-nombre">Nombre</label>
							<input type="text" class="form-control" id="plan-nombre" name="nombre" required>
						</div>
						<div class="form-group">
							<label for="plan-precio">Precio mensual</label>
							<input type="text" class="form-control" id="plan-precio" name="precio_mensual" value="0.00">
						</div>
						<div class="form-group">
							<label for="plan-cats">Límite categorías</label>
							<input type="number" class="form-control" id="plan-cats" name="limite_categorias" value="5">
						</div>
						<div class="form-group">
							<label for="plan-items">Límite productos</label>
							<input type="number" class="form-control" id="plan-items" name="limite_items" value="50">
						</div>
						<div class="form-group form-check">
							<input type="checkbox" class="form-check-input" id="plan-ads" name="ads">
							<label class="form-check-label" for="plan-ads">Incluir publicidad</label>
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

	<script src="<?php echo base_url('assets/js/admin.js?v=' . time()); ?>"></script>
	<!-- Plan Pro -->
	<div class="col-lg-4 mb-4">
		<div class="card border-left-success shadow h-100">
			<div class="card-header bg-success text-white">
				<h6 class="m-0 font-weight-bold">Plan Pro</h6>
			</div>
			<div class="card-body">
				<div class="text-center">
					<h2 class="font-weight-bold">$199.00</h2>
					<p class="text-muted">por mes</p>
				</div>
				<ul class="list-unstyled">
					<li><i class="fas fa-check text-success"></i> 20 Categorías</li>
					<li><i class="fas fa-check text-success"></i> 300 Productos</li>
					<li><i class="fas fa-check text-success"></i> Sin publicidad</li>
					<li><i class="fas fa-check text-success"></i> Soporte prioritario</li>
					<li><i class="fas fa-check text-success"></i> Análisis avanzados</li>
				</ul>
				<div class="text-center">
					<a href="#" class="btn btn-outline-success">Editar</a>
				</div>
			</div>
		</div>
	</div>

	<!-- Nuevo Plan -->
	<div class="col-lg-4 mb-4">
		<div class="card border-left-primary shadow h-100 text-center">
			<div class="card-body d-flex align-items-center justify-content-center">
				<div>
					<i class="fas fa-plus fa-3x text-primary mb-3"></i>
					<h5>Crear Nuevo Plan</h5>
					<a href="#" class="btn btn-primary">Agregar Plan</a>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Tabla de Planes -->
<div class="card shadow mb-4">
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-primary">Todos los Planes</h6>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
				<thead>
					<tr>
						<th>ID</th>
						<th>Nombre</th>
						<th>Precio Mensual</th>
						<th>Límite Categorías</th>
						<th>Límite Productos</th>
						<th>Publicidad</th>
						<th>Tenants Activos</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody id="planes-tbody">
					<!-- filas generadas dinámicamente por assets/js/admin.js -->
				</tbody>
			</table>
		</div>
	</div>
</div>
