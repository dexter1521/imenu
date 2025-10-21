<?php
// application/views/app/pedidos.php
// Requiere SB Admin 2 + Bootstrap 4 + jQuery ya cargados en tu layout base.
?>
<div class="container-fluid">

	<!-- Page Heading -->
	<div class="d-sm-flex align-items-center justify-content-between mb-4">
		<h1 class="h3 mb-0 text-gray-800">Gestión de Pedidos</h1>
		<div>
			<button class="btn btn-sm btn-info" id="btn-refresh">
				<i class="fas fa-sync-alt"></i> Actualizar
			</button>
			<button class="btn btn-sm btn-success" id="btn-export">
				<i class="fas fa-download"></i> Exportar
			</button>
		</div>
	</div>

	<!-- Filtros -->
	<div class="card shadow mb-4">
		<div class="card-header py-3">
			<h6 class="m-0 font-weight-bold text-primary">
				<i class="fas fa-filter"></i> Filtros
			</h6>
		</div>
		<div class="card-body">
			<div class="row">
				<div class="col-md-3 mb-3">
					<label for="filter-estado">Estado</label>
					<select class="form-control form-control-sm" id="filter-estado">
						<option value="">Todos</option>
						<option value="pendiente">Pendiente</option>
						<option value="preparando">Preparando</option>
						<option value="listo">Listo</option>
						<option value="entregado">Entregado</option>
						<option value="cancelado">Cancelado</option>
					</select>
				</div>
				<div class="col-md-3 mb-3">
					<label for="filter-metodo-pago">Método de Pago</label>
					<select class="form-control form-control-sm" id="filter-metodo-pago">
						<option value="">Todos</option>
						<option value="efectivo">Efectivo</option>
						<option value="tarjeta">Tarjeta</option>
						<option value="transferencia">Transferencia</option>
					</select>
				</div>
				<div class="col-md-3 mb-3">
					<label for="filter-fecha-inicio">Fecha Inicio</label>
					<input type="date" class="form-control form-control-sm" id="filter-fecha-inicio">
				</div>
				<div class="col-md-3 mb-3">
					<label for="filter-fecha-fin">Fecha Fin</label>
					<input type="date" class="form-control form-control-sm" id="filter-fecha-fin">
				</div>
			</div>
			<div class="row">
				<div class="col-md-9 mb-3">
					<label for="filter-cliente">Buscar Cliente</label>
					<input type="text" class="form-control form-control-sm" id="filter-cliente" placeholder="Nombre del cliente...">
				</div>
				<div class="col-md-3 mb-3">
					<label>&nbsp;</label>
					<button class="btn btn-primary btn-block btn-sm" id="btn-aplicar-filtros">
						<i class="fas fa-search"></i> Buscar
					</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Tabla de Pedidos -->
	<div class="card shadow mb-4">
		<div class="card-header py-3">
			<h6 class="m-0 font-weight-bold text-primary">Lista de Pedidos</h6>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered table-hover" id="pedidos-table">
					<thead>
						<tr>
							<th>ID</th>
							<th>Cliente</th>
							<th>Teléfono</th>
							<th>Total</th>
							<th>Método Pago</th>
							<th>Estado</th>
							<th>Fecha</th>
							<th>Acciones</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="8" class="text-center">
								<span class="spinner-border spinner-border-sm"></span> Cargando pedidos...
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- Paginación -->
			<div class="row mt-3">
				<div class="col-md-6">
					<p class="text-muted" id="pagination-info">Mostrando 0 de 0 pedidos</p>
				</div>
				<div class="col-md-6 text-right">
					<button class="btn btn-sm btn-secondary" id="btn-prev" disabled>
						<i class="fas fa-chevron-left"></i> Anterior
					</button>
					<button class="btn btn-sm btn-secondary" id="btn-next" disabled>
						Siguiente <i class="fas fa-chevron-right"></i>
					</button>
				</div>
			</div>
		</div>
	</div>


	<!-- Modal Detalle de Pedido -->
	<div class="modal fade" id="pedidoModal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<i class="fas fa-receipt"></i> Detalle del Pedido #<span id="modal-pedido-id">--</span>
					</h5>
					<button type="button" class="close" data-dismiss="modal">
						<span>&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<!-- Información del Cliente -->
					<div class="row mb-3">
						<div class="col-md-6">
							<h6 class="font-weight-bold">Cliente</h6>
							<p class="mb-1"><i class="fas fa-user"></i> <span id="modal-cliente">--</span></p>
							<p class="mb-1"><i class="fas fa-phone"></i> <span id="modal-telefono">--</span></p>
						</div>
						<div class="col-md-6">
							<h6 class="font-weight-bold">Información del Pedido</h6>
							<p class="mb-1"><i class="fas fa-calendar"></i> <span id="modal-fecha">--</span></p>
							<p class="mb-1"><i class="fas fa-credit-card"></i> <span id="modal-metodo-pago">--</span></p>
							<p class="mb-1"><i class="fas fa-info-circle"></i> <span id="modal-estado-badge">--</span></p>
						</div>
					</div>

					<hr>

					<!-- Items del Pedido -->
					<h6 class="font-weight-bold">Productos</h6>
					<div class="table-responsive">
						<table class="table table-sm">
							<thead>
								<tr>
									<th>Producto</th>
									<th>Precio Unit.</th>
									<th>Cantidad</th>
									<th>Subtotal</th>
								</tr>
							</thead>
							<tbody id="modal-items">
								<tr>
									<td colspan="4" class="text-center text-muted">Sin items</td>
								</tr>
							</tbody>
						</table>
					</div>

					<div class="text-right">
						<h4>Total: <span class="text-success" id="modal-total">$0.00</span></h4>
					</div>

					<!-- Notas -->
					<div class="mt-3" id="modal-notas-container" style="display: none;">
						<h6 class="font-weight-bold">Notas</h6>
						<p id="modal-notas" class="text-muted">--</p>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
					<button type="button" class="btn btn-success" id="btn-aceptar-pedido" style="display: none;">
						<i class="fas fa-check"></i> Aceptar
					</button>
					<button type="button" class="btn btn-info" id="btn-marcar-listo" style="display: none;">
						<i class="fas fa-check-double"></i> Marcar Listo
					</button>
					<button type="button" class="btn btn-primary" id="btn-marcar-entregado" style="display: none;">
						<i class="fas fa-shipping-fast"></i> Entregar
					</button>
					<button type="button" class="btn btn-danger" id="btn-cancelar-pedido" style="display: none;">
						<i class="fas fa-times"></i> Cancelar
					</button>
				</div>
			</div>
		</div>
	</div>

