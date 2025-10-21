<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-800">Productos</h1>
	<a href="#" id="btn-new-product" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
		<i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Producto
	</a>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-primary">Lista de Productos</h6>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" id="products-table" width="100%" cellspacing="0">
				<thead>
					<tr>
						<th>ID</th>
						<th>Nombre</th>
						<th>Categoría</th>
						<th>Precio</th>
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

<!-- Modal: Crear/Editar Producto -->
<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="product-form">
				<input type="hidden" id="producto-id" name="id">
				<div class="modal-header">
					<h5 class="modal-title" id="product-modal-title">Nuevo Producto</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="nombre">Nombre <span class="text-danger">*</span></label>
						<input type="text" name="nombre" id="nombre" class="form-control" required />
					</div>
					<div class="form-group">
						<label for="descripcion">Descripción</label>
						<textarea name="descripcion" id="descripcion" class="form-control"></textarea>
					</div>
					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="precio">Precio <span class="text-danger">*</span></label>
							<input type="number" step="0.01" name="precio" id="precio" class="form-control" value="0.00" required />
						</div>
						<div class="form-group col-md-6">
							<label for="categoria_id">Categoría <span class="text-danger">*</span></label>
							<select name="categoria_id" id="categoria_id" class="form-control" required>
								<option value="">-- Seleccione --</option>
								<!-- Opciones cargadas por JS -->
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="product-image">Imagen del Producto</label>
						<div class="custom-file">
							<input type="file" class="custom-file-input" id="product-image" name="product_image" accept="image/jpeg, image/png, image/webp">
							<label class="custom-file-label" for="product-image">Elegir archivo...</label>
						</div>
						<img id="image-preview" src="" alt="Previsualización" class="img-thumbnail mt-2" style="display:none; max-height: 150px;" />
						<input type="hidden" name="img_url" id="img_url">
					</div>
					<div class="form-group">
						<label for="activo">Estado</label>
						<select name="activo" id="activo" class="form-control">
							<option value="1">Activo</option>
							<option value="0">Inactivo</option>
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
					<button type="submit" class="btn btn-primary" id="btn-save">Guardar</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Cargar JS unificado del panel App -->
<script src="<?php echo base_url('assets/js/app.js?v=' . time()); ?>"></script>
