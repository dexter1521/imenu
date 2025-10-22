<?php
// ===== application/views/public/menu.php =====
// Requiere: $tenant, $cats, $prods, $aj (desde Public::menu($slug))
// Catálogo Pro con carrito, barra inferior, checkout y envío a /api/public/pedido
?>
<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?= htmlspecialchars($tenant->nombre) ?> | Menú</title>
	<link href="<?php echo base_url('assets/css/sb-admin-2.min.css'); ?>" rel="stylesheet">
	<link href="<?php echo base_url('assets/css/menu.css'); ?>" rel="stylesheet">
	<link href="<?php echo base_url('assets/vendor/fontawesome-free/css/all.min.css'); ?>" rel="stylesheet" type="text/css">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet" />
	<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
	
	<!-- Variables CSS dinámicas -->
	<style>
		:root {
			--primary-color: <?= htmlspecialchars($aj->color_primario ?? '#e91e63') ?>;
			--primary-color-rgb: <?php 
				$color = $aj->color_primario ?? '#e91e63';
				// Convertir hex a RGB
				$color = str_replace('#', '', $color);
				$r = hexdec(substr($color, 0, 2));
				$g = hexdec(substr($color, 2, 2));
				$b = hexdec(substr($color, 4, 2));
				echo "$r, $g, $b";
			?>;
		}
	</style>
</head>

