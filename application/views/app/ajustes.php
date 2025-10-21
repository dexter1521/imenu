<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="container-fluid">
	<!-- Encabezado -->
	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1 class="h3 mb-0 text-gray-800">
			<i class="fas fa-cog"></i> Configuración del Restaurante
		</h1>
		<button type="button" class="btn btn-success" id="btn-guardar">
			<i class="fas fa-save"></i> Guardar Cambios
		</button>
	</div>

	<!-- Loader -->
	<div id="ajustes-loader" class="text-center py-5">
		<div class="spinner-border text-primary" role="status">
			<span class="sr-only">Cargando...</span>
		</div>
	</div>

	<!-- Contenido -->
	<form id="form-ajustes" style="display: none;">
		<div class="row">
			<!-- Configuración General -->
			<div class="col-lg-6 mb-4">
				<div class="card shadow h-100">
					<div class="card-header py-3">
						<h6 class="m-0 font-weight-bold text-primary">
							<i class="fas fa-store"></i> Información General
						</h6>
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="nombre_negocio">
								Nombre del Negocio <span class="text-danger">*</span>
							</label>
							<input type="text" class="form-control" id="nombre_negocio" name="nombre_negocio" required>
							<small class="form-text text-muted">Aparecerá en el menú público</small>
						</div>

						<div class="form-group">
							<label for="telefono">Teléfono/WhatsApp</label>
							<input type="text" class="form-control" id="telefono" name="telefono" placeholder="5512345678">
							<small class="form-text text-muted">Para contacto de clientes</small>
						</div>

						<div class="form-group">
							<label for="email">Email de contacto</label>
							<input type="email" class="form-control" id="email" name="email" placeholder="contacto@restaurante.com">
						</div>

						<div class="form-group">
							<label for="direccion">Dirección</label>
							<textarea class="form-control" id="direccion" name="direccion" rows="2" placeholder="Calle, número, colonia, ciudad"></textarea>
						</div>
					</div>
				</div>
			</div>

			<!-- Personalización Visual -->
			<div class="col-lg-6 mb-4">
				<div class="card shadow h-100">
					<div class="card-header py-3">
						<h6 class="m-0 font-weight-bold text-primary">
							<i class="fas fa-palette"></i> Personalización Visual
						</h6>
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="color_primario">Color Principal del Menú</label>
							<div class="input-group">
								<input type="color" class="form-control" id="color_primario" name="color_primario" value="#F50087" style="max-width: 80px;">
								<input type="text" class="form-control" id="color_primario_hex" readonly>
							</div>
							<small class="form-text text-muted">Se aplicará al encabezado y botones</small>
						</div>

						<div class="form-group">
							<label for="logo">Logo del Restaurante</label>
							<div class="custom-file">
								<input type="file" class="custom-file-input" id="logo" accept="image/*">
								<label class="custom-file-label" for="logo">Seleccionar imagen...</label>
							</div>
							<small class="form-text text-muted">Tamaño recomendado: 200x200px</small>
							<div id="logo-preview" class="mt-2" style="display: none;">
								<img src="" alt="Logo" class="img-thumbnail" style="max-width: 150px;">
								<button type="button" class="btn btn-sm btn-danger ml-2" id="btn-remove-logo">
									<i class="fas fa-times"></i>
								</button>
							</div>
						</div>

						<div class="form-group">
							<label>Opciones de Visualización</label>
							<div class="custom-control custom-switch mb-2">
								<input type="checkbox" class="custom-control-input" id="mostrar_precios" name="mostrar_precios" checked>
								<label class="custom-control-label" for="mostrar_precios">
									<i class="fas fa-dollar-sign"></i> Mostrar Precios en el Menú
								</label>
							</div>
							<div class="custom-control custom-switch mb-2">
								<input type="checkbox" class="custom-control-input" id="mostrar_imagenes" name="mostrar_imagenes" checked>
								<label class="custom-control-label" for="mostrar_imagenes">
									<i class="fas fa-image"></i> Mostrar Imágenes de Productos
								</label>
							</div>
							<div class="custom-control custom-switch">
								<input type="checkbox" class="custom-control-input" id="aceptar_pedidos" name="aceptar_pedidos" checked>
								<label class="custom-control-label" for="aceptar_pedidos">
									<i class="fas fa-shopping-cart"></i> Aceptar Pedidos en Línea
								</label>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<!-- Configuración Regional -->
			<div class="col-lg-6 mb-4">
				<div class="card shadow">
					<div class="card-header py-3">
						<h6 class="m-0 font-weight-bold text-primary">
							<i class="fas fa-globe"></i> Configuración Regional
						</h6>
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="idioma">Idioma del Menú</label>
							<select class="form-control" id="idioma" name="idioma">
								<option value="es">🇲🇽 Español</option>
								<option value="en">🇺🇸 English</option>
								<option value="fr">🇫🇷 Français</option>
							</select>
						</div>

						<div class="form-group">
							<label for="moneda">Moneda</label>
							<select class="form-control" id="moneda" name="moneda">
								<option value="MXN">$ - Peso Mexicano (MXN)</option>
								<option value="USD">$ - Dólar Americano (USD)</option>
								<option value="EUR">€ - Euro (EUR)</option>
								<option value="GBP">£ - Libra Esterlina (GBP)</option>
							</select>
						</div>

						<div class="form-group">
							<label for="formato_precio">Formato de Precio</label>
							<select class="form-control" id="formato_precio" name="formato_precio">
								<option value="$0.00">$0.00 (Ejemplo: $19.99)</option>
								<option value="$0">$0 (Ejemplo: $20)</option>
								<option value="0.00">0.00 (Ejemplo: 19.99)</option>
								<option value="$ 0.00">$ 0.00 (Ejemplo: $ 19.99)</option>
							</select>
							<small class="form-text text-muted">Vista previa: <strong id="precio-preview">$19.99</strong></small>
						</div>

						<div class="form-group">
							<label for="zona_horaria">Zona Horaria</label>
							<select class="form-control" id="zona_horaria" name="zona_horaria">
								<option value="America/Mexico_City">Ciudad de México (GMT-6)</option>
								<option value="America/Monterrey">Monterrey (GMT-6)</option>
								<option value="America/Cancun">Cancún (GMT-5)</option>
								<option value="America/Tijuana">Tijuana (GMT-8)</option>
							</select>
						</div>
					</div>
				</div>
			</div>

			<!-- Notas y Mensajes -->
			<div class="col-lg-6 mb-4">
				<div class="card shadow">
					<div class="card-header py-3">
						<h6 class="m-0 font-weight-bold text-primary">
							<i class="fas fa-sticky-note"></i> Mensajes Personalizados
						</h6>
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="mensaje_bienvenida">Mensaje de Bienvenida</label>
							<textarea class="form-control" id="mensaje_bienvenida" name="mensaje_bienvenida" rows="2"
								placeholder="¡Bienvenido a nuestro restaurante!"></textarea>
							<small class="form-text text-muted">Aparece al inicio del menú</small>
						</div>

						<div class="form-group">
							<label for="notas_menu">Notas del Menú</label>
							<textarea class="form-control" id="notas_menu" name="notas_menu" rows="3"
								placeholder="Información adicional, horarios, políticas, etc."></textarea>
							<small class="form-text text-muted">Información adicional para clientes</small>
						</div>

						<div class="form-group">
							<label for="mensaje_pedido">Mensaje al Realizar Pedido</label>
							<textarea class="form-control" id="mensaje_pedido" name="mensaje_pedido" rows="2"
								placeholder="Gracias por tu pedido. Lo prepararemos pronto."></textarea>
							<small class="form-text text-muted">Confirmación después de ordenar</small>
						</div>

						<div class="form-group mb-0">
							<label for="pie_menu">Pie de Página</label>
							<input type="text" class="form-control" id="pie_menu" name="pie_menu"
								placeholder="© 2025 - Todos los derechos reservados">
							<small class="form-text text-muted">Texto al final del menú</small>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Horarios de Atención -->
		<div class="row">
			<div class="col-12 mb-4">
				<div class="card shadow">
					<div class="card-header py-3">
						<h6 class="m-0 font-weight-bold text-primary">
							<i class="fas fa-clock"></i> Horarios de Atención
						</h6>
					</div>
					<div class="card-body">
						<div class="table-responsive">
							<table class="table table-bordered">
								<thead>
									<tr>
										<th style="width: 200px;">Día</th>
										<th style="width: 100px;">Abierto</th>
										<th>Apertura</th>
										<th>Cierre</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td><strong>Lunes</strong></td>
										<td><input type="checkbox" class="form-check-input" id="lunes_abierto" name="lunes_abierto" checked></td>
										<td><input type="time" class="form-control form-control-sm" id="lunes_inicio" name="lunes_inicio" value="09:00"></td>
										<td><input type="time" class="form-control form-control-sm" id="lunes_fin" name="lunes_fin" value="22:00"></td>
									</tr>
									<tr>
										<td><strong>Martes</strong></td>
										<td><input type="checkbox" class="form-check-input" id="martes_abierto" name="martes_abierto" checked></td>
										<td><input type="time" class="form-control form-control-sm" id="martes_inicio" name="martes_inicio" value="09:00"></td>
										<td><input type="time" class="form-control form-control-sm" id="martes_fin" name="martes_fin" value="22:00"></td>
									</tr>
									<tr>
										<td><strong>Miércoles</strong></td>
										<td><input type="checkbox" class="form-check-input" id="miercoles_abierto" name="miercoles_abierto" checked></td>
										<td><input type="time" class="form-control form-control-sm" id="miercoles_inicio" name="miercoles_inicio" value="09:00"></td>
										<td><input type="time" class="form-control form-control-sm" id="miercoles_fin" name="miercoles_fin" value="22:00"></td>
									</tr>
									<tr>
										<td><strong>Jueves</strong></td>
										<td><input type="checkbox" class="form-check-input" id="jueves_abierto" name="jueves_abierto" checked></td>
										<td><input type="time" class="form-control form-control-sm" id="jueves_inicio" name="jueves_inicio" value="09:00"></td>
										<td><input type="time" class="form-control form-control-sm" id="jueves_fin" name="jueves_fin" value="22:00"></td>
									</tr>
									<tr>
										<td><strong>Viernes</strong></td>
										<td><input type="checkbox" class="form-check-input" id="viernes_abierto" name="viernes_abierto" checked></td>
										<td><input type="time" class="form-control form-control-sm" id="viernes_inicio" name="viernes_inicio" value="09:00"></td>
										<td><input type="time" class="form-control form-control-sm" id="viernes_fin" name="viernes_fin" value="23:00"></td>
									</tr>
									<tr>
										<td><strong>Sábado</strong></td>
										<td><input type="checkbox" class="form-check-input" id="sabado_abierto" name="sabado_abierto" checked></td>
										<td><input type="time" class="form-control form-control-sm" id="sabado_inicio" name="sabado_inicio" value="09:00"></td>
										<td><input type="time" class="form-control form-control-sm" id="sabado_fin" name="sabado_fin" value="23:00"></td>
									</tr>
									<tr>
										<td><strong>Domingo</strong></td>
										<td><input type="checkbox" class="form-check-input" id="domingo_abierto" name="domingo_abierto"></td>
										<td><input type="time" class="form-control form-control-sm" id="domingo_inicio" name="domingo_inicio" value="10:00"></td>
										<td><input type="time" class="form-control form-control-sm" id="domingo_fin" name="domingo_fin" value="20:00"></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Botones de Acción -->
		<div class="row mb-4">
			<div class="col-12">
				<button type="submit" class="btn btn-success btn-lg">
					<i class="fas fa-save"></i> Guardar Todos los Cambios
				</button>
				<button type="button" class="btn btn-secondary btn-lg ml-2" id="btn-cancelar">
					<i class="fas fa-times"></i> Cancelar
				</button>
				<button type="button" class="btn btn-outline-primary btn-lg ml-2" id="btn-vista-previa">
					<i class="fas fa-eye"></i> Vista Previa del Menú
				</button>
			</div>
		</div>
	</form>

</div> <!-- /container-fluid -->

<!-- Scripts -->
<script src="<?= base_url('assets/js/app.js') ?>"></script>
<script src="<?= base_url('assets/js/ajustes.js') ?>"></script>