</div> <!-- /container-fluid -->

<script src="<?= base_url('assets/js/app.js') ?>"></script>
<script>
	// Pedidos - Vanilla JS
	(function() {
		'use strict';

		const BASE = (typeof window.IMENU_BASE_URL !== 'undefined' && window.IMENU_BASE_URL) ?
			window.IMENU_BASE_URL :
			'/imenu/';

		function appUrl(path) {
			path = path || '';
			if (path.charAt(0) === '/') path = path.slice(1);
			return BASE + 'app/' + path;
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
			load: function() {
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

				fetch(appUrl('pedidos?' + params.toString()), {
						method: 'GET',
						headers: {
							'Content-Type': 'application/json'
						}
					})
					.then(res => res.json())
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

			render: function(pedidos) {
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

			updatePagination: function(pagination) {
				const info = document.getElementById('pagination-info');
				const btnPrev = document.getElementById('btn-prev');
				const btnNext = document.getElementById('btn-next');

				const start = pagination.offset + 1;
				const end = Math.min(pagination.offset + pagination.limit, pagination.total);

				info.textContent = 'Mostrando ' + start + '-' + end + ' de ' + pagination.total + ' pedidos';

				btnPrev.disabled = pagination.offset === 0;
				btnNext.disabled = !pagination.has_more;
			},

			loadDetalle: function(id) {
				fetch(appUrl('pedido/' + id), {
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

			showModal: function(pedido) {
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

			updateModalButtons: function(estado) {
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

			updateEstado: function(id, nuevoEstado, callback) {
				const data = Object.assign({
					estado: nuevoEstado
				}, csrfData());

				fetch(appUrl('pedido_update_estado/' + id), {
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

			bindUI: function() {
				// Aplicar filtros
				document.getElementById('btn-aplicar-filtros').addEventListener('click', function() {
					currentOffset = 0;
					Pedidos.load();
				});

				// Refresh
				document.getElementById('btn-refresh').addEventListener('click', function() {
					Pedidos.load();
				});

				// Paginación
				document.getElementById('btn-prev').addEventListener('click', function() {
					if (currentOffset > 0) {
						currentOffset = Math.max(0, currentOffset - currentLimit);
						Pedidos.load();
					}
				});

				document.getElementById('btn-next').addEventListener('click', function() {
					if (currentOffset + currentLimit < totalPedidos) {
						currentOffset += currentLimit;
						Pedidos.load();
					}
				});

				// Ver detalle
				document.querySelector('#pedidos-table').addEventListener('click', function(e) {
					if (e.target.closest('.btn-ver-detalle')) {
						const btn = e.target.closest('.btn-ver-detalle');
						const id = btn.dataset.id;
						Pedidos.loadDetalle(id);
					} else if (e.target.closest('.btn-quick-action')) {
						const btn = e.target.closest('.btn-quick-action');
						const id = btn.dataset.id;
						const action = btn.dataset.action;

						if (confirm('¿Cambiar estado del pedido?')) {
							Pedidos.updateEstado(id, action, function(err, resp) {
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
				document.getElementById('btn-aceptar-pedido').addEventListener('click', function() {
					Pedidos.updateEstado(currentPedidoId, 'preparando', function(err, resp) {
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

				document.getElementById('btn-marcar-listo').addEventListener('click', function() {
					Pedidos.updateEstado(currentPedidoId, 'listo', function(err, resp) {
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

				document.getElementById('btn-marcar-entregado').addEventListener('click', function() {
					Pedidos.updateEstado(currentPedidoId, 'entregado', function(err, resp) {
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

				document.getElementById('btn-cancelar-pedido').addEventListener('click', function() {
					if (confirm('¿Seguro que deseas cancelar este pedido?')) {
						Pedidos.updateEstado(currentPedidoId, 'cancelado', function(err, resp) {
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
				document.getElementById('btn-export').addEventListener('click', function() {
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

					window.open(appUrl('pedidos_export?' + params.toString()), '_blank');
				});
			},

			init: function() {
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
</script>
