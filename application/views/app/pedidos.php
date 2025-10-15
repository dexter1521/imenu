<?php
// application/views/app/pedidos.php
// Requiere SB Admin 2 + Bootstrap 4 + jQuery ya cargados en tu layout base.
?>
<div class="container-fluid">

	<!-- Page Heading -->
	<div class="d-sm-flex align-items-center justify-content-between mb-4">
		<h1 class="h3 mb-0 text-gray-800">Pedidos</h1>
	</div>

	<!-- Tabla -->
	<div class="card shadow mb-4">
		<div class="card-header py-3 d-flex align-items-center">
			<h6 class="m-0 font-weight-bold text-primary">Listado de pedidos</h6>
			<div class="ml-auto small text-muted">
				Ordenados por fecha (más recientes primero)
			</div>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table id="tblPedidos" class="table table-bordered table-striped" width="100%">
					<thead class="thead-light">
						<tr>
							<th>#</th>
							<th>Cliente</th>
							<th>Teléfono</th>
							<th>Método</th>
							<th class="text-right">Total</th>
							<th>Estado</th>
							<th>Fecha</th>
							<th style="width:180px;">Acciones</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>

</div>

<!-- Modal Detalle -->
<div class="modal fade" id="mdlDetalle" tabindex="-1" role="dialog" aria-labelledby="mdlDetalleLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header py-2">
				<h5 class="modal-title" id="mdlDetalleLabel">Detalle del pedido</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span>&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="mb-2">
					<strong>Cliente:</strong> <span id="dCli"></span> &nbsp;|&nbsp;
					<strong>Tel:</strong> <span id="dTel"></span> &nbsp;|&nbsp;
					<strong>Método:</strong> <span id="dMetodo"></span>
				</div>
				<div class="table-responsive">
					<table class="table table-sm table-striped mb-0">
						<thead class="thead-light">
							<tr>
								<th>Producto</th>
								<th class="text-center">Cant.</th>
								<th class="text-right">Precio</th>
								<th class="text-right">Subtotal</th>
							</tr>
						</thead>
						<tbody id="dItems"></tbody>
						<tfoot>
							<tr>
								<th colspan="3" class="text-right">Total:</th>
								<th class="text-right" id="dTotal">$0.00</th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
			<div class="modal-footer py-2">
				<button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal Eliminar -->
<div class="modal fade" id="mdlDelete" tabindex="-1" role="dialog" aria-labelledby="mdlDeleteLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header py-2">
				<h5 class="modal-title" id="mdlDeleteLabel">Eliminar pedido</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span>&times;</span></button>
			</div>
			<div class="modal-body">
				¿Seguro que deseas eliminar el pedido <strong>#<span id="delId"></span></strong>? Esta acción no se puede deshacer.
			</div>
			<div class="modal-footer py-2">
				<button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
				<button type="button" class="btn btn-danger" id="btnConfirmDelete">Eliminar</button>
			</div>
		</div>
	</div>
</div>

<!-- DataTables (si no lo tienes ya en tu layout) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

