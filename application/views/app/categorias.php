<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-800">Categorías</h1>
	<button id="btn-new-cat" class="btn btn-sm btn-primary shadow-sm">
		<i class="fas fa-plus fa-sm text-white-50"></i> Nueva Categoría
	</button>
</div>

<!-- Modal Categoría -->
<div class="modal fade" id="modalCat" tabindex="-1" role="dialog" aria-labelledby="modalCatLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalCatLabel">Categoría</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="formCat">
					<input type="hidden" id="cat_id">
					<div class="form-group">
						<label for="cat_nombre">Nombre</label>
						<input type="text" class="form-control" id="cat_nombre" required>
					</div>
					<div class="form-group">
						<label for="cat_orden">Orden</label>
						<input type="number" class="form-control" id="cat_orden" required min="1">
					</div>
					<div class="form-group">
						<label for="cat_activo">Activo</label>
						<select class="form-control" id="cat_activo">
							<option value="1">Sí</option>
							<option value="0">No</option>
						</select>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
				<button type="button" class="btn btn-primary" id="btn-save-cat">Guardar</button>
			</div>
		</div>
	</div>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-primary">Lista de Categorías</h6>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" id="catTable" width="100%" cellspacing="0">
				<thead>
					<tr>
						<th>ID</th>
						<th>Nombre</th>
						<th>Orden</th>
						<th>Estado</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
</div>
<!-- Cargar JS del panel App -->
<script src="<?php echo base_url('assets/js/app.js?v=' . time()); ?>"></script>
