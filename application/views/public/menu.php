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
	<link rel="stylesheet" href="/assets/css/bootstrap.min.css">
	<style>
		body {
			background: #f7f7f9;
			color: #333
		}

		.header {
			background: <?= htmlspecialchars($tenant->color_primario ?: '#343a40') ?>;
			color: #fff;
			padding: 24px 0
		}

		.header .brand {
			display: flex;
			align-items: center;
			gap: 16px;
			justify-content: center
		}

		.brand img {
			max-height: 72px;
			object-fit: contain
		}

		.card-product {
			transition: box-shadow .2s ease
		}

		.card-product:hover {
			box-shadow: 0 8px 24px rgba(0, 0, 0, .08)
		}

		.price {
			font-weight: 700
		}

		.powered {
			font-size: 12px;
			color: #6c757d
		}

		.bar-cart {
			position: fixed;
			left: 0;
			right: 0;
			bottom: 0;
			background: #fff;
			border-top: 1px solid #e5e5e5;
			box-shadow: 0 -4px 18px rgba(0, 0, 0, .06);
			padding: 10px 0;
			z-index: 1050
		}

		.bar-cart .total {
			font-weight: 700
		}

		.btn-add {
			white-space: nowrap
		}

		.form-error {
			font-size: .85rem;
			color: #c00;
			display: none
		}

		.modal-footer .left {
			flex: 1
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
	$noImg = '/assets/img/no-image.png';
	?>

	<header class="header">
		<div class="container">
			<div class="brand text-center">
				<?php if (!empty($tenant->logo_url)): ?>
					<img src="<?= htmlspecialchars($tenant->logo_url) ?>" alt="<?= htmlspecialchars($tenant->nombre) ?>">
				<?php endif; ?>
				<div>
					<h2 class="mb-0"><?= htmlspecialchars($tenant->nombre) ?></h2>
					<?php if (!empty($tenant->whatsapp)): ?>
						<small>WhatsApp: <?= htmlspecialchars($tenant->whatsapp) ?></small>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</header>

	<main class="py-4 mb-5">
		<div class="container">
			<!-- Filtros -->
			<div class="row mb-3">
				<div class="col-md-6 mb-2">
					<input id="search" class="form-control" type="text" placeholder="Buscar platillos... (nombre o descripción)">
				</div>
				<div class="col-md-6 mb-2">
					<select id="category" class="form-control">
						<option value="">Todas las categorías</option>
						<?php foreach ($cats as $c): ?>
							<option value="cat-<?= (int)$c->id ?>"><?= htmlspecialchars($c->nombre) ?></option>
						<?php endforeach; ?>
					</select>
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
									Agregar
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
		<div class="container d-flex align-items-center">
			<div class="mr-auto">
				<span id="cartCount">0</span> artículo(s) · <span class="total" id="cartTotal">$0.00</span>
			</div>
			<button class="btn btn-success" id="btnCheckout">Ver pedido</button>
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
					<button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
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
					<button type="button" class="btn btn-light" data-dismiss="modal">Seguir viendo</button>
					<button type="button" class="btn btn-success" id="btnEnviarPedido">Enviar pedido</button>
				</div>
			</div>
		</div>
	</div>

	<script src="/assets/js/jquery.min.js"></script>
	<script src="/assets/js/bootstrap.bundle.min.js"></script>
	<script>
		(function() {
			// ---------- Utilidades ----------
			var currency = <?= json_encode($symbol) ?>;

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
				$('#qtyModal').modal('show');
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
				$('#checkoutModal').modal('show');
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

				$('#btnEnviarPedido').prop('disabled', true).text('Enviando...');
				$.ajax({
					url: '/api/public/pedido',
					method: 'POST',
					data: payload
				}).done(function(res) {
					if (res && res.ok) {
						if (res.whatsapp_url) {
							window.location.href = res.whatsapp_url;
						} else {
							alert('Pedido creado: #' + res.pedido_id);
						}
						// Limpia carrito
						cart = [];
						cartRenderBar();
						$('#checkoutModal').modal('hide');
					} else {
						alert((res && res.msg) ? res.msg : 'No se pudo crear el pedido.');
					}
				}).fail(function(xhr) {
					alert('Error enviando pedido. Intenta de nuevo.');
				}).always(function() {
					$('#btnEnviarPedido').prop('disabled', false).text('Enviar pedido');
				});
			});

		})();
	</script>
</body>

</html>
