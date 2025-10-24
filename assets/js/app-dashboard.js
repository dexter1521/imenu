(function () {
	'use strict';

	const BASE = (typeof window.IMENU_BASE_URL !== 'undefined' && window.IMENU_BASE_URL) ?
		window.IMENU_BASE_URL :
		'/imenu/';

	function appUrl(path) {
		path = path || '';
		if (path.charAt(0) === '/') path = path.slice(1);
		return BASE + 'app/' + path;
	}

	function formatCurrency(amount) {
		return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
	}

	function formatDate(dateStr) {
		const d = new Date(dateStr);
		return d.toLocaleDateString('es-MX', {
			year: 'numeric',
			month: 'short',
			day: 'numeric',
			hour: '2-digit',
			minute: '2-digit'
		});
	}

	function getEstadoBadge(estado) {
		const badges = {
			'pendiente': 'badge-warning',
			'en_proceso': 'badge-info',
			'completado': 'badge-success',
			'cancelado': 'badge-danger'
		};
		const labels = {
			'pendiente': 'Pendiente',
			'en_proceso': 'En Proceso',
			'completado': 'Completado',
			'cancelado': 'Cancelado'
		};
		return '<span class="badge ' + (badges[estado] || 'badge-secondary') + '">' +
			(labels[estado] || estado) + '</span>';
	}

	function loadDashboard() {
		fetch(appUrl('dashboard_data'), {
			method: 'GET',
			headers: {
				'Content-Type': 'application/json'
			}
		})
			.then(res => res.json())
			.then(resp => {
				if (!resp || !resp.ok) {
					console.error('Error al cargar dashboard');
					return;
				}

				// Actualizar estadísticas
				document.getElementById('pedidos-hoy').textContent = resp.stats.pedidos_hoy;
				document.getElementById('ingresos-hoy').textContent = formatCurrency(resp.stats.ingresos_hoy);
				document.getElementById('productos-activos').textContent = resp.stats.productos_activos;
				document.getElementById('total-categorias').textContent = resp.stats.total_categorias;

				// Límites
				if (resp.plan.limites.productos.limite) {
					document.getElementById('productos-limite').textContent =
						'/ ' + resp.plan.limites.productos.limite + ' máx';
				}

				if (resp.plan.limites.categorias.limite) {
					document.getElementById('categorias-limite').textContent =
						'/ ' + resp.plan.limites.categorias.limite + ' máx';
				}

				// Plan info
				document.getElementById('plan-nombre').textContent = resp.plan.nombre;

				if (resp.plan.dias_restantes !== null) {
					const dias = resp.plan.dias_restantes;
					let color = 'text-success';
					if (dias <= 7) color = 'text-danger';
					else if (dias <= 15) color = 'text-warning';
					document.getElementById('dias-restantes').innerHTML =
						'<span class="' + color + '">' + dias + '</span>';
				} else {
					document.getElementById('dias-restantes').textContent = 'N/A';
				}

				// Estado suscripción
				const activo = resp.plan.suscripcion_activa;
				const estadoEl = document.getElementById('suscripcion-estado');
				estadoEl.className = 'badge badge-pill ' + (activo ? 'badge-success' : 'badge-danger');
				estadoEl.textContent = activo ? 'Activa' : 'Inactiva';

				// Pedidos recientes
				const tbody = document.querySelector('#pedidos-recientes-table tbody');
				let html = '';

				if (resp.pedidos_recientes && resp.pedidos_recientes.length > 0) {
					resp.pedidos_recientes.forEach(p => {
						html += '<tr>' +
							'<td>#' + p.id + '</td>' +
							'<td>' + (p.nombre_cliente || 'Sin nombre') + '</td>' +
							'<td>' + formatCurrency(p.total) + '</td>' +
							'<td>' + getEstadoBadge(p.estado) + '</td>' +
							'<td>' + formatDate(p.creado_en) + '</td>' +
							'<td>' +
							'<a href="' + appUrl('pedidos/' + p.id) + '" class="btn btn-sm btn-info">Ver</a>' +
							'</td>' +
							'</tr>';
					});
				} else {
					html = '<tr><td colspan="6" class="text-center text-muted">No hay pedidos recientes</td></tr>';
				}

				tbody.innerHTML = html;

				// Actualizar timestamp
				document.getElementById('last-update').textContent =
					new Date().toLocaleTimeString('es-MX');
			})
			.catch(err => {
				console.error('Error cargando dashboard:', err);
				document.getElementById('pedidos-hoy').innerHTML =
					'<span class="text-danger">Error</span>';
			});
	}

	// Cargar al iniciar
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', loadDashboard);
	} else {
		loadDashboard();
	}

	// Actualizar cada 60 segundos
	setInterval(loadDashboard, 60000);

	// Botón para ver URL del menú público
	const btnVerSlug = document.getElementById('btn-ver-slug');
	if (btnVerSlug) {
		btnVerSlug.addEventListener('click', function () {
			fetch(BASE + 'api/app/tenant_info', {
				method: 'GET',
				headers: {
					'Content-Type': 'application/json'
				}
			})
				.then(res => res.json())
				.then(resp => {
					if (resp && resp.ok && resp.tenant) {
						const t = resp.tenant;
						const slug = t.slug || 'NO CONFIGURADO';
						const url = t.url_menu_publico || 'Slug no configurado';

						if (window.Swal) {
							Swal.fire({
								icon: slug === 'NO CONFIGURADO' ? 'warning' : 'info',
								title: 'URL del Menú Público',
								html: '<div class="text-left">' +
									'<p><strong>Nombre:</strong> ' + t.nombre + '</p>' +
									'<p><strong>Slug:</strong> <code>' + slug + '</code></p>' +
									'<p><strong>URL:</strong> <a href="' + url + '" target="_blank">' + url + '</a></p>' +
									(slug === 'NO CONFIGURADO' ?
										'<div class="alert alert-warning mt-3">⚠️ Necesitas configurar el slug en la base de datos para acceder al menú público.</div>' :
										'<div class="alert alert-info mt-3">💡 Comparte esta URL para que tus clientes vean el menú.</div>') +
									'</div>',
								width: '600px',
								confirmButtonText: 'Cerrar'
							});
						} else {
							alert('Nombre: ' + t.nombre + '\nSlug: ' + slug + '\nURL: ' + url);
						}
					} else {
						if (window.Swal) {
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: 'No se pudo obtener la información del tenant'
							});
						} else {
							alert('Error al obtener información');
						}
					}
				})
				.catch(err => {
					console.error('Error:', err);
					if (window.Swal) {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'No se pudo conectar con el servidor'
						});
					} else {
						alert('Error de conexión');
					}
				});
		});
	}
})();
