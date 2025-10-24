(function () {
	'use strict';

	const BASE = (typeof window.IMENU_BASE_URL !== 'undefined' && window.IMENU_BASE_URL) ?
		window.IMENU_BASE_URL :
		'/imenu/';

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
		load: function () {
			fetch(appUrl('usuarios'), {
				method: 'GET',
				headers: {
					'Content-Type': 'application/json'
				}
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

		render: function (usuarios) {
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

		invitar: function (formData, callback) {
			const data = Object.assign({}, formData, csrfData());

			fetch(appUrl('usuario'), {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
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

		loadPermisos: function (userId, callback) {
			fetch(appUrl('usuario/' + userId + '/permisos'), {
				method: 'GET',
				headers: {
					'Content-Type': 'application/json'
				}
			})
				.then(res => res.json())
				.then(resp => {
					if (callback) callback(null, resp);
				})
				.catch(err => {
					if (callback) callback(err);
				});
		},

		updatePermisos: function (userId, permisos, callback) {
			const data = Object.assign({}, permisos, csrfData());

			fetch(appUrl('usuario/' + userId + '/permisos'), {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
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

		toggleActivo: function (userId, activo, callback) {
			const data = Object.assign({
				activo: activo
			}, csrfData());

			fetch(appUrl('usuario/' + userId), {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
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

		eliminar: function (userId, callback) {
			const data = csrfData();

			fetch(appUrl('usuario/' + userId), {
				method: 'DELETE',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
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

		bindUI: function () {
			// Botón invitar
			document.getElementById('btn-invitar').addEventListener('click', function () {
				document.getElementById('form-invitar').reset();
				const modal = document.getElementById('invitarModal');
				if (window.jQuery && window.jQuery(modal).modal) {
					window.jQuery(modal).modal('show');
				} else if (window.bootstrap) {
					new bootstrap.Modal(modal).show();
				}
			});

			// Submit invitación
			document.getElementById('form-invitar').addEventListener('submit', function (e) {
				e.preventDefault();

				const formData = {
					nombre: document.getElementById('inv-nombre').value,
					email: document.getElementById('inv-email').value,
					can_products: document.getElementById('perm-products').checked ? 1 : 0,
					can_categories: document.getElementById('perm-categories').checked ? 1 : 0,
					can_adjustments: document.getElementById('perm-adjustments').checked ? 1 : 0,
					can_view_stats: document.getElementById('perm-stats').checked ? 1 : 0
				};

				Staff.invitar(formData, function (err, resp) {
					if (err || !resp || !resp.ok) {
						if (window.Swal) {
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: (resp && resp.msg) || 'Error de conexión'
							});
						} else {
							alert('Error al invitar usuario: ' + (resp ? resp.msg : 'Error de conexión'));
						}
					} else {
						if (window.Swal) {
							Swal.fire({
								icon: 'success',
								title: 'Invitación Enviada',
								text: 'El usuario recibirá un email con sus credenciales.'
							});
						} else {
							alert('✓ Invitación enviada. El usuario recibirá un email con sus credenciales.');
						}
						const modal = document.getElementById('invitarModal');
						if (window.jQuery && window.jQuery(modal).modal) {
							window.jQuery(modal).modal('hide');
						}
						Staff.load();
					}
				});
			});

			// Refresh
			document.getElementById('btn-refresh').addEventListener('click', function () {
				Staff.load();
			});

			// Event delegation para botones de tabla
			document.querySelector('#usuarios-table').addEventListener('click', function (e) {
				// Permisos
				if (e.target.closest('.btn-permisos')) {
					const btn = e.target.closest('.btn-permisos');
					const userId = btn.dataset.id;
					const userName = btn.dataset.nombre;

					document.getElementById('perm-user-id').value = userId;
					document.getElementById('perm-user-name').textContent = userName;

					// Cargar permisos actuales
					Staff.loadPermisos(userId, function (err, resp) {
						if (err || !resp || !resp.ok) {
							if (window.Swal) {
								Swal.fire({
									icon: 'error',
									title: 'Error',
									text: 'No se pudieron cargar los permisos'
								});
							} else {
								alert('Error al cargar permisos');
							}
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

					if (window.Swal) {
						Swal.fire({
							title: '¿Activar usuario?',
							text: 'Este usuario podrá acceder al panel',
							icon: 'question',
							showCancelButton: true,
							confirmButtonText: 'Sí, activar',
							cancelButtonText: 'Cancelar'
						}).then(function (result) {
							if (result.isConfirmed) {
								Staff.toggleActivo(userId, 1, function (err, resp) {
									if (err || !resp || !resp.ok) {
										Swal.fire({
											icon: 'error',
											title: 'Error',
											text: 'No se pudo activar el usuario'
										});
									} else {
										Swal.fire({
											icon: 'success',
											title: 'Usuario Activado',
											text: 'El usuario puede acceder al panel',
											timer: 2000,
											showConfirmButton: false
										});
										Staff.load();
									}
								});
							}
						});
					} else if (confirm('¿Activar este usuario?')) {
						Staff.toggleActivo(userId, 1, function (err, resp) {
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

					if (window.Swal) {
						Swal.fire({
							title: '¿Desactivar usuario?',
							text: 'El usuario no podrá acceder al panel',
							icon: 'warning',
							showCancelButton: true,
							confirmButtonText: 'Sí, desactivar',
							cancelButtonText: 'Cancelar'
						}).then(function (result) {
							if (result.isConfirmed) {
								Staff.toggleActivo(userId, 0, function (err, resp) {
									if (err || !resp || !resp.ok) {
										Swal.fire({
											icon: 'error',
											title: 'Error',
											text: 'No se pudo desactivar el usuario'
										});
									} else {
										Swal.fire({
											icon: 'success',
											title: 'Usuario Desactivado',
											text: 'El usuario no puede acceder al panel',
											timer: 2000,
											showConfirmButton: false
										});
										Staff.load();
									}
								});
							}
						});
					} else if (confirm('¿Desactivar este usuario? No podrá acceder al panel.')) {
						Staff.toggleActivo(userId, 0, function (err, resp) {
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

					if (window.Swal) {
						Swal.fire({
							title: '¿ELIMINAR usuario?',
							text: 'Esta acción no se puede deshacer',
							icon: 'error',
							showCancelButton: true,
							confirmButtonColor: '#d33',
							confirmButtonText: 'Sí, eliminar',
							cancelButtonText: 'Cancelar'
						}).then(function (result) {
							if (result.isConfirmed) {
								Staff.eliminar(userId, function (err, resp) {
									if (err || !resp || !resp.ok) {
										Swal.fire({
											icon: 'error',
											title: 'Error',
											text: 'No se pudo eliminar el usuario'
										});
									} else {
										Swal.fire({
											icon: 'success',
											title: 'Usuario Eliminado',
											text: 'El usuario ha sido eliminado',
											timer: 2000,
											showConfirmButton: false
										});
										Staff.load();
									}
								});
							}
						});
					} else if (confirm('¿ELIMINAR este usuario permanentemente?')) {
						Staff.eliminar(userId, function (err, resp) {
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
			document.getElementById('form-permisos').addEventListener('submit', function (e) {
				e.preventDefault();

				const userId = document.getElementById('perm-user-id').value;
				const permisos = {
					can_products: document.getElementById('edit-perm-products').checked ? 1 : 0,
					can_categories: document.getElementById('edit-perm-categories').checked ? 1 : 0,
					can_adjustments: document.getElementById('edit-perm-adjustments').checked ? 1 : 0,
					can_view_stats: document.getElementById('edit-perm-stats').checked ? 1 : 0
				};

				Staff.updatePermisos(userId, permisos, function (err, resp) {
					if (err || !resp || !resp.ok) {
						if (window.Swal) {
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: 'No se pudieron actualizar los permisos'
							});
						} else {
							alert('Error al actualizar permisos');
						}
					} else {
						if (window.Swal) {
							Swal.fire({
								icon: 'success',
								title: 'Permisos Actualizados',
								text: 'Los cambios se han aplicado correctamente',
								timer: 2000,
								showConfirmButton: false
							});
						}
						const modal = document.getElementById('permisosModal');
						if (window.jQuery && window.jQuery(modal).modal) {
							window.jQuery(modal).modal('hide');
						}
						Staff.load();
					}
				});
			});
		},

		init: function () {
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
