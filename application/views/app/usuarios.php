<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="container-fluid">
	<!-- Encabezado -->
	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1 class="h3 mb-0 text-gray-800">
			<i class="fas fa-users"></i> Gestión de Staff
		</h1>
		<button type="button" class="btn btn-primary" id="btn-invitar">
			<i class="fas fa-user-plus"></i> Invitar Usuario
		</button>
	</div>

	<!-- Tabla de Usuarios -->
	<div class="card shadow mb-4">
		<div class="card-header py-3 d-flex justify-content-between align-items-center">
			<h6 class="m-0 font-weight-bold text-primary">Lista de Usuarios</h6>
			<button type="button" class="btn btn-sm btn-secondary" id="btn-refresh">
				<i class="fas fa-sync-alt"></i> Actualizar
			</button>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered" id="usuarios-table">
					<thead>
						<tr>
							<th>ID</th>
							<th>Nombre</th>
							<th>Email</th>
							<th>Rol</th>
							<th>Estado</th>
							<th>Creado</th>
							<th>Acciones</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="7" class="text-center">
								<div class="spinner-border text-primary" role="status">
									<span class="sr-only">Cargando...</span>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

</div> <!-- /container-fluid -->

<!-- Modal Invitar Usuario -->
<div class="modal fade" id="invitarModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="form-invitar">
				<div class="modal-header">
					<h5 class="modal-title">
						<i class="fas fa-user-plus"></i> Invitar Nuevo Usuario
					</h5>
					<button type="button" class="close" data-dismiss="modal">
						<span>&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="inv-nombre">Nombre <span class="text-danger">*</span></label>
						<input type="text" class="form-control" id="inv-nombre" name="nombre" required>
					</div>
					<div class="form-group">
						<label for="inv-email">Email <span class="text-danger">*</span></label>
						<input type="email" class="form-control" id="inv-email" name="email" required>
						<small class="form-text text-muted">
							Se enviará una contraseña temporal a este correo
						</small>
					</div>

					<hr>

					<h6 class="font-weight-bold mb-3">
						<i class="fas fa-shield-alt"></i> Permisos Iniciales
					</h6>

					<div class="form-check mb-2">
						<input class="form-check-input" type="checkbox" id="perm-products" name="can_products" value="1">
						<label class="form-check-label" for="perm-products">
							<i class="fas fa-box"></i> Gestionar Productos
						</label>
						<small class="form-text text-muted">Crear, editar y eliminar productos del menú</small>
					</div>

					<div class="form-check mb-2">
						<input class="form-check-input" type="checkbox" id="perm-categories" name="can_categories" value="1">
						<label class="form-check-label" for="perm-categories">
							<i class="fas fa-tags"></i> Gestionar Categorías
						</label>
						<small class="form-text text-muted">Crear, editar y eliminar categorías</small>
					</div>

					<div class="form-check mb-2">
						<input class="form-check-input" type="checkbox" id="perm-adjustments" name="can_adjustments" value="1">
						<label class="form-check-label" for="perm-adjustments">
							<i class="fas fa-cogs"></i> Gestionar Ajustes
						</label>
						<small class="form-text text-muted">Modificar configuración del negocio</small>
					</div>

					<div class="form-check mb-2">
						<input class="form-check-input" type="checkbox" id="perm-stats" name="can_view_stats" value="1" checked>
						<label class="form-check-label" for="perm-stats">
							<i class="fas fa-chart-bar"></i> Ver Estadísticas
						</label>
						<small class="form-text text-muted">Acceso al dashboard y reportes</small>
					</div>

					<div class="alert alert-info mt-3 mb-0">
						<i class="fas fa-info-circle"></i> <strong>Nota:</strong> Los permisos pueden modificarse después desde la lista de usuarios.
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
					<button type="submit" class="btn btn-primary">
						<i class="fas fa-paper-plane"></i> Enviar Invitación
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Modal Editar Permisos -->
<div class="modal fade" id="permisosModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="form-permisos">
				<input type="hidden" id="perm-user-id">
				<div class="modal-header">
					<h5 class="modal-title">
						<i class="fas fa-shield-alt"></i> Permisos de <span id="perm-user-name">Usuario</span>
					</h5>
					<button type="button" class="close" data-dismiss="modal">
						<span>&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="alert alert-warning">
						<i class="fas fa-exclamation-triangle"></i> Los cambios se aplicarán inmediatamente
					</div>

					<div class="form-check mb-3">
						<input class="form-check-input" type="checkbox" id="edit-perm-products" name="can_products" value="1">
						<label class="form-check-label" for="edit-perm-products">
							<strong><i class="fas fa-box"></i> Gestionar Productos</strong>
						</label>
						<small class="form-text text-muted">Crear, editar y eliminar productos del menú</small>
					</div>

					<div class="form-check mb-3">
						<input class="form-check-input" type="checkbox" id="edit-perm-categories" name="can_categories" value="1">
						<label class="form-check-label" for="edit-perm-categories">
							<strong><i class="fas fa-tags"></i> Gestionar Categorías</strong>
						</label>
						<small class="form-text text-muted">Crear, editar y eliminar categorías</small>
					</div>

					<div class="form-check mb-3">
						<input class="form-check-input" type="checkbox" id="edit-perm-adjustments" name="can_adjustments" value="1">
						<label class="form-check-label" for="edit-perm-adjustments">
							<strong><i class="fas fa-cogs"></i> Gestionar Ajustes</strong>
						</label>
						<small class="form-text text-muted">Modificar configuración del negocio</small>
					</div>

					<div class="form-check mb-3">
						<input class="form-check-input" type="checkbox" id="edit-perm-stats" name="can_view_stats" value="1">
						<label class="form-check-label" for="edit-perm-stats">
							<strong><i class="fas fa-chart-bar"></i> Ver Estadísticas</strong>
						</label>
						<small class="form-text text-muted">Acceso al dashboard y reportes</small>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
					<button type="submit" class="btn btn-primary">
						<i class="fas fa-save"></i> Guardar Cambios
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script src="<?= base_url('assets/js/app-usuarios.js?v=' . time()); ?>"></script>

