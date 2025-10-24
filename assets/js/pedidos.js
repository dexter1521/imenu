(function () {
	'use strict';

	const BASE_URL = (typeof window.IMENU_BASE_URL !== 'undefined' && window.IMENU_BASE_URL) ?
		window.IMENU_BASE_URL :
		'/imenu/';

	// Función auxiliar para construir URLs
	function url(path) {
		return BASE_URL + path;
	}

	const api = {
		pedidos: url('PedidosService/pedidos'),
		pedido_create: url('PedidosService/pedido_create'),
		pedido: (id) => url('PedidosService/pedido/' + encodeURIComponent(id)),
		pedido_update_estado: (id) => url('PedidosService/pedido_update_estado/' + encodeURIComponent(id)),
		pedidos_export: url('PedidosService/pedidos_export'),
		pedido_delete: (id) => url('PedidosService/pedido_delete/' + encodeURIComponent(id)),

	};

	// === Pedidos ===

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
			'preparando': 'badge-info',
			'listo': 'badge-primary',
			'entregado': 'badge-success',
			'cancelado': 'badge-danger'
		};
		const labels = {
			'pendiente': 'Pendiente',
			'preparando': 'Preparando',
			'listo': 'Listo',
			'entregado': 'Entregado',
			'cancelado': 'Cancelado'
		};
		return '<span class="badge ' + (badges[estado] || 'badge-secondary') + '">' +
			(labels[estado] || estado) + '</span>';
	}

	function getMetodoPagoLabel(metodo) {
		const labels = {
			'efectivo': 'Efectivo',
			'tarjeta': 'Tarjeta',
			'transferencia': 'Transferencia'
		};
		return labels[metodo] || metodo;
	}

	// Estado de paginación
	let currentOffset = 0;
	let currentLimit = 20;
	let totalPedidos = 0;
	let currentPedidoId = null;

	const Pedidos = {
		load: function () {
			const filters = {
				estado: document.getElementById('filter-estado').value,
				metodo_pago: document.getElementById('filter-metodo-pago').value,
				fecha_inicio: document.getElementById('filter-fecha-inicio').value,
				fecha_fin: document.getElementById('filter-fecha-fin').value,
				cliente: document.getElementById('filter-cliente').value,
				limit: currentLimit,
				offset: currentOffset
			};

			const params = new URLSearchParams();
			for (const key in filters) {
				if (filters[key]) params.append(key, filters[key]);
			}

			fetch(api.pedidos + (params.toString() ? ('?' + params.toString()) : ''), {
				method: 'GET',
				headers: {
					'Content-Type': 'application/json'
				}
			}).then(res => res.json())
				.then(resp => {
					if (!resp || !resp.ok) {
						console.error('Error al cargar pedidos');
						return;
					}

					totalPedidos = resp.pagination.total;
					Pedidos.render(resp.data);
					Pedidos.updatePagination(resp.pagination);
				})
				.catch(err => {
					console.error('Error cargando pedidos:', err);
					const tbody = document.querySelector('#pedidos-table tbody');
					tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error al cargar pedidos</td></tr>';
				});
		},

		render: function (pedidos) {
			const tbody = document.querySelector('#pedidos-table tbody');
			let html = '';

			if (pedidos && pedidos.length > 0) {
				pedidos.forEach(p => {
					html += '<tr>' +
						'<td><strong>#' + p.id + '</strong></td>' +
						'<td>' + (p.nombre_cliente || 'Sin nombre') + '</td>' +
						'<td>' + (p.telefono_cliente || '--') + '</td>' +
						'<td>' + formatCurrency(p.total) + '</td>' +
						'<td>' + getMetodoPagoLabel(p.metodo_pago) + '</td>' +
						'<td>' + getEstadoBadge(p.estado) + '</td>' +
						'<td>' + formatDate(p.creado_en) + '</td>' +
						'<td>' +
						'<button class="btn btn-sm btn-info btn-ver-detalle" data-id="' + p.id + '">' +
						'<i class="fas fa-eye"></i> Ver' +
						'</button> ';

					// Botones rápidos según estado
					if (p.estado === 'pendiente') {
						html += '<button class="btn btn-sm btn-success btn-quick-action" data-id="' + p.id + '" data-action="preparando" title="Aceptar">' +
							'<i class="fas fa-check"></i>' +
							'</button>';
					} else if (p.estado === 'preparando') {
						html += '<button class="btn btn-sm btn-primary btn-quick-action" data-id="' + p.id + '" data-action="listo" title="Marcar Listo">' +
							'<i class="fas fa-check-double"></i>' +
							'</button>';
					} else if (p.estado === 'listo') {
						html += '<button class="btn btn-sm btn-success btn-quick-action" data-id="' + p.id + '" data-action="entregado" title="Entregar">' +
							'<i class="fas fa-shipping-fast"></i>' +
							'</button>';
					}

					html += '</td></tr>';
				});
			} else {
				html = '<tr><td colspan="8" class="text-center text-muted">No hay pedidos</td></tr>';
			}

			tbody.innerHTML = html;
		},

		updatePagination: function (pagination) {
			const info = document.getElementById('pagination-info');
			const btnPrev = document.getElementById('btn-prev');
			const btnNext = document.getElementById('btn-next');

			const start = pagination.offset + 1;
			const end = Math.min(pagination.offset + pagination.limit, pagination.total);

			info.textContent = 'Mostrando ' + start + '-' + end + ' de ' + pagination.total + ' pedidos';

			btnPrev.disabled = pagination.offset === 0;
			btnNext.disabled = !pagination.has_more;
		},

		loadDetalle: function (id) {
			fetch(api.pedido(id), {
				method: 'GET',
				headers: {
					'Content-Type': 'application/json'
				}
			})
				.then(res => res.json())
				.then(resp => {
					if (!resp || !resp.ok) {
						alert('Error al cargar detalle del pedido');
						return;
					}

					Pedidos.showModal(resp.data);
				})
				.catch(err => {
					console.error('Error cargando detalle:', err);
					alert('Error al cargar detalle del pedido');
				});
		},

		showModal: function (pedido) {
			currentPedidoId = pedido.id;

			document.getElementById('modal-pedido-id').textContent = pedido.id;
			document.getElementById('modal-cliente').textContent = pedido.nombre_cliente || 'Sin nombre';
			document.getElementById('modal-telefono').textContent = pedido.telefono_cliente || '--';
			document.getElementById('modal-fecha').textContent = formatDate(pedido.creado_en);
			document.getElementById('modal-metodo-pago').textContent = getMetodoPagoLabel(pedido.metodo_pago);
			document.getElementById('modal-estado-badge').innerHTML = getEstadoBadge(pedido.estado);
			document.getElementById('modal-total').textContent = formatCurrency(pedido.total);

			// Items
			const itemsBody = document.getElementById('modal-items');
			let itemsHtml = '';

			if (pedido.items && pedido.items.length > 0) {
				pedido.items.forEach(item => {
					itemsHtml += '<tr>' +
						'<td>' + (item.nombre || item.producto_nombre || 'Producto') + '</td>' +
						'<td>' + formatCurrency(item.precio_unit) + '</td>' +
						'<td>' + item.cantidad + '</td>' +
						'<td>' + formatCurrency(item.subtotal) + '</td>' +
						'</tr>';
				});
			} else {
				itemsHtml = '<tr><td colspan="4" class="text-center text-muted">Sin items</td></tr>';
			}

			itemsBody.innerHTML = itemsHtml;

			// Notas
			if (pedido.notas) {
				document.getElementById('modal-notas').textContent = pedido.notas;
				document.getElementById('modal-notas-container').style.display = 'block';
			} else {
				document.getElementById('modal-notas-container').style.display = 'none';
			}

			// Botones según estado
			Pedidos.updateModalButtons(pedido.estado);

			// Mostrar modal
			const modal = document.getElementById('pedidoModal');
			if (window.jQuery && window.jQuery(modal).modal) {
				window.jQuery(modal).modal('show');
			} else if (window.bootstrap) {
				new bootstrap.Modal(modal).show();
			}
		},

		updateModalButtons: function (estado) {
			const btnAceptar = document.getElementById('btn-aceptar-pedido');
			const btnListo = document.getElementById('btn-marcar-listo');
			const btnEntregado = document.getElementById('btn-marcar-entregado');
			const btnCancelar = document.getElementById('btn-cancelar-pedido');

			// Ocultar todos
			btnAceptar.style.display = 'none';
			btnListo.style.display = 'none';
			btnEntregado.style.display = 'none';
			btnCancelar.style.display = 'none';

			// Mostrar según estado
			if (estado === 'pendiente') {
				btnAceptar.style.display = 'inline-block';
				btnCancelar.style.display = 'inline-block';
			} else if (estado === 'preparando') {
				btnListo.style.display = 'inline-block';
				btnCancelar.style.display = 'inline-block';
			} else if (estado === 'listo') {
				btnEntregado.style.display = 'inline-block';
			}
		},

		updateEstado: function (id, nuevoEstado, callback) {
			const data = Object.assign({
				estado: nuevoEstado
			}, csrfData());

			fetch(api.pedido_update_estado(id), {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: new URLSearchParams(data)
			}).then(res => res.json())
				.then(resp => {
					if (callback) callback(null, resp);
				})
				.catch(err => {
					if (callback) callback(err);
				});
		},

		bindUI: function () {
			// Aplicar filtros
			document.getElementById('btn-aplicar-filtros').addEventListener('click', function () {
				currentOffset = 0;
				Pedidos.load();
			});

			// Refresh
			document.getElementById('btn-refresh').addEventListener('click', function () {
				Pedidos.load();
			});

			// Paginación
			document.getElementById('btn-prev').addEventListener('click', function () {
				if (currentOffset > 0) {
					currentOffset = Math.max(0, currentOffset - currentLimit);
					Pedidos.load();
				}
			});

			document.getElementById('btn-next').addEventListener('click', function () {
				if (currentOffset + currentLimit < totalPedidos) {
					currentOffset += currentLimit;
					Pedidos.load();
				}
			});

			// Ver detalle
			document.querySelector('#pedidos-table').addEventListener('click', function (e) {
				if (e.target.closest('.btn-ver-detalle')) {
					const btn = e.target.closest('.btn-ver-detalle');
					const id = btn.dataset.id;
					Pedidos.loadDetalle(id);
				} else if (e.target.closest('.btn-quick-action')) {
					const btn = e.target.closest('.btn-quick-action');
					const id = btn.dataset.id;
					const action = btn.dataset.action;

					if (confirm('¿Cambiar estado del pedido?')) {
						Pedidos.updateEstado(id, action, function (err, resp) {
							if (err || !resp || !resp.ok) {
								alert('Error al actualizar estado');
							} else {
								Pedidos.load();
							}
						});
					}
				}
			});

			// Botones del modal
			document.getElementById('btn-aceptar-pedido').addEventListener('click', function () {
				Pedidos.updateEstado(currentPedidoId, 'preparando', function (err, resp) {
					if (err || !resp || !resp.ok) {
						alert('Error al aceptar pedido');
					} else {
						const modal = document.getElementById('pedidoModal');
						if (window.jQuery && window.jQuery(modal).modal) {
							window.jQuery(modal).modal('hide');
						}
						Pedidos.load();
					}
				});
			});

			document.getElementById('btn-marcar-listo').addEventListener('click', function () {
				Pedidos.updateEstado(currentPedidoId, 'listo', function (err, resp) {
					if (err || !resp || !resp.ok) {
						alert('Error al marcar como listo');
					} else {
						const modal = document.getElementById('pedidoModal');
						if (window.jQuery && window.jQuery(modal).modal) {
							window.jQuery(modal).modal('hide');
						}
						Pedidos.load();
					}
				});
			});

			document.getElementById('btn-marcar-entregado').addEventListener('click', function () {
				Pedidos.updateEstado(currentPedidoId, 'entregado', function (err, resp) {
					if (err || !resp || !resp.ok) {
						alert('Error al marcar como entregado');
					} else {
						const modal = document.getElementById('pedidoModal');
						if (window.jQuery && window.jQuery(modal).modal) {
							window.jQuery(modal).modal('hide');
						}
						Pedidos.load();
					}
				});
			});

			document.getElementById('btn-cancelar-pedido').addEventListener('click', function () {
				if (confirm('¿Seguro que deseas cancelar este pedido?')) {
					Pedidos.updateEstado(currentPedidoId, 'cancelado', function (err, resp) {
						if (err || !resp || !resp.ok) {
							alert('Error al cancelar pedido');
						} else {
							const modal = document.getElementById('pedidoModal');
							if (window.jQuery && window.jQuery(modal).modal) {
								window.jQuery(modal).modal('hide');
							}
							Pedidos.load();
						}
					});
				}
			});

			// Export
			document.getElementById('btn-export').addEventListener('click', function () {
				const filters = {
					estado: document.getElementById('filter-estado').value,
					metodo_pago: document.getElementById('filter-metodo-pago').value,
					fecha_inicio: document.getElementById('filter-fecha-inicio').value,
					fecha_fin: document.getElementById('filter-fecha-fin').value,
					cliente: document.getElementById('filter-cliente').value,
					formato: 'csv'
				};

				const params = new URLSearchParams();
				for (const key in filters) {
					if (filters[key]) params.append(key, filters[key]);
				}

				window.open(api.pedidos_export + (params.toString() ? ('?' + params.toString()) : ''), '_blank');
			});
		},

		init: function () {
			Pedidos.bindUI();
			Pedidos.load();
		}
	};

	// Inicializar
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', Pedidos.init);
	} else {
		Pedidos.init();
	}

})();
