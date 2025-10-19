<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-800">Ficha del Tenant: <?= html_escape($tenant->nombre) ?></h1>
	<div>
		<a href="<?= site_url('admin/tenants_view') ?>" class="btn btn-sm btn-secondary shadow-sm">
			<i class="fas fa-arrow-left fa-sm"></i> Volver a Tenants
		</a>
		<button type="button" class="btn btn-sm <?= $tenant->activo ? 'btn-warning' : 'btn-success' ?> shadow-sm" onclick="toggleTenantStatus(<?= $tenant->id ?>, <?= $tenant->activo ?>)">
			<i class="fas fa-<?= $tenant->activo ? 'pause' : 'play' ?> fa-sm"></i>
			<?= $tenant->activo ? 'Suspender' : 'Reactivar' ?>
		</button>
	</div>
</div>

<!-- Tarjetas de Estadísticas -->
<div class="row mb-4">
	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-primary shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Categorías</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_categorias'] ?></div>
					</div>
					<div class="col-auto">
						<i class="fas fa-list fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-success shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-success text-uppercase mb-1">Productos</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_productos'] ?></div>
					</div>
					<div class="col-auto">
						<i class="fas fa-utensils fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-info shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pedidos</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_pedidos'] ?></div>
					</div>
					<div class="col-auto">
						<i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-warning shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pagos</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_pagos'] ?></div>
					</div>
					<div class="col-auto">
						<i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
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
			<div class="card-header py-3 d-flex justify-content-between align-items-center">
				<h6 class="m-0 font-weight-bold text-primary">Plan y Suscripción</h6>
				<button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalCambiarPlan">
					<i class="fas fa-exchange-alt fa-sm"></i> Cambiar Plan
				</button>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-6">
						<?php if ($plan) : ?>
							<p><strong>Plan Actual:</strong> <span class="badge badge-primary"><?= html_escape($plan->nombre) ?></span></p>
							<p><strong>Precio:</strong> $<?= number_format($plan->precio_mensual, 2) ?>/mes</p>
							<p><strong>Límite Categorías:</strong> <?= $plan->limite_categorias ?></p>
							<p><strong>Límite Items:</strong> <?= $plan->limite_items ?></p>
							<p><strong>Anuncios:</strong> <?= $plan->ads ? '<span class="badge badge-warning">Sí</span>' : '<span class="badge badge-success">No</span>' ?></p>
						<?php else : ?>
							<p><strong>Plan Actual:</strong> <span class="text-muted">Ninguno asignado</span></p>
							<p><em>Este tenant no tiene plan asignado. Use el botón "Cambiar Plan" para asignar uno.</em></p>
						<?php endif; ?>
					</div>
					<div class="col-md-6">
						<?php if ($suscripcion) : ?>
							<p><strong>Suscripción:</strong>
								<?php
								$badge_class = 'secondary';
								if ($suscripcion->estatus == 'activa') $badge_class = 'success';
								elseif ($suscripcion->estatus == 'pendiente') $badge_class = 'warning';
								elseif ($suscripcion->estatus == 'expirada') $badge_class = 'danger';
								?>
								<span class="badge badge-<?= $badge_class ?>"><?= ucfirst($suscripcion->estatus) ?></span>
							</p>
							<p><strong>Inicio:</strong> <?= date('d/m/Y', strtotime($suscripcion->inicio)) ?></p>
							<p><strong>Fin:</strong> <?= date('d/m/Y', strtotime($suscripcion->fin)) ?></p>
							<?php
							$dias_restantes = (strtotime($suscripcion->fin) - time()) / (60 * 60 * 24);
							if ($dias_restantes > 0) {
								echo '<p><strong>Días restantes:</strong> <span class="text-success">' . ceil($dias_restantes) . ' días</span></p>';
							} else {
								echo '<p><strong>Estado:</strong> <span class="text-danger">Expirada hace ' . abs(floor($dias_restantes)) . ' días</span></p>';
							}
							?>
						<?php else : ?>
							<p><strong>Suscripción:</strong> <span class="text-muted">No hay suscripción activa.</span></p>
						<?php endif; ?>
					</div>
				</div>
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

	<!-- Columna Derecha: QR y Acciones -->
	<div class="col-lg-4">
		<!-- Código QR -->
		<div class="card shadow mb-4">
			<div class="card-header py-3">
				<h6 class="m-0 font-weight-bold text-primary">Código QR del Menú</h6>
			</div>
			<div class="card-body text-center">
				<?php if (file_exists(FCPATH . 'uploads/tenants/' . $tenant->id . '/qr.png')) : ?>
					<img src="<?= $qr_url ?>" alt="Código QR" class="img-fluid mb-3" style="max-width: 250px; border: 1px solid #ddd; padding: 10px; background: white;">
					<div>
						<a href="<?= $qr_url ?>" download="qr-<?= html_escape($tenant->slug) ?>.png" class="btn btn-sm btn-primary">
							<i class="fas fa-download"></i> Descargar QR
						</a>
					</div>
				<?php else : ?>
					<p class="text-muted">No se ha generado el código QR aún.</p>
					<button type="button" class="btn btn-sm btn-primary" onclick="generarQR(<?= $tenant->id ?>)">
						<i class="fas fa-qrcode"></i> Generar QR
					</button>
				<?php endif; ?>
			</div>
		</div>

		<!-- Enlaces útiles -->
		<div class="card shadow mb-4">
			<div class="card-header py-3">
				<h6 class="m-0 font-weight-bold text-primary">Enlaces</h6>
			</div>
			<div class="card-body">
				<p><strong>Menú Público:</strong></p>
				<div class="input-group mb-3">
					<input type="text" class="form-control form-control-sm" id="menuUrl" value="<?= $menu_url ?>" readonly>
					<div class="input-group-append">
						<button class="btn btn-sm btn-outline-secondary" type="button" onclick="copiarUrl()">
							<i class="fas fa-copy"></i>
						</button>
					</div>
				</div>
				<a href="<?= $menu_url ?>" target="_blank" class="btn btn-sm btn-block btn-success">
					<i class="fas fa-external-link-alt"></i> Ver Menú Público
				</a>
			</div>
		</div>
	</div>
