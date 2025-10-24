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

<script src="<?php echo base_url('assets/js/pedidos.js?v=' . time()); ?>"></script>
