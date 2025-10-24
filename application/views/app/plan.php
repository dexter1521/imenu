<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="container-fluid">
	<!-- Encabezado -->
	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1 class="h3 mb-0 text-gray-800">
			<i class="fas fa-crown"></i> Mi Plan y Suscripción
		</h1>
	</div>

	<!-- Contenedor principal -->
	<div id="plan-container">
		<div class="text-center py-5">
			<div class="spinner-border text-primary" role="status">
				<span class="sr-only">Cargando...</span>
			</div>
		</div>
	</div>

	<!-- Card de Plan Actual -->
	<div class="row" id="plan-content" style="display: none;">
		<!-- Información del Plan -->
		<div class="col-xl-4 col-lg-5 mb-4">
			<div class="card shadow h-100">
				<div class="card-header py-3 bg-gradient-primary text-white">
					<h6 class="m-0 font-weight-bold">
						<i class="fas fa-info-circle"></i> Plan Actual
					</h6>
				</div>
				<div class="card-body text-center">
					<div class="plan-icon mb-3">
						<i class="fas fa-crown fa-3x text-warning" id="plan-icon"></i>
					</div>
					<h3 class="mb-2" id="plan-nombre">--</h3>
					<h4 class="text-success mb-3" id="plan-precio">$0.00/mes</h4>
					<p class="text-muted mb-4" id="plan-descripcion">--</p>

					<div class="alert alert-info mb-4" id="suscripcion-info">
						<div class="d-flex align-items-center justify-content-center">
							<i class="fas fa-calendar-check fa-2x mr-3"></i>
							<div>
								<strong id="dias-restantes-label">-- días restantes</strong>
								<br>
								<small class="text-muted">Vence: <span id="fecha-vencimiento">--</span></small>
							</div>
						</div>
					</div>

					<div class="alert alert-warning mb-4" id="suscripcion-inactiva" style="display: none;">
						<i class="fas fa-exclamation-triangle"></i>
						<strong>Suscripción Inactiva</strong>
						<p class="mb-0 small">Contacta a soporte para activar tu plan</p>
					</div>

					<button type="button" class="btn btn-success btn-block mb-2" id="btn-upgrade">
						<i class="fas fa-arrow-up"></i> Mejorar Plan
					</button>
					<button type="button" class="btn btn-outline-primary btn-block" id="btn-soporte">
						<i class="fas fa-headset"></i> Contactar Soporte
					</button>
				</div>
			</div>
		</div>

		<!-- Uso y Límites -->
		<div class="col-xl-8 col-lg-7">
			<div class="card shadow mb-4">
				<div class="card-header py-3">
					<h6 class="m-0 font-weight-bold text-primary">
						<i class="fas fa-chart-bar"></i> Uso de Recursos
					</h6>
				</div>
				<div class="card-body">
					<p class="mb-4 text-muted">
						<i class="fas fa-info-circle"></i> 
						Monitorea el uso de tu plan para evitar alcanzar los límites
					</p>

					<!-- Categorías -->
					<div class="mb-4">
						<div class="d-flex justify-content-between align-items-center mb-2">
							<div>
								<h6 class="mb-0">
									<i class="fas fa-tags text-primary"></i> Categorías
								</h6>
								<small class="text-muted">
									<span id="uso-categorias">0</span> de <span id="limite-categorias">0</span> utilizadas
								</small>
							</div>
							<span class="badge badge-info" id="porcentaje-categorias">0%</span>
						</div>
						<div class="progress" style="height: 25px;">
							<div class="progress-bar" id="barra-categorias" role="progressbar" 
								 style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
								<span class="font-weight-bold">0%</span>
							</div>
						</div>
					</div>

					<!-- Productos -->
					<div class="mb-4">
						<div class="d-flex justify-content-between align-items-center mb-2">
							<div>
								<h6 class="mb-0">
									<i class="fas fa-box text-success"></i> Productos
								</h6>
								<small class="text-muted">
									<span id="uso-productos">0</span> de <span id="limite-productos">0</span> creados
								</small>
							</div>
							<span class="badge badge-info" id="porcentaje-productos">0%</span>
						</div>
						<div class="progress" style="height: 25px;">
							<div class="progress-bar bg-success" id="barra-productos" role="progressbar" 
								 style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
								<span class="font-weight-bold">0%</span>
							</div>
						</div>
					</div>

					<!-- Pedidos del Mes -->
					<div class="mb-4">
						<div class="d-flex justify-content-between align-items-center mb-2">
							<div>
								<h6 class="mb-0">
									<i class="fas fa-shopping-cart text-warning"></i> Pedidos del Mes
								</h6>
								<small class="text-muted">
									<span id="uso-pedidos">0</span> de <span id="limite-pedidos">0</span> pedidos
								</small>
							</div>
							<span class="badge badge-info" id="porcentaje-pedidos">0%</span>
						</div>
						<div class="progress" style="height: 25px;">
							<div class="progress-bar bg-warning" id="barra-pedidos" role="progressbar" 
								 style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
								<span class="font-weight-bold">0%</span>
							</div>
						</div>
					</div>

					<!-- Alertas de uso -->
					<div id="alerta-uso" style="display: none;">
						<div class="alert alert-warning">
							<i class="fas fa-exclamation-triangle"></i>
							<strong>¡Atención!</strong> Estás cerca de alcanzar los límites de tu plan.
							<a href="#" id="link-upgrade" class="alert-link">Considera mejorar tu plan</a>
						</div>
					</div>
				</div>
			</div>

			<!-- Comparación de Planes -->
			<div class="card shadow">
				<div class="card-header py-3">
					<h6 class="m-0 font-weight-bold text-primary">
						<i class="fas fa-layer-group"></i> Planes Disponibles
					</h6>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<table class="table table-bordered text-center">
							<thead>
								<tr>
									<th>Característica</th>
									<th>Gratis</th>
									<th>Básico</th>
									<th>Pro</th>
									<th>Premium</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="text-left"><strong>Precio</strong></td>
									<td>$0/mes</td>
									<td>$199/mes</td>
									<td>$499/mes</td>
									<td>$999/mes</td>
								</tr>
								<tr>
									<td class="text-left">Categorías</td>
									<td>3</td>
									<td>10</td>
									<td>50</td>
									<td>Ilimitado</td>
								</tr>
								<tr>
									<td class="text-left">Productos</td>
									<td>10</td>
									<td>50</td>
									<td>500</td>
									<td>Ilimitado</td>
								</tr>
								<tr>
									<td class="text-left">Pedidos/mes</td>
									<td>50</td>
									<td>500</td>
									<td>5,000</td>
									<td>Ilimitado</td>
								</tr>
								<tr>
									<td class="text-left">Soporte</td>
									<td>Email</td>
									<td>Email</td>
									<td>Prioritario</td>
									<td>24/7 VIP</td>
								</tr>
							</tbody>
						</table>
					</div>
					<p class="text-center text-muted mb-0 mt-3">
						<i class="fas fa-info-circle"></i> 
						Contacta a soporte para cambiar de plan
					</p>
				</div>
			</div>
		</div>
	</div>

</div> <!-- /container-fluid -->

<script src="<?= base_url('assets/js/app-plan.js?v=' . time()); ?>"></script>