</div>

<!-- Modal: Cambiar Plan -->
<div class="modal fade" id="modalCambiarPlan" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Cambiar Plan del Tenant</h5>
				<button type="button" class="close" data-dismiss="modal">
					<span>&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="formCambiarPlan">
					<div class="form-group">
						<label for="nuevoPlan">Seleccionar Nuevo Plan</label>
						<select class="form-control" id="nuevoPlan" name="plan_id" required>
							<option value="">-- Seleccione un plan --</option>
							<?php if (!empty($planes_disponibles)) : foreach ($planes_disponibles as $p) : ?>
									<option value="<?= $p->id ?>" <?= ($plan && $plan->id == $p->id) ? 'selected' : '' ?>>
										<?= html_escape($p->nombre) ?> - $<?= number_format($p->precio_mensual, 2) ?>/mes
										(<?= $p->limite_categorias ?> cat, <?= $p->limite_items ?> items)
									</option>
							<?php endforeach;
							endif; ?>
						</select>
					</div>
					<div class="alert alert-info">
						<strong>Nota:</strong> El cambio de plan se aplicará inmediatamente. Asegúrese de crear una nueva suscripción si es necesario.
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
				<button type="button" class="btn btn-primary" onclick="cambiarPlan()">
					<i class="fas fa-save"></i> Cambiar Plan
				</button>
			</div>
		</div>
	</div>
</div>

<script>
	const BASE_URL = '<?= base_url() ?>';
	const TENANT_ID = <?= $tenant->id ?>;

	// Copiar URL al portapapeles
	function copiarUrl() {
		const input = document.getElementById('menuUrl');
		input.select();
		document.execCommand('copy');
		showAlert('URL copiada al portapapeles', 'success');
	}

	// Cambiar plan del tenant
	async function cambiarPlan() {
		const planId = document.getElementById('nuevoPlan').value;
		if (!planId) {
			showAlert('Por favor seleccione un plan', 'warning');
			return;
		}

		try {
			const formData = new FormData();
			formData.append('plan_id', planId);
			formData.append('<?= $this->security->get_csrf_token_name() ?>', '<?= $this->security->get_csrf_hash() ?>');

			const response = await fetch(BASE_URL + 'admin/tenant_change_plan/' + TENANT_ID, {
				method: 'POST',
				body: formData
			});

			const data = await response.json();

			if (data.ok) {
				showAlert(data.msg, 'success');
				$('#modalCambiarPlan').modal('hide');
				setTimeout(() => location.reload(), 1500);
			} else {
				showAlert(data.msg || 'Error al cambiar el plan', 'error');
			}
		} catch (error) {
			console.error('Error:', error);
			showAlert('Error de conexión', 'error');
		}
	}

	// Suspender/Reactivar tenant
	async function toggleTenantStatus(id, currentStatus) {
		const action = currentStatus ? 'suspender' : 'reactivar';
		const confirmed = await confirmAction(
			`¿Está seguro que desea ${action} este tenant?<br>` +
			`<small>${currentStatus ? 'El tenant no podrá acceder al sistema.' : 'El tenant podrá acceder nuevamente al sistema.'}</small>`,
			`${action.charAt(0).toUpperCase() + action.slice(1)} Tenant`
		);

		if (!confirmed) return;

		try {
			const response = await fetch(BASE_URL + 'admin/tenant_toggle/' + id, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: '<?= $this->security->get_csrf_token_name() ?>=<?= $this->security->get_csrf_hash() ?>'
			});

			const data = await response.json();

			if (data.ok) {
				showAlert(data.msg, 'success');
				setTimeout(() => location.reload(), 1500);
			} else {
				showAlert(data.msg || 'Error al cambiar el estado', 'error');
			}
		} catch (error) {
			console.error('Error:', error);
			showAlert('Error de conexión', 'error');
		}
	}

	// Generar QR (placeholder - implementar según tu lógica)
	function generarQR(tenantId) {
		showAlert('Función de generación de QR pendiente de implementar', 'info');
	}

	// Funciones de alerta (asumiendo que ya existen en admin.js)
	function showAlert(msg, type) {
		if (window.Swal) {
			const icons = {
				success: 'success',
				error: 'error',
				warning: 'warning',
				info: 'info'
			};
			const titles = {
				success: '¡Éxito!',
				error: 'Error',
				warning: 'Atención',
				info: 'Información'
			};
			const colors = {
				success: '#28a745',
				error: '#dc3545',
				warning: '#ffc107',
				info: '#17a2b8'
			};

			Swal.fire({
				title: titles[type] || titles.info,
				html: msg,
				icon: icons[type] || icons.info,
				confirmButtonColor: colors[type] || colors.info,
				timer: type === 'success' ? 3000 : undefined,
				timerProgressBar: type === 'success'
			});
		} else {
			alert(msg);
		}
	}

	async function confirmAction(message, title) {
		if (window.Swal) {
			const result = await Swal.fire({
				title: title || '¿Estás seguro?',
				html: message,
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: '<i class="fas fa-check"></i> Sí, confirmar',
				cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				reverseButtons: true
			});
			return result.isConfirmed;
		}
		return confirm(message.replace(/<[^>]*>/g, ''));
	}
</script>