<body>
	<?php
	// Símbolo de moneda desde formato/ajustes
	$fmt = isset($aj->formato_precio) && $aj->formato_precio ? $aj->formato_precio : '$0.00';
	$symbol = '$';
	if (strpos($fmt, '$') !== false) $symbol = '$';
	elseif (!empty($aj->moneda) && strtoupper($aj->moneda) === 'EUR') $symbol = '€';
	elseif (!empty($aj->moneda) && strtoupper($aj->moneda) === 'USD') $symbol = '$';

	$showPrices = !isset($aj->show_precios) || (int)$aj->show_precios === 1;
	$showImgs   = !isset($aj->show_imgs)    || (int)$aj->show_imgs === 1;

	// “Powered by iMenu”: oculto si el plan es premium (heurística simple)
	$hidePowered = isset($tenant->plan_id) && (int)$tenant->plan_id > 1;

	// WhatsApp global
	$wa = preg_replace('/\D+/', '', (string)$tenant->whatsapp);

	// Imagen por defecto
	$noImg = base_url('assets/img/200x200.jpg');  // Imagen por defecto genérica
	?>

	<header class="header">
		<div class="container">
			<div class="brand text-center">
				<?php if (!empty($tenant->logo_url)): ?>
					<img src="<?= htmlspecialchars($tenant->logo_url) ?>" alt="<?= htmlspecialchars($tenant->nombre) ?>">
				<?php endif; ?>
				<div>
					<h2 class="mb-1"><?= htmlspecialchars($tenant->nombre) ?></h2>
					<?php if (!empty($tenant->whatsapp)): ?>
						<small><i class="fab fa-whatsapp"></i> <?= htmlspecialchars($tenant->whatsapp) ?></small>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</header>

	<main class="py-4 mb-5">
		<div class="container">
			<!-- Filtros -->
			<div class="row mb-4">
				<div class="col-md-6 mb-3">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text bg-white border-right-0">
								<i class="fas fa-search text-muted"></i>
							</span>
						</div>
						<input id="search" class="form-control border-left-0" type="text" placeholder="Buscar platillos... (nombre o descripción)">
					</div>
				</div>
				<div class="col-md-6 mb-3">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text bg-white border-right-0">
								<i class="fas fa-filter text-muted"></i>
							</span>
						</div>
						<select id="category" class="form-control border-left-0">
							<option value="">Todas las categorías</option>
							<?php foreach ($cats as $c): ?>
								<option value="cat-<?= (int)$c->id ?>"><?= htmlspecialchars($c->nombre) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>

			<?php if (empty($prods)): ?>
				<div class="alert alert-info">Este restaurante aún no ha agregado productos.</div>
			<?php endif; ?>

			<!-- Grid de productos -->
			<div class="row" id="grid">
				<?php foreach ($prods as $p): ?>
					<?php
					$name = (string)$p->nombre;
					$desc = (string)$p->descripcion;
					$cat  = (int)$p->categoria_id;
					$price = (float)$p->precio;
					$img  = $showImgs ? (trim((string)$p->img_url) ?: $noImg) : $noImg;
					?>
					<div class="col-12 col-md-6 col-xl-3 mb-4 product-card"
						data-name="<?= htmlspecialchars(mb_strtolower($name, 'UTF-8')) ?>"
						data-desc="<?= htmlspecialchars(mb_strtolower($desc, 'UTF-8')) ?>"
						data-cat="cat-<?= $cat ?>">
						<div class="card card-product h-100">
							<?php if ($showImgs): ?>
								<img class="card-img-top" src="<?= htmlspecialchars($img) ?>"
									alt="<?= htmlspecialchars($name) ?>"
									onerror="this.src='<?= $noImg ?>'">
							<?php endif; ?>
							<div class="card-body d-flex flex-column">
								<h5 class="card-title mb-1"><?= htmlspecialchars($name) ?></h5>
								<?php if (strlen(trim($desc))): ?>
									<p class="card-text text-muted mb-2" style="min-height:38px;"><?= htmlspecialchars($desc) ?></p>
								<?php endif; ?>
								<?php if ($showPrices): ?>
									<div class="mb-2">
										<div class="price h5 mb-0"><?= $symbol . number_format($price, 2) ?></div>
									</div>
								<?php endif; ?>
								<button class="btn btn-primary btn-sm mt-auto btn-add"
									data-id="<?= (int)$p->id ?>"
									data-name="<?= htmlspecialchars($name) ?>"
									data-price="<?= number_format($price, 2, '.', '') ?>"
									data-cat="cat-<?= $cat ?>">
									<i class="fas fa-plus-circle"></i> Agregar
								</button>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<?php if (!$hidePowered): ?>
				<div class="text-center my-4 powered">Powered by <strong>iMenu</strong></div>
			<?php endif; ?>
		</div>
	</main>

	<!-- Barra inferior de carrito -->
	<div class="bar-cart d-none" id="barCart">
		<div class="container d-flex align-items-center justify-content-between">
			<div class="d-flex align-items-center">
				<span id="cartCount">0</span>
				<div class="ml-2">
					<small class="d-block text-muted" style="font-size: 0.75rem;">artículos</small>
					<span class="total" id="cartTotal">$0.00</span>
				</div>
			</div>
			<button class="btn btn-success" id="btnCheckout">
				<i class="fas fa-shopping-cart"></i> Ver pedido
			</button>
		</div>
	</div>

	<!-- Modal cantidad -->
	<div class="modal fade" id="qtyModal" tabindex="-1" role="dialog" aria-labelledby="qtyLabel" aria-hidden="true">
		<div class="modal-dialog modal-sm" role="document">
			<div class="modal-content">
				<div class="modal-header py-2">
					<h5 class="modal-title" id="qtyLabel">Cantidad</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body pt-3">
					<div class="mb-2"><strong id="qtyProdName"></strong></div>
					<div class="input-group">
						<div class="input-group-prepend">
							<button class="btn btn-outline-secondary" type="button" id="btnMinus">−</button>
						</div>
						<input type="number" class="form-control text-center" id="qtyInput" value="1" min="1" step="1">
						<div class="input-group-append">
							<button class="btn btn-outline-secondary" type="button" id="btnPlus">+</button>
						</div>
					</div>
					<div class="mt-2 text-right"><small>Precio: <span id="qtyPrice"></span></small></div>
				</div>
				<div class="modal-footer py-2">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
					<button type="button" class="btn btn-primary" id="qtyAddConfirm">Agregar</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal checkout -->
	<div class="modal fade" id="checkoutModal" tabindex="-1" role="dialog" aria-labelledby="chkLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header py-2">
					<h5 class="modal-title" id="chkLabel">Tu pedido</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>

				<div class="modal-body">
					<!-- Resumen -->
					<div class="table-responsive mb-3">
						<table class="table table-sm table-striped mb-0" id="tblResumen">
							<thead class="thead-light">
								<tr>
									<th>Producto</th>
									<th class="text-center">Cant.</th>
									<th class="text-right">Precio</th>
									<th class="text-right">Subtotal</th>
									<th></th>
								</tr>
							</thead>
							<tbody></tbody>
							<tfoot>
								<tr>
									<th colspan="3" class="text-right">Total:</th>
									<th class="text-right" id="resTotal">$0.00</th>
									<th></th>
								</tr>
							</tfoot>
						</table>
					</div>

					<!-- Datos del cliente -->
					<div class="row">
						<div class="col-md-6">
							<label class="mb-0">Nombre</label>
							<input type="text" class="form-control" id="cliNombre" placeholder="Tu nombre">
							<div class="form-error" id="errNombre">Ingresa tu nombre.</div>
						</div>
						<div class="col-md-6">
							<label class="mb-0">Teléfono</label>
							<input type="tel" class="form-control" id="cliTel" placeholder="10 dígitos">
							<div class="form-error" id="errTel">Ingresa un teléfono válido (10 dígitos).</div>
						</div>
					</div>

					<div class="row mt-2">
						<div class="col-md-6">
							<label class="mb-0">Método de pago</label>
							<select class="form-control" id="metodoPago">
								<option value="">Selecciona...</option>
								<option value="efectivo">Efectivo</option>
								<option value="tarjeta">Tarjeta</option>
								<option value="transferencia">Transferencia</option>
								<option value="otro">Otro</option>
							</select>
							<div class="form-error" id="errPago">Selecciona un método de pago.</div>
						</div>
					</div>

					<div class="form-error mt-3" id="errCarrito">Agrega al menos un producto.</div>
				</div>

				<div class="modal-footer">
					<div class="left text-muted small">Se enviará el pedido por WhatsApp al negocio.</div>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
					<button type="button" class="btn btn-success" id="btnEnviarPedido">Enviar pedido</button>
				</div>
			</div>
		</div>
	</div>

	<script src="<?php echo base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
	<script src="<?php echo base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		(function() {
			// ---------- Utilidades ----------
			var currency = <?= json_encode($symbol) ?>;
			
			// CSRF token para CodeIgniter
			var csrfName = '<?= $this->security->get_csrf_token_name() ?>';
			var csrfHash = '<?= $this->security->get_csrf_hash() ?>';

			function money(n) {
				n = parseFloat(n || 0);
				return currency + n.toFixed(2);
			}

			function norm(t) {
				return (t || '').toString().toLowerCase();
			}

			// ---------- Filtro y búsqueda ----------
			var search = $('#search');
			var catSel = $('#category');

			function applyFilters() {
				var q = norm(search.val());
				var c = catSel.val();
				var visible = 0;
				$('.product-card').each(function() {
					var $el = $(this);
					var okCat = !c || $el.data('cat') === c;
					var hay = ($el.data('name') + ' ' + $el.data('desc'));
					var okText = !q || hay.indexOf(q) !== -1;
					var show = okCat && okText;
					$el.toggle(show);
					if (show) visible++;
				});
				var $ex = $('#nores');
				if (visible === 0) {
					if (!$ex.length) $('#grid').prepend('<div id="nores" class="alert alert-warning w-100">No hay resultados que coincidan con tu búsqueda.</div>');
				} else {
					$ex.remove();
				}
			}
			search.on('input', applyFilters);
			catSel.on('change', applyFilters);

			// ---------- Carrito ----------
			var cart = []; // [{producto_id, nombre, precio, cantidad}]
			var $bar = $('#barCart'),
				$count = $('#cartCount'),
				$total = $('#cartTotal');

			function cartAdd(prod, qty) {
				qty = parseInt(qty || 1);
				if (qty < 1) qty = 1;
				var found = cart.find(function(x) {
					return x.producto_id === prod.producto_id;
				});
				if (found) {
					found.cantidad += qty;
				} else {
					cart.push({
						producto_id: prod.producto_id,
						nombre: prod.nombre,
						precio: prod.precio,
						cantidad: qty
					});
				}
				cartRenderBar();
			}

			function cartRemove(pid) {
				cart = cart.filter(function(x) {
					return x.producto_id !== pid;
				});
				cartRenderBar();
				renderResumen(); // por si está abierto el checkout
			}

			function cartSetQty(pid, qty) {
				qty = Math.max(1, parseInt(qty || 1));
				var it = cart.find(function(x) {
					return x.producto_id === pid;
				});
				if (it) {
					it.cantidad = qty;
				}
				cartRenderBar();
			}

			function cartTotals() {
				var items = 0,
					total = 0;
				cart.forEach(function(x) {
					items += x.cantidad;
					total += x.precio * x.cantidad;
				});
				return {
					items: items,
					total: total
				};
			}

			function cartRenderBar() {
				var t = cartTotals();
				$count.text(t.items);
				$total.text(money(t.total));
				$bar.toggleClass('d-none', t.items === 0);
			}

			// ---------- Modal Cantidad ----------
			var currentProd = null;
			$('.btn-add').on('click', function() {
				var $b = $(this);
				currentProd = {
					producto_id: parseInt($b.data('id')),
					nombre: $b.data('name'),
					precio: parseFloat($b.data('price'))
				};
				$('#qtyProdName').text(currentProd.nombre);
				$('#qtyPrice').text(money(currentProd.precio));
				$('#qtyInput').val(1);
				$('#qtyModal').modal({
					backdrop: 'static',
					keyboard: false
				});
			});
			$('#btnMinus').on('click', function() {
				var v = parseInt($('#qtyInput').val() || 1);
				v = Math.max(1, v - 1);
				$('#qtyInput').val(v);
			});
			$('#btnPlus').on('click', function() {
				var v = parseInt($('#qtyInput').val() || 1);
				$('#qtyInput').val(v + 1);
			});
			$('#qtyAddConfirm').on('click', function() {
				var q = parseInt($('#qtyInput').val() || 1);
				if (currentProd) {
					cartAdd(currentProd, q);
				}
				$('#qtyModal').modal('hide');
			});

			// ---------- Checkout ----------
			var $tblBody = $('#tblResumen tbody');
			var $resTotal = $('#resTotal');

			function renderResumen() {
				$tblBody.empty();
				var t = cartTotals();
				cart.forEach(function(x) {
					var sub = x.precio * x.cantidad;
					var row = $('<tr>');
					row.append($('<td>').text(x.nombre));
					row.append($('<td class="text-center">').append(
						$('<div class="input-group input-group-sm" style="max-width:120px;margin:0 auto;">')
						.append('<div class="input-group-prepend"><button class="btn btn-outline-secondary btn-minus" data-id="' + x.producto_id + '">−</button></div>')
						.append('<input type="number" min="1" class="form-control text-center qty-row" data-id="' + x.producto_id + '" value="' + x.cantidad + '">')
						.append('<div class="input-group-append"><button class="btn btn-outline-secondary btn-plus" data-id="' + x.producto_id + '">+</button></div>')
					));
					row.append($('<td class="text-right">').text(money(x.precio)));
					row.append($('<td class="text-right">').text(money(sub)));
					row.append($('<td class="text-right">').append('<button class="btn btn-link text-danger btn-sm btn-del" data-id="' + x.producto_id + '">Quitar</button>'));
					$tblBody.append(row);
				});
				$resTotal.text(money(t.total));
				$('#errCarrito').toggle(cart.length === 0);
			}

			$('#btnCheckout').on('click', function() {
				renderResumen();
				$('#checkoutModal').modal({
					backdrop: 'static',
					keyboard: false
				});
			});

			// Cambios de cantidad/Quitar dentro del resumen
			$tblBody.on('click', '.btn-minus', function() {
				var id = parseInt($(this).data('id'));
				var it = cart.find(function(x) {
					return x.producto_id === id;
				});
				if (!it) return;
				it.cantidad = Math.max(1, it.cantidad - 1);
				renderResumen();
				cartRenderBar();
			});
			$tblBody.on('click', '.btn-plus', function() {
				var id = parseInt($(this).data('id'));
				var it = cart.find(function(x) {
					return x.producto_id === id;
				});
				if (!it) return;
				it.cantidad = it.cantidad + 1;
				renderResumen();
				cartRenderBar();
			});
			$tblBody.on('change', '.qty-row', function() {
				var id = parseInt($(this).data('id'));
				var v = Math.max(1, parseInt($(this).val() || 1));
				cartSetQty(id, v);
				renderResumen();
			});
			$tblBody.on('click', '.btn-del', function() {
				var id = parseInt($(this).data('id'));
				cartRemove(id);
			});

			// ---------- Envío del pedido ----------
			function validarCheckout() {
				var ok = true;
				var nom = $('#cliNombre').val().trim();
				var tel = ($('#cliTel').val() || '').replace(/\D+/g, '');
				var pago = $('#metodoPago').val();

				$('#errNombre').toggle(!nom);
				if (!nom) ok = false;
				$('#errTel').toggle(!(tel.length === 10));
				if (tel.length !== 10) ok = false;
				$('#errPago').toggle(!pago);
				if (!pago) ok = false;
				$('#errCarrito').toggle(cart.length === 0);
				if (cart.length === 0) ok = false;

				return ok;
			}

			$('#btnEnviarPedido').on('click', function() {
				if (!validarCheckout()) return;

				var payload = {
					slug: <?= json_encode($tenant->slug) ?>,
					nombre: $('#cliNombre').val().trim(),
					telefono: ($('#cliTel').val() || '').replace(/\D+/g, ''),
					metodo_pago: $('#metodoPago').val(),
					items: JSON.stringify(cart.map(function(x) {
						return {
							producto_id: x.producto_id,
							cantidad: x.cantidad
						};
					}))
				};
				
				// Agregar token CSRF
				payload[csrfName] = csrfHash;

				$('#btnEnviarPedido').prop('disabled', true).text('Enviando...');
				$.ajax({
					url: '<?= base_url("api/public/pedido") ?>',
					method: 'POST',
					data: payload
				}).done(function(res) {
					// Actualizar CSRF hash para próxima petición
					if (res && res.csrf_token) {
						csrfHash = res.csrf_token;
					}
					
					if (res && res.ok) {
						if (res.whatsapp_url) {
							window.location.href = res.whatsapp_url;
						} else {
							Swal.fire({
								icon: 'success',
								title: '¡Pedido creado!',
								text: 'Pedido #' + res.pedido_id,
								confirmButtonColor: 'var(--primary-color)'
							});
						}
						// Limpia carrito
						cart = [];
						cartRenderBar();
						$('#checkoutModal').modal('hide');
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: (res && res.msg) ? res.msg : 'No se pudo crear el pedido.',
							confirmButtonColor: 'var(--primary-color)'
						});
					}
				}).fail(function(xhr) {
					Swal.fire({
						icon: 'error',
						title: 'Error de conexión',
						text: 'No se pudo enviar el pedido. Intenta de nuevo.',
						confirmButtonColor: 'var(--primary-color)'
					});
				}).always(function() {
					$('#btnEnviarPedido').prop('disabled', false).text('Enviar pedido');
				});
			});

		})();
	</script>
</body>

</html>
