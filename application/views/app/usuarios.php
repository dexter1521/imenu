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

<script src="<?= base_url('assets/js/app.js') ?>"></script>
<script>
// Staff/Usuarios - Vanilla JS
(function() {
	'use strict';

	const BASE = (typeof window.IMENU_BASE_URL !== 'undefined' && window.IMENU_BASE_URL)
		? window.IMENU_BASE_URL
		: '/imenu/';

	function appUrl(path) {
		path = path || '';
		if (path.charAt(0) === '/') path = path.slice(1);
		return BASE + 'api/app/' + path;
	}

	// CSRF helpers
	const CSRF_TOKEN_NAME = 'csrf_test_name';
	const CSRF_COOKIE_NAME = 'csrf_cookie_name';

	function getCookie(name) {
		const m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)'));
		return m ? decodeURIComponent(m[1]) : '';
	}

	function csrfData() {
		const o = {};
		o[CSRF_TOKEN_NAME] = getCookie(CSRF_COOKIE_NAME);
		return o;
	}

	function formatDate(dateStr) {
		if (!dateStr) return '--';
		const d = new Date(dateStr);
		return d.toLocaleDateString('es-MX', {
			year: 'numeric',
			month: 'short',
			day: 'numeric'
		});
	}

	function getRolBadge(rol) {
		if (rol === 'owner') {
			return '<span class="badge badge-danger"><i class="fas fa-crown"></i> Owner</span>';
		} else {
			return '<span class="badge badge-info"><i class="fas fa-user"></i> Staff</span>';
		}
	}

	function getEstadoBadge(activo) {
		if (activo == 1) {
			return '<span class="badge badge-success">Activo</span>';
		} else {
			return '<span class="badge badge-secondary">Inactivo</span>';
		}
	}

	const Staff = {
		load: function() {
			fetch(appUrl('usuarios'), {
				method: 'GET',
				headers: { 'Content-Type': 'application/json' }
			})
			.then(res => res.json())
			.then(resp => {
				if (!resp || !resp.ok) {
					console.error('Error al cargar usuarios');
					return;
				}

				Staff.render(resp.data);
			})
			.catch(err => {
				console.error('Error cargando usuarios:', err);
				const tbody = document.querySelector('#usuarios-table tbody');
				tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error al cargar usuarios</td></tr>';
			});
		},

		render: function(usuarios) {
			const tbody = document.querySelector('#usuarios-table tbody');
			let html = '';

			if (usuarios && usuarios.length > 0) {
				usuarios.forEach(u => {
					html += '<tr>' +
						'<td>' + u.id + '</td>' +
						'<td>' + (u.nombre || 'Sin nombre') + '</td>' +
						'<td>' + (u.email || '--') + '</td>' +
						'<td>' + getRolBadge(u.rol) + '</td>' +
						'<td>' + getEstadoBadge(u.activo) + '</td>' +
						'<td>' + formatDate(u.created_at) + '</td>' +
						'<td>';

					// Solo mostrar acciones si no es owner
					if (u.rol !== 'owner') {
						html += '<button class="btn btn-sm btn-info btn-permisos" data-id="' + u.id + '" data-nombre="' + (u.nombre || u.email) + '">' +
							'<i class="fas fa-shield-alt"></i> Permisos' +
							'</button> ';

						if (u.activo == 1) {
							html += '<button class="btn btn-sm btn-warning btn-desactivar" data-id="' + u.id + '">' +
								'<i class="fas fa-ban"></i> Desactivar' +
								'</button> ';
						} else {
							html += '<button class="btn btn-sm btn-success btn-activar" data-id="' + u.id + '">' +
								'<i class="fas fa-check"></i> Activar' +
								'</button> ';
						}

						html += '<button class="btn btn-sm btn-danger btn-eliminar" data-id="' + u.id + '">' +
							'<i class="fas fa-trash"></i>' +
							'</button>';
					} else {
						html += '<span class="text-muted">--</span>';
					}

					html += '</td></tr>';
				});
			} else {
				html = '<tr><td colspan="7" class="text-center text-muted">No hay usuarios</td></tr>';
			}

			tbody.innerHTML = html;
		},

		invitar: function(formData, callback) {
			const data = Object.assign({}, formData, csrfData());

			fetch(appUrl('usuario'), {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: new URLSearchParams(data)
			})
			.then(res => res.json())
			.then(resp => {
				if (callback) callback(null, resp);
			})
			.catch(err => {
				if (callback) callback(err);
			});
		},

		loadPermisos: function(userId, callback) {
			fetch(appUrl('usuario/' + userId + '/permisos'), {
				method: 'GET',
				headers: { 'Content-Type': 'application/json' }
			})
			.then(res => res.json())
			.then(resp => {
				if (callback) callback(null, resp);
			})
			.catch(err => {
				if (callback) callback(err);
			});
		},

		updatePermisos: function(userId, permisos, callback) {
			const data = Object.assign({}, permisos, csrfData());

			fetch(appUrl('usuario/' + userId + '/permisos'), {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: new URLSearchParams(data)
			})
			.then(res => res.json())
			.then(resp => {
				if (callback) callback(null, resp);
			})
			.catch(err => {
				if (callback) callback(err);
			});
		},

		toggleActivo: function(userId, activo, callback) {
			const data = Object.assign({ activo: activo }, csrfData());

			fetch(appUrl('usuario/' + userId), {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: new URLSearchParams(data)
			})
			.then(res => res.json())
			.then(resp => {
				if (callback) callback(null, resp);
			})
			.catch(err => {
				if (callback) callback(err);
			});
		},

		eliminar: function(userId, callback) {
			const data = csrfData();

			fetch(appUrl('usuario/' + userId), {
				method: 'DELETE',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: new URLSearchParams(data)
			})
			.then(res => res.json())
			.then(resp => {
				if (callback) callback(null, resp);
			})
			.catch(err => {
				if (callback) callback(err);
			});
		},

		bindUI: function() {
			// Botón invitar
			document.getElementById('btn-invitar').addEventListener('click', function() {
				document.getElementById('form-invitar').reset();
				const modal = document.getElementById('invitarModal');
				if (window.jQuery && window.jQuery(modal).modal) {
					window.jQuery(modal).modal('show');
				} else if (window.bootstrap) {
					new bootstrap.Modal(modal).show();
				}
			});

			// Submit invitación
			document.getElementById('form-invitar').addEventListener('submit', function(e) {
				e.preventDefault();

				const formData = {
					nombre: document.getElementById('inv-nombre').value,
					email: document.getElementById('inv-email').value,
					can_products: document.getElementById('perm-products').checked ? 1 : 0,
					can_categories: document.getElementById('perm-categories').checked ? 1 : 0,
					can_adjustments: document.getElementById('perm-adjustments').checked ? 1 : 0,
					can_view_stats: document.getElementById('perm-stats').checked ? 1 : 0
				};

				Staff.invitar(formData, function(err, resp) {
					if (err || !resp || !resp.ok) {
						alert('Error al invitar usuario: ' + (resp ? resp.msg : 'Error de conexión'));
					} else {
						alert('✓ Invitación enviada. El usuario recibirá un email con sus credenciales.');
						const modal = document.getElementById('invitarModal');
						if (window.jQuery && window.jQuery(modal).modal) {
							window.jQuery(modal).modal('hide');
						}
						Staff.load();
					}
				});
			});

			// Refresh
			document.getElementById('btn-refresh').addEventListener('click', function() {
				Staff.load();
			});

			// Event delegation para botones de tabla
			document.querySelector('#usuarios-table').addEventListener('click', function(e) {
				// Permisos
				if (e.target.closest('.btn-permisos')) {
					const btn = e.target.closest('.btn-permisos');
					const userId = btn.dataset.id;
					const userName = btn.dataset.nombre;

					document.getElementById('perm-user-id').value = userId;
					document.getElementById('perm-user-name').textContent = userName;

					// Cargar permisos actuales
					Staff.loadPermisos(userId, function(err, resp) {
						if (err || !resp || !resp.ok) {
							alert('Error al cargar permisos');
							return;
						}

						const perms = resp.data || {};
						document.getElementById('edit-perm-products').checked = perms.can_products == 1;
						document.getElementById('edit-perm-categories').checked = perms.can_categories == 1;
						document.getElementById('edit-perm-adjustments').checked = perms.can_adjustments == 1;
						document.getElementById('edit-perm-stats').checked = perms.can_view_stats == 1;

						const modal = document.getElementById('permisosModal');
						if (window.jQuery && window.jQuery(modal).modal) {
							window.jQuery(modal).modal('show');
						} else if (window.bootstrap) {
							new bootstrap.Modal(modal).show();
						}
					});
				}
				// Activar
				else if (e.target.closest('.btn-activar')) {
					const btn = e.target.closest('.btn-activar');
					const userId = btn.dataset.id;

					if (confirm('¿Activar este usuario?')) {
						Staff.toggleActivo(userId, 1, function(err, resp) {
							if (err || !resp || !resp.ok) {
								alert('Error al activar usuario');
							} else {
								Staff.load();
							}
						});
					}
				}
				// Desactivar
				else if (e.target.closest('.btn-desactivar')) {
					const btn = e.target.closest('.btn-desactivar');
					const userId = btn.dataset.id;

					if (confirm('¿Desactivar este usuario? No podrá acceder al panel.')) {
						Staff.toggleActivo(userId, 0, function(err, resp) {
							if (err || !resp || !resp.ok) {
								alert('Error al desactivar usuario');
							} else {
								Staff.load();
							}
						});
					}
				}
				// Eliminar
				else if (e.target.closest('.btn-eliminar')) {
					const btn = e.target.closest('.btn-eliminar');
					const userId = btn.dataset.id;

					if (confirm('¿ELIMINAR este usuario permanentemente?')) {
						Staff.eliminar(userId, function(err, resp) {
							if (err || !resp || !resp.ok) {
								alert('Error al eliminar usuario');
							} else {
								Staff.load();
							}
						});
					}
				}
			});

			// Submit permisos
			document.getElementById('form-permisos').addEventListener('submit', function(e) {
				e.preventDefault();

				const userId = document.getElementById('perm-user-id').value;
				const permisos = {
					can_products: document.getElementById('edit-perm-products').checked ? 1 : 0,
					can_categories: document.getElementById('edit-perm-categories').checked ? 1 : 0,
					can_adjustments: document.getElementById('edit-perm-adjustments').checked ? 1 : 0,
					can_view_stats: document.getElementById('edit-perm-stats').checked ? 1 : 0
				};

				Staff.updatePermisos(userId, permisos, function(err, resp) {
					if (err || !resp || !resp.ok) {
						alert('Error al actualizar permisos');
					} else {
						const modal = document.getElementById('permisosModal');
						if (window.jQuery && window.jQuery(modal).modal) {
							window.jQuery(modal).modal('hide');
						}
						Staff.load();
					}
				});
			});
		},

		init: function() {
			Staff.bindUI();
			Staff.load();
		}
	};

	// Inicializar
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', Staff.init);
	} else {
		Staff.init();
	}

})();
</script>
