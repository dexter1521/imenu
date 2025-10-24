/**
 * Módulo de Ajustes del Restaurante
 * Gestiona la configuración y personalización del menú
 */
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

	// === CSRF helpers (CodeIgniter 3) ===
	const CSRF_TOKEN_NAME = window.IMENU_CSRF_TOKEN_NAME || 'csrf_test_name';
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

	const api = {};

	const Ajustes = {
		/**
		 * Cargar ajustes desde el servidor
		 */
		load: function () {
			fetch(url('api/app/ajustes'), {
				method: 'GET',
				headers: {
					'Content-Type': 'application/json'
				}
			})
				.then(res => res.json())
				.then(resp => {
					console.log('Respuesta ajustes:', resp);

					// Mostrar formulario siempre
					document.getElementById('ajustes-loader').style.display = 'none';
					document.getElementById('form-ajustes').style.display = 'block';

					if (resp && resp.ok && resp.data) {
						Ajustes.populate(resp.data);
					} else {
						console.warn('No hay ajustes guardados o respuesta vacía');
					}
				})
				.catch(err => {
					console.error('Error cargando ajustes:', err);
					// Mostrar formulario de todos modos con valores por defecto
					document.getElementById('ajustes-loader').style.display = 'none';
					document.getElementById('form-ajustes').style.display = 'block';

					Swal.fire({
						icon: 'warning',
						title: 'Atención',
						text: 'No se pudieron cargar los ajustes guardados. Se mostrarán valores por defecto.',
						confirmButtonColor: '#3085d6'
					});
				});
		},

		/**
		 * Poblar formulario con datos
		 */
		populate: function (data) {
			// Información general
			if (data.nombre_negocio) document.getElementById('nombre_negocio').value = data.nombre_negocio;
			if (data.telefono) document.getElementById('telefono').value = data.telefono;
			if (data.email) document.getElementById('email').value = data.email;
			if (data.direccion) document.getElementById('direccion').value = data.direccion;

			// Visual
			if (data.color_primario) {
				document.getElementById('color_primario').value = data.color_primario;
				document.getElementById('color_primario_hex').value = data.color_primario;
			}

			document.getElementById('mostrar_precios').checked = data.mostrar_precios == 1;
			document.getElementById('mostrar_imagenes').checked = data.mostrar_imagenes == 1;
			document.getElementById('aceptar_pedidos').checked = data.aceptar_pedidos == 1;

			// Regional
			if (data.idioma) document.getElementById('idioma').value = data.idioma;
			if (data.moneda) document.getElementById('moneda').value = data.moneda;
			if (data.formato_precio) document.getElementById('formato_precio').value = data.formato_precio;
			if (data.zona_horaria) document.getElementById('zona_horaria').value = data.zona_horaria;

			// Mensajes
			if (data.mensaje_bienvenida) document.getElementById('mensaje_bienvenida').value = data.mensaje_bienvenida;
			if (data.notas_menu) document.getElementById('notas_menu').value = data.notas_menu;
			if (data.mensaje_pedido) document.getElementById('mensaje_pedido').value = data.mensaje_pedido;
			if (data.pie_menu) document.getElementById('pie_menu').value = data.pie_menu;

			// Horarios (si existen)
			const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
			dias.forEach(dia => {
				if (data[dia + '_abierto'] !== undefined) {
					document.getElementById(dia + '_abierto').checked = data[dia + '_abierto'] == 1;
				}
				if (data[dia + '_inicio']) document.getElementById(dia + '_inicio').value = data[dia + '_inicio'];
				if (data[dia + '_fin']) document.getElementById(dia + '_fin').value = data[dia + '_fin'];
			});

			// Actualizar vista previa de precio
			Ajustes.updatePrecioPreview();
		},

		/**
		 * Guardar ajustes en el servidor
		 */
		save: function (callback) {
			const formData = new FormData(document.getElementById('form-ajustes'));
			const data = {};

			// Convertir FormData a objeto
			for (const [key, value] of formData.entries()) {
				if (key.includes('_abierto')) {
					data[key] = document.getElementById(key).checked ? 1 : 0;
				} else {
					data[key] = value;
				}
			}

			// Agregar checkboxes no marcados
			data.mostrar_precios = document.getElementById('mostrar_precios').checked ? 1 : 0;
			data.mostrar_imagenes = document.getElementById('mostrar_imagenes').checked ? 1 : 0;
			data.aceptar_pedidos = document.getElementById('aceptar_pedidos').checked ? 1 : 0;

			// Agregar CSRF
			Object.assign(data, csrfData());

			fetch(url('api/app/ajustes'), {
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

		/**
		 * Actualizar vista previa del formato de precio
		 */
		updatePrecioPreview: function () {
			const formato = document.getElementById('formato_precio').value;
			const precio = 19.99;
			let preview = '';

			switch (formato) {
				case '$0.00':
					preview = '$' + precio.toFixed(2);
					break;
				case '$0':
					preview = '$' + Math.round(precio);
					break;
				case '0.00':
					preview = precio.toFixed(2);
					break;
				case '$ 0.00':
					preview = '$ ' + precio.toFixed(2);
					break;
				default:
					preview = '$' + precio.toFixed(2);
			}

			document.getElementById('precio-preview').textContent = preview;
		},

		/**
		 * Vincular eventos de UI
		 */
		bindUI: function () {
			// Color picker hex sincronizado
			document.getElementById('color_primario').addEventListener('change', function () {
				document.getElementById('color_primario_hex').value = this.value;
			});

			// Formato de precio preview
			document.getElementById('formato_precio').addEventListener('change', function () {
				Ajustes.updatePrecioPreview();
			});

			// Logo upload
			document.getElementById('logo').addEventListener('change', function (e) {
				const file = e.target.files[0];
				if (file) {
					// Validar tamaño (máx 2MB)
					if (file.size > 2 * 1024 * 1024) {
						Swal.fire({
							icon: 'error',
							title: 'Archivo muy grande',
							text: 'El logo debe ser menor a 2MB',
							confirmButtonColor: '#d33'
						});
						e.target.value = '';
						return;
					}

					// Validar tipo
					if (!file.type.match(/^image\/(jpeg|jpg|png|gif|webp)$/)) {
						Swal.fire({
							icon: 'error',
							title: 'Formato no válido',
							text: 'Solo se permiten imágenes (JPG, PNG, GIF, WEBP)',
							confirmButtonColor: '#d33'
						});
						e.target.value = '';
						return;
					}

					const reader = new FileReader();
					reader.onload = function (e) {
						const preview = document.getElementById('logo-preview');
						preview.querySelector('img').src = e.target.result;
						preview.style.display = 'block';
					};
					reader.readAsDataURL(file);
				}
			});

			// Remove logo
			document.getElementById('btn-remove-logo').addEventListener('click', function () {
				Swal.fire({
					title: '¿Eliminar logo?',
					text: 'Se quitará el logo actual',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#d33',
					cancelButtonColor: '#6c757d',
					confirmButtonText: 'Sí, eliminar',
					cancelButtonText: 'Cancelar'
				}).then((result) => {
					if (result.isConfirmed) {
						document.getElementById('logo').value = '';
						document.getElementById('logo-preview').style.display = 'none';
						// Aquí podrías también eliminar del servidor si es necesario
					}
				});
			});

			// Submit form
			document.getElementById('form-ajustes').addEventListener('submit', function (e) {
				e.preventDefault();

				const btnGuardar = document.getElementById('btn-guardar');
				const btnGuardarForm = e.target.querySelector('button[type="submit"]');

				btnGuardar.disabled = true;
				btnGuardarForm.disabled = true;
				btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

				Ajustes.save(function (err, resp) {
					btnGuardar.disabled = false;
					btnGuardarForm.disabled = false;
					btnGuardar.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';

					if (err || !resp || !resp.ok) {
						Swal.fire({
							icon: 'error',
							title: 'Error al guardar',
							text: resp ? resp.msg : 'Error de conexión',
							confirmButtonColor: '#d33'
						});
					} else {
						Swal.fire({
							icon: 'success',
							title: '¡Guardado!',
							text: 'Los ajustes se guardaron correctamente',
							timer: 2000,
							showConfirmButton: false
						});
					}
				});
			});

			// Botón guardar del header
			document.getElementById('btn-guardar').addEventListener('click', function () {
				document.getElementById('form-ajustes').dispatchEvent(new Event('submit'));
			});

			// Cancelar
			const btnCancelar = document.getElementById('btn-cancelar');
			if (btnCancelar) {
				btnCancelar.addEventListener('click', function () {
					Swal.fire({
						title: '¿Descartar cambios?',
						text: 'Se recargarán los valores guardados',
						icon: 'warning',
						showCancelButton: true,
						confirmButtonColor: '#3085d6',
						cancelButtonColor: '#d33',
						confirmButtonText: 'Sí, recargar',
						cancelButtonText: 'Cancelar'
					}).then((result) => {
						if (result.isConfirmed) {
							Ajustes.load();
						}
					});
				});
			}

			// Vista previa
			const btnVistaPrevia = document.getElementById('btn-vista-previa');
			if (btnVistaPrevia) {
				btnVistaPrevia.addEventListener('click', function () {
					Swal.fire({
						icon: 'info',
						title: 'Vista previa',
						text: 'Esta función estará disponible próximamente',
						confirmButtonColor: '#3085d6'
					});
				});
			}
		},

		/**
		 * Inicializar módulo
		 */
		init: function () {
			Ajustes.bindUI();
			Ajustes.load();
		}
	};

	// Inicializar cuando el DOM esté listo
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', Ajustes.init);
	} else {
		Ajustes.init();
	}

})();