<script>
	(function() {
		var currency = '<?= isset($aj->formato_precio) && strpos($aj->formato_precio, "$") !== false ? "$" : "$" ?>';

		function money(n) {
			n = parseFloat(n || 0);
			return currency + n.toFixed(2);
		}

		var table = $('#tblPedidos').DataTable({
			ajax: {
				url: '/api/app/pedidos',
				dataSrc: function(json) {
					return (json && json.ok) ? json.data : [];
				}
			},
			columns: [{
					data: 'id'
				},
				{
					data: 'nombre_cliente'
				},
				{
					data: 'telefono_cliente'
				},
				{
					data: 'metodo_pago',
					render: function(v) {
						return v ? v.charAt(0).toUpperCase() + v.slice(1) : '';
					}
				},
				{
					data: 'total',
					className: 'text-right',
					render: function(v) {
						return money(v);
					}
				},
				{
					data: 'estado',
					render: function(v, t, row) {
						var sel =
							'<select class="custom-select custom-select-sm sel-estado" data-id="' + row.id + '" style="min-width:120px;">' +
							opt('pendiente', 'Pendiente', v === 'pendiente') +
							opt('entregado', 'Entregado', v === 'entregado') +
							opt('cancelado', 'Cancelado', v === 'cancelado') +
							'</select>';

						function opt(val, label, sel) {
							return '<option value="' + val + '" ' + (sel ? 'selected' : '') + '>' + label + '</option>';
						}
						return sel;
					}
				},
				{
					data: 'creado_en',
					render: function(v) {
						return v ? v : '';
					}
				},
				{
					data: null,
					orderable: false,
					render: function(row) {
						return '' +
							'<div class="btn-group btn-group-sm" role="group">' +
							'<button class="btn btn-info btn-detalle" data-id="' + row.id + '">Detalle</button>' +
							'<button class="btn btn-success btn-entregado" data-id="' + row.id + '">Entregado</button>' +
							'<button class="btn btn-warning btn-cancelado text-white" data-id="' + row.id + '">Cancelar</button>' +
							'<button class="btn btn-danger btn-delete" data-id="' + row.id + '">Eliminar</button>' +
							'</div>';
					}
				}
			],
			order: [
				[6, 'desc']
			], // por fecha descendente
			language: {
				url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-MX.json'
			},
			pageLength: 10,
			responsive: true
		});

		// Refrescar
		function reload() {
			table.ajax.reload(null, false);
		}

		// Ver detalle
		$('#tblPedidos').on('click', '.btn-detalle', function() {
			var id = $(this).data('id');
			$.getJSON('/api/app/pedido/' + id, function(res) {
				if (!res || !res.ok) {
					alert('No se pudo cargar el detalle.');
					return;
				}
				var p = res.data.pedido,
					items = res.data.items || [];
				$('#mdlDetalle #dCli').text(p.nombre_cliente || '');
				$('#mdlDetalle #dTel').text(p.telefono_cliente || '');
				$('#mdlDetalle #dMetodo').text(p.metodo_pago || '');
				var $tb = $('#mdlDetalle #dItems').empty();
				var total = 0;
				items.forEach(function(it) {
					total += parseFloat(it.subtotal || 0);
					$tb.append(
						'<tr>' +
						'<td>' + escapeHtml(it.nombre || '') + '</td>' +
						'<td class="text-center">' + (it.cantidad || 1) + '</td>' +
						'<td class="text-right">' + money(it.precio_unit || 0) + '</td>' +
						'<td class="text-right">' + money(it.subtotal || 0) + '</td>' +
						'</tr>'
					);
				});
				$('#mdlDetalle #dTotal').text(money(total));
				$('#mdlDetalle').modal('show');
			}).fail(function() {
				alert('Error al consultar el pedido.');
			});
		});

		// Cambio de estado (dropdown)
		$('#tblPedidos').on('change', '.sel-estado', function() {
			var id = $(this).data('id');
			var estado = $(this).val();
			updateEstado(id, estado);
		});

		// Botón rápido: Entregado
		$('#tblPedidos').on('click', '.btn-entregado', function() {
			updateEstado($(this).data('id'), 'entregado');
		});

		// Botón rápido: Cancelado
		$('#tblPedidos').on('click', '.btn-cancelado', function() {
			updateEstado($(this).data('id'), 'cancelado');
		});

		function updateEstado(id, estado) {
			$.post('/api/app/pedido/' + id + '/estado', {
					estado: estado
				})
				.done(function(res) {
					if (res && res.ok) {
						reload();
					} else {
						alert((res && res.msg) ? res.msg : 'No fue posible actualizar el estado.');
						reload();
					}
				})
				.fail(function() {
					alert('Error al actualizar estado.');
					reload();
				});
		}

		// Eliminar
		var delId = null;
		$('#tblPedidos').on('click', '.btn-delete', function() {
			delId = $(this).data('id');
			$('#delId').text(delId);
			$('#mdlDelete').modal('show');
		});
		$('#btnConfirmDelete').on('click', function() {
			if (!delId) return;
			$.ajax({
					url: '/api/app/pedido/' + delId,
					method: 'DELETE'
				}).done(function(res) {
					if (res && res.ok) {
						$('#mdlDelete').modal('hide');
						reload();
					} else {
						alert((res && res.msg) ? res.msg : 'No se pudo eliminar.');
					}
				}).fail(function() {
					alert('Error al eliminar.');
				})
				.always(function() {
					delId = null;
				});
		});

		// Helpers
		function escapeHtml(str) {
			return ('' + str).replace(/[&<>"']/g, function(m) {
				return ({
					'&': '&amp;',
					'<': '&lt;',
					'>': '&gt;',
					'"': '&quot;',
					"'": '&#39;'
				})[m];
			});
		}
	})();
</script>
