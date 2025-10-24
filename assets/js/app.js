// app.js - Lógica del panel del tenant (App) - Vanilla JS
(function () {
	'use strict';

	// Base URL del proyecto (definida en header) o fallback
	const BASE_URL = (typeof window.IMENU_BASE_URL !== 'undefined' && window.IMENU_BASE_URL)
		? window.IMENU_BASE_URL
		: '/imenu/';

	// Función auxiliar para construir URLs
	function url(path) {
		return BASE_URL + path;
	}

	const api = {
		categorias: url('CategoriasService/categorias'),
		categorias_create: url('CategoriasService/categoria_create'),
		categoria_update: (id) => url('CategoriasService/categoria_update/' + encodeURIComponent(id)),
		categoria_delete: (id) => url('CategoriasService/categoria_delete/' + encodeURIComponent(id)),
		productos: url('ProductosService/productos'),
		producto_create: url('ProductosService/producto_create'),
		producto_update: (id) => url('ProductosService/producto_update/' + encodeURIComponent(id)),
		producto_delete: (id) => url('ProductosService/producto_delete/' + encodeURIComponent(id)),
		productos_upload: url('ProductosService/producto_upload'),

	};

	// === CSRF helpers (CodeIgniter 3) ===
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

	/**
	 * Mostrar alerta con SweetAlert2 mejorado
	 * @param {string} title - Título
	 * @param {string} text - Mensaje
	 * @param {string} icon - Tipo: 'success', 'error', 'warning', 'info'
	 */
	function showAlert(title, text, icon) {
		if (window.Swal) {
			Swal.fire({ title, text, icon });
		} else {
			alert(title + ': ' + text);
		}
	}

	function showToast(title, icon) {
		if (window.Swal) {
			Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 1500 });
		}
	}

	// === Categorías ===
	const Categories = {
		load: function () {
			fetch(api.categorias, { method: 'GET', headers: { 'Content-Type': 'application/json' } })
				.then(res => res.json())
				.then(resp => {
					if (!resp || resp.ok !== true) return;
					let html = '';
					(resp.data || []).forEach(cat => {
						const isActive = cat.activo === '1' || cat.activo === 1;
						html += '<tr>' +
							'<td>' + cat.id + '</td>' +
							'<td>' + (cat.nombre || '') + '</td>' +
							'<td>' + (cat.orden || '') + '</td>' +
							'<td><span class="badge ' + (isActive ? 'badge-success' : 'badge-secondary') + '">' + (isActive ? 'Activo' : 'Inactivo') + '</span></td>' +
							'<td>' +
							'<button class="btn btn-sm btn-primary btn-edit" data-id="' + cat.id + '" data-nombre="' + (cat.nombre || '') + '" data-orden="' + (cat.orden || '') + '" data-activo="' + (cat.activo || '1') + '">Editar</button> ' +
							'<button class="btn btn-sm btn-danger btn-del" data-id="' + cat.id + '">Eliminar</button>' +
							'</td>' +
							'</tr>';
					});
					if (!html) html = '<tr><td colspan="5" class="text-center text-muted">No hay categorías</td></tr>';
					const tbody = document.querySelector('#catTable tbody');
					if (tbody) tbody.innerHTML = html;
				})
				.catch(err => console.error('Error cargando categorías', err));
		},

		save: function (payload, callback) {
			const id = payload.id;
			const data = Object.assign({}, {
				nombre: payload.nombre,
				orden: payload.orden,
				activo: payload.activo
			}, csrfData());

			const url = id ? api.categoria_update(id) : api.categorias_create;

			fetch(url, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: new URLSearchParams(data)
			}).then(res => res.json()).then(resp => {
				if (callback) callback(null, resp);
			}).catch(err => {
				if (callback) callback(err, null);
			});
		},

		remove: function (id, callback) {
			const data = csrfData();
			const params = new URLSearchParams(data);
			const url = api.categoria_delete(id) + '?' + params.toString();

			fetch(url, {
				method: 'DELETE',
				headers: { 'Content-Type': 'application/json' }
			}).then(res => res.json()).then(resp => {
				if (callback) callback(null, resp);
			}).catch(err => {
				if (callback) callback(err, null);
			});
		},

		bindUI: function () {
			const btnNew = document.querySelector('#btn-new-cat');
			if (btnNew) {
				btnNew.addEventListener('click', function () {
					const modal = document.querySelector('#modalCat');
					document.querySelector('#cat_id').value = '';
					document.querySelector('#cat_nombre').value = '';
					document.querySelector('#cat_orden').value = '';
					document.querySelector('#cat_activo').value = '1';
					// Abrir modal (compatible con Bootstrap 4 jQuery)
					if (window.jQuery && modal) {
						window.jQuery(modal).modal('show');
					}
				});
			}

			const btnSave = document.querySelector('#btn-save-cat');
			if (btnSave) {
				btnSave.addEventListener('click', function () {
					const id = document.querySelector('#cat_id').value;
					const nombre = (document.querySelector('#cat_nombre').value || '').trim();
					const orden = parseInt(document.querySelector('#cat_orden').value, 10);
					const activo = document.querySelector('#cat_activo').value;
					if (!nombre || isNaN(orden) || orden < 0) {
						showAlert('Error', 'Nombre y orden son obligatorios', 'error');
						return;
					}
					btnSave.disabled = true;
					btnSave.textContent = 'Guardando...';
					Categories.save({ id, nombre, orden, activo }, function (err, resp) {
						btnSave.disabled = false;
						btnSave.textContent = 'Guardar';

						if (err || !resp || (resp.ok !== true && resp.ok !== 'true')) {
							showAlert('Error', (resp && resp.msg) || 'Error al guardar', 'error');
						} else {
							// Cerrar modal (compatible con Bootstrap 4 jQuery)
							const modal = document.querySelector('#modalCat');
							if (window.jQuery && modal) {
								window.jQuery(modal).modal('hide');
							}
							showAlert('Guardado', 'Categoría guardada correctamente', 'success');
							Categories.load();
						}
					});
				});
			}

			const table = document.querySelector('#catTable');
			if (table) {
				table.addEventListener('click', function (e) {
					if (e.target.classList.contains('btn-edit')) {
						const btn = e.target;
						document.querySelector('#cat_id').value = btn.dataset.id;
						document.querySelector('#cat_nombre').value = btn.dataset.nombre;
						document.querySelector('#cat_orden').value = btn.dataset.orden;
						document.querySelector('#cat_activo').value = btn.dataset.activo;
						// Abrir modal (compatible con Bootstrap 4 jQuery)
						const modal = document.querySelector('#modalCat');
						if (window.jQuery && modal) {
							window.jQuery(modal).modal('show');
						}
					} else if (e.target.classList.contains('btn-del')) {
						const id = e.target.dataset.id;
						if (window.Swal) {
							Swal.fire({
								title: '¿Eliminar categoría?',
								text: 'Esta acción no se puede deshacer',
								icon: 'warning',
								showCancelButton: true,
								confirmButtonText: 'Sí, eliminar',
								cancelButtonText: 'Cancelar'
							}).then(function (result) {
								if (result.isConfirmed) {
									Categories.remove(id, function (err, resp) {
										if (err || !resp || (resp.ok !== true && resp.ok !== 'true')) {
											showAlert('Error', (resp && resp.msg) || 'No se pudo eliminar', 'error');
										} else {
											showAlert('Eliminado', 'Categoría eliminada', 'success');
											Categories.load();
										}
									});
								}
							});
						} else if (confirm('¿Eliminar categoría?')) {
							Categories.remove(id, function (err, resp) {
								if (err || !resp || (resp.ok !== true && resp.ok !== 'true')) {
									alert('No se pudo eliminar');
								} else {
									alert('Categoría eliminada');
									Categories.load();
								}
							});
						}
					}
				});
			}
		},

		init: function () {
			if (document.querySelector('#catTable')) {
				Categories.bindUI();
				Categories.load();
			}
		}
	};

	// === Productos ===
	const Products = {
		loadCategories: function (selectedId) {
			fetch(api.categorias, { method: 'GET', headers: { 'Content-Type': 'application/json' } })
				.then(res => res.json())
				.then(resp => {
					if (!resp || resp.ok !== true) return;
					let opts = '<option value="">-- Seleccione --</option>';
					(resp.data || []).forEach(c => {
						opts += '<option value="' + c.id + '"' + (selectedId && String(selectedId) === String(c.id) ? ' selected' : '') + '>' + (c.nombre || ('Cat ' + c.id)) + '</option>';
					});
					const select = document.querySelector('#categoria_id');
					if (select) select.innerHTML = opts;
				})
				.catch(err => console.error('Error cargando categorías', err));
		},

		load: function () {
			fetch(api.productos, { method: 'GET', headers: { 'Content-Type': 'application/json' } })
				.then(res => res.json())
				.then(resp => {
					if (!resp || resp.ok !== true) return;
					let html = '';
					(resp.data || []).forEach(p => {
						console.log(resp.data);
						const isActive = p.activo === '1' || p.activo === 1;
						html += '<tr>' +
							'<td>' + p.id + '</td>' +
							'<td>' + (p.nombre || '') + '</td>' +
							'<td>' + (p.categoria_nombre || '') + '</td>' +
							'<td>' + (p.precio || 0) + '</td>' +
							'<td><span class="badge ' + (isActive ? 'badge-success' : 'badge-secondary') + '">' + (isActive ? 'Activo' : 'Inactivo') + '</span></td>' +
							'<td>' +
							'<button class="btn btn-sm btn-primary btn-edit-product" ' +
							'data-id="' + p.id + '" ' +
							'data-nombre="' + (p.nombre || '') + '" ' +
							'data-descripcion="' + (p.descripcion || '') + '" ' +
							'data-precio="' + (p.precio || 0) + '" ' +
							'data-categoria_id="' + (p.categoria_id || '') + '" ' +
							'data-activo="' + (p.activo || 1) + '" ' +
							'data-img_url="' + (p.img_url || '') + '">Editar</button> ' +
							'<button class="btn btn-sm btn-danger btn-del-product" data-id="' + p.id + '">Eliminar</button>' +
							'</td>' +
							'</tr>';
					});
					if (!html) html = '<tr><td colspan="6" class="text-center text-muted">No hay productos</td></tr>';
					const tbody = document.querySelector('#products-table tbody');
					if (tbody) tbody.innerHTML = html;
				})
				.catch(err => console.error('Error cargando productos', err));
		},

		save: function (payload, callback) {
			const id = payload.id;
			const data = Object.assign({}, {
				nombre: payload.nombre,
				descripcion: payload.descripcion,
				precio: payload.precio,
				categoria_id: payload.categoria_id,
				img_url: payload.img_url,
				orden: payload.orden || 0,
				activo: payload.activo
			}, csrfData());
			const url = id ? api.producto_update(id) : api.producto_create;
			fetch(url, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: new URLSearchParams(data)
			}).then(res => res.json()).then(resp => {
				if (callback) callback(null, resp);
			}).catch(err => {
				if (callback) callback(err, null);
			});
		},

		remove: function (id, callback) {
			const data = csrfData();
			const params = new URLSearchParams(data);
			fetch(api.producto_delete(id) + '?' + params.toString(), {
				method: 'DELETE',
				headers: { 'Content-Type': 'application/json' }
			}).then(res => res.json()).then(resp => {
				if (callback) callback(null, resp);
			}).catch(err => {
				if (callback) callback(err, null);
			});
		},

		bindUI: function () {
			const btnNew = document.querySelector('#btn-new-product');
			if (btnNew) {
				btnNew.addEventListener('click', function (e) {
					e.preventDefault();
					document.querySelector('#producto-id').value = '';
					const form = document.querySelector('#product-form');
					if (form) form.reset();
					const preview = document.querySelector('#image-preview');
					if (preview) {
						preview.style.display = 'none';
						preview.src = '';
					}
					Products.loadCategories();
					// Abrir modal (compatible con Bootstrap 4 jQuery)
					const modal = document.querySelector('#productModal');
					if (window.jQuery && modal) {
						window.jQuery(modal).modal('show');
					}
				});
			}

			const form = document.querySelector('#product-form');
			if (form) {
				form.addEventListener('submit', function (e) {
					e.preventDefault();
					const id = document.querySelector('#producto-id').value;
					const nombre = (document.querySelector('#nombre').value || '').trim();
					const descripcion = document.querySelector('#descripcion').value || '';
					const precio = parseFloat(document.querySelector('#precio').value);
					const categoria_id = document.querySelector('#categoria_id').value;
					const activo = document.querySelector('#activo').value;
					const img_url = document.querySelector('#img_url').value;
					if (!nombre || isNaN(precio) || !categoria_id) {
						showAlert('Error', 'Nombre, precio y categoría son obligatorios', 'error');
						return;
					}
					const btnSave = document.querySelector('#btn-save');
					btnSave.disabled = true;
					btnSave.textContent = 'Guardando...';
					Products.save({ id, nombre, descripcion, precio, categoria_id, activo, img_url }, function (err, resp) {
						btnSave.disabled = false;
						btnSave.textContent = 'Guardar';

						if (err || !resp || (resp.ok !== true && resp.ok !== 'true')) {
							showAlert('Error', (resp && resp.msg) || 'Error al guardar', 'error');
						} else {
							// Cerrar modal (compatible con Bootstrap 4 jQuery)
							const modal = document.querySelector('#productModal');
							if (window.jQuery && modal) {
								window.jQuery(modal).modal('hide');
							}
							showAlert('Guardado', 'Producto guardado correctamente', 'success');
							Products.load();
						}
					});
				});
			}

			const table = document.querySelector('#products-table');
			if (table) {
				table.addEventListener('click', function (e) {
					if (e.target.classList.contains('btn-edit-product')) {
						const btn = e.target;
						const id = btn.dataset.id;
						document.querySelector('#producto-id').value = id;
						document.querySelector('#nombre').value = btn.dataset.nombre;
						document.querySelector('#descripcion').value = btn.dataset.descripcion;
						document.querySelector('#precio').value = btn.dataset.precio;
						document.querySelector('#activo').value = btn.dataset.activo;
						const url = btn.dataset.img_url;
						const preview = document.querySelector('#image-preview');
						if (url && preview) {
							preview.src = url;
							preview.style.display = 'block';
							document.querySelector('#img_url').value = url;
						} else if (preview) {
							preview.style.display = 'none';
						}
						Products.loadCategories(btn.dataset.categoria_id);
						// Abrir modal (compatible con Bootstrap 4 jQuery)
						const modal = document.querySelector('#productModal');
						if (window.jQuery && modal) {
							window.jQuery(modal).modal('show');
						}
					} else if (e.target.classList.contains('btn-del-product')) {
						const id = e.target.dataset.id;
						if (window.Swal) {
							Swal.fire({
								title: '¿Eliminar producto?',
								text: 'Esta acción no se puede deshacer',
								icon: 'warning',
								showCancelButton: true,
								confirmButtonText: 'Sí, eliminar',
								cancelButtonText: 'Cancelar'
							}).then(function (result) {
								if (result.isConfirmed) {
									Products.remove(id, function (err, resp) {
										if (err || !resp || (resp.ok !== true && resp.ok !== 'true')) {
											showAlert('Error', (resp && resp.msg) || 'No se pudo eliminar', 'error');
										} else {
											showAlert('Eliminado', 'Producto eliminado', 'success');
											Products.load();
										}
									});
								}
							});
						} else if (confirm('¿Eliminar producto?')) {
							Products.remove(id, function (err, resp) {
								if (err || !resp || (resp.ok !== true && resp.ok !== 'true')) {
									alert('No se pudo eliminar');
								} else {
									alert('Producto eliminado');
									Products.load();
								}
							});
						}
					}
				});
			}

			// Subida de imagen con vista previa
			const fileInput = document.querySelector('#product-image');
			if (fileInput) {
				fileInput.addEventListener('change', function () {
					const file = this.files && this.files[0];
					if (!file) return;
					const label = this.parentNode.querySelector('.custom-file-label');
					if (label) label.textContent = file.name;
					const fd = new FormData();
					fd.append('product_image', file);
					const csrf = csrfData();
					for (const k in csrf) {
						if (csrf.hasOwnProperty(k)) fd.append(k, csrf[k]);
					}
					fetch(appUrl('producto_upload'), {
						method: 'POST',
						body: fd
					})
						.then(res => res.json())
						.then(resp => {
							if (resp && resp.ok) {
								const preview = document.querySelector('#image-preview');
								if (preview) {
									preview.src = resp.url;
									preview.style.display = 'block';
								}
								document.querySelector('#img_url').value = resp.url;
								showToast('Imagen subida', 'success');
							} else {
								showAlert('Error', (resp && resp.msg) || 'No se pudo subir la imagen', 'error');
							}
						})
						.catch(err => {
							showAlert('Error', 'No se pudo subir la imagen', 'error');
						});
				});
			}
		},

		init: function () {
			if (document.querySelector('#products-table')) {
				Products.bindUI();
				Products.load();
			}
		}
	};

	// Inicialización automática al cargar el DOM
	function runInit() {
		Categories.init();
		Products.init();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', runInit);
	} else {
		runInit();
	}

	// Export por si se necesita invocar manualmente
	window.IMENU_APP = window.IMENU_APP || {};
	window.IMENU_APP.Categories = Categories;
	window.IMENU_APP.Products = Products;

})();
