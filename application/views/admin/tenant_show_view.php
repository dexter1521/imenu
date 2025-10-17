<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-800">Ficha del Tenant: <?= html_escape($tenant->nombre) ?></h1>
	<a href="<?= site_url('admin/tenants_view') ?>" class="btn btn-sm btn-secondary shadow-sm">
		<i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver a Tenants
	</a>
</div>

<div class="row">
	<!-- Columna Izquierda: Información Principal -->
	<div class="col-lg-8">
		<!-- Detalles del Tenant -->
		<div class="card shadow mb-4">
			<div class="card-header py-3">
				<h6 class="m-0 font-weight-bold text-primary">Detalles del Tenant</h6>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-6">
						<p><strong>ID:</strong> <?= $tenant->id ?></p>
						<p><strong>Nombre:</strong> <?= html_escape($tenant->nombre) ?></p>
						<p><strong>Slug:</strong> <code><?= html_escape($tenant->slug) ?></code></p>
						<p><strong>WhatsApp:</strong> <?= html_escape($tenant->whatsapp ?: '-') ?></p>
					</div>
					<div class="col-md-6">
						<p><strong>Estado:</strong> <?= $tenant->activo ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Suspendido</span>' ?></p>
						<p><strong>Fecha de Creación:</strong> <?= date('d/m/Y H:i', strtotime($tenant->created_at)) ?></p>
						<p><strong>Enlace Público:</strong> <a href="<?= $menu_url ?>" target="_blank"><?= $menu_url ?></a></p>
					</div>
				</div>
			</div>
		</div>

		<!-- Información de Suscripción y Plan -->
		<div class="card shadow mb-4">
			<div class="card-header py-3">
				<h6 class="m-0 font-weight-bold text-primary">Plan y Suscripción</h6>
			</div>
			<div class="card-body">
				<?php if ($plan) : ?>
					<p><strong>Plan Actual:</strong> <?= html_escape($plan->nombre) ?> ($<?= number_format($plan->precio_mensual, 2) ?>/mes)</p>
				<?php else : ?>
					<p><strong>Plan Actual:</strong> <span class="text-muted">Ninguno</span></p>
				<?php endif; ?>

				<?php if ($suscripcion) : ?>
					<p><strong>Suscripción:</strong> <span class="badge badge-info"><?= ucfirst($suscripcion->estatus) ?></span></p>
					<p><strong>Válida desde:</strong> <?= date('d/m/Y', strtotime($suscripcion->inicio)) ?></p>
					<p><strong>Válida hasta:</strong> <?= date('d/m/Y', strtotime($suscripcion->fin)) ?></p>
				<?php else : ?>
					<p><strong>Suscripción:</strong> <span class="text-muted">No hay suscripción activa.</span></p>
				<?php endif; ?>
			</div>
		</div>

		<!-- Últimos Pagos -->
		<div class="card shadow mb-4">
			<div class="card-header py-3">
				<h6 class="m-0 font-weight-bold text-primary">Últimos 5 Pagos</h6>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-sm">
						<thead>
							<tr>
								<th>ID</th>
								<th>Concepto</th>
								<th>Monto</th>
								<th>Fecha</th>
								<th>Estado</th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($ultimos_pagos)) : foreach ($ultimos_pagos as $pago) : ?>
									<tr>
										<td><?= $pago->id ?></td>
										<td><?= html_escape($pago->concepto) ?></td>
										<td>$<?= number_format($pago->monto, 2) ?></td>
										<td><?= date('d/m/Y', strtotime($pago->fecha)) ?></td>
										<td><span class="badge badge-success"><?= html_escape($pago->status) ?></span></td>
									</tr>
								<?php endforeach;
							else : ?>
								<tr>
									<td colspan="5" class="text-center text-muted">No hay pagos registrados.</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<!-- Últimos Pedidos -->
		<div class="card shadow mb-4">
			<div class="card-header py-3">
				<h6 class="m-0 font-weight-bold text-primary">Últimos 5 Pedidos</h6>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-sm">
						<thead>
							<tr>
								<th>ID</th>
								<th>Cliente</th>
								<th>Total</th>
								<th>Fecha</th>
								<th>Estado</th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($ultimos_pedidos)) : foreach ($ultimos_pedidos as $pedido) : ?>
									<tr>
										<td><?= $pedido->id ?></td>
										<td><?= html_escape($pedido->nombre_cliente) ?></td>
										<td>$<?= number_format($pedido->total, 2) ?></td>
										<td><?= date('d/m/Y H:i', strtotime($pedido->fecha_creacion)) ?></td>
										<td><span class="badge badge-info"><?= html_escape($pedido->estado) ?></span></td>
									</tr>
								<?php endforeach;
							else : ?>
								<tr>
									<td colspan="5" class="text-center text-muted">No hay pedidos registrados.</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<!-- Columna Derecha: QR -->
	<div class="col-lg-4">
		<div class="card shadow mb-4">
			<div class="card-header py-3">
				<h6 class="m-0 font-weight-bold text-primary">Código QR del Menú</h6>
			</div>
			<div class="card-body text-center">
				<img src="<?= $qr_url ?>" alt="Código QR" class="img-fluid" style="max-width: 250px; border: 1px solid #ddd; padding: 5px;">
				<p class="mt-2"><a href="<?= $qr_url ?>" download="qr-<?= html_escape($tenant->slug) ?>.png">Descargar QR</a></p>
			</div>
		</div>
	</div>
</div>
