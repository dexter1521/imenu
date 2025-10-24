(function () {
	'use strict';

	const BASE = (typeof window.IMENU_BASE_URL !== 'undefined' && window.IMENU_BASE_URL)
		? window.IMENU_BASE_URL
		: '/imenu/';

	function appUrl(path) {
		path = path || '';
		if (path.charAt(0) === '/') path = path.slice(1);
		return BASE + 'api/app/' + path;
	}

	function formatCurrency(amount) {
		return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
	}

	function formatDate(dateStr) {
		if (!dateStr) return '--';
		const d = new Date(dateStr);
		return d.toLocaleDateString('es-MX', {
			year: 'numeric',
			month: 'long',
			day: 'numeric'
		});
	}

	function getProgressColor(porcentaje) {
		if (porcentaje >= 90) return 'bg-danger';
		if (porcentaje >= 75) return 'bg-warning';
		return 'bg-success';
	}

	const Plan = {
		load: function () {
			fetch(appUrl('plan_info'), {
				method: 'GET',
				headers: { 'Content-Type': 'application/json' }
			})
				.then(res => res.json())
				.then(resp => {
					if (!resp || !resp.ok) {
						console.error('Error al cargar plan');
						document.getElementById('plan-container').innerHTML =
							'<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error al cargar información del plan</div>';
						return;
					}

					Plan.render(resp);
				})
				.catch(err => {
					console.error('Error cargando plan:', err);
					document.getElementById('plan-container').innerHTML =
						'<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error al cargar información del plan</div>';
				});
		},

		render: function (data) {
			const plan = data.plan;
			const limites = data.limites;
			const uso = data.uso;
			const porcentajes = data.porcentajes;

			// Mostrar contenido
			document.getElementById('plan-container').style.display = 'none';
			document.getElementById('plan-content').style.display = 'flex';

			// Información del plan
			document.getElementById('plan-nombre').textContent = plan.nombre;
			document.getElementById('plan-precio').textContent = formatCurrency(plan.precio) + '/mes';
			document.getElementById('plan-descripcion').textContent = plan.descripcion || 'Plan básico de iMenu';

			// Estado de suscripción
			if (plan.suscripcion_activa) {
				document.getElementById('suscripcion-info').style.display = 'block';
				document.getElementById('suscripcion-inactiva').style.display = 'none';

				const diasRestantes = plan.dias_restantes || 0;
				const diasLabel = document.getElementById('dias-restantes-label');

				diasLabel.textContent = diasRestantes + ' días restantes';

				// Color según días restantes
				const alertBox = document.getElementById('suscripcion-info');
				alertBox.className = 'alert mb-4';
				if (diasRestantes <= 7) {
					alertBox.classList.add('alert-danger');
				} else if (diasRestantes <= 15) {
					alertBox.classList.add('alert-warning');
				} else {
					alertBox.classList.add('alert-info');
				}

				document.getElementById('fecha-vencimiento').textContent = formatDate(plan.fecha_fin);
			} else {
				document.getElementById('suscripcion-info').style.display = 'none';
				document.getElementById('suscripcion-inactiva').style.display = 'block';
			}

			// Uso de recursos - Categorías
			document.getElementById('uso-categorias').textContent = uso.categorias;
			document.getElementById('limite-categorias').textContent = limites.categorias || '∞';
			document.getElementById('porcentaje-categorias').textContent = porcentajes.categorias + '%';

			const barraCategorias = document.getElementById('barra-categorias');
			barraCategorias.style.width = Math.min(porcentajes.categorias, 100) + '%';
			barraCategorias.setAttribute('aria-valuenow', porcentajes.categorias);
			barraCategorias.querySelector('span').textContent = porcentajes.categorias + '%';
			barraCategorias.className = 'progress-bar ' + getProgressColor(porcentajes.categorias);

			// Uso de recursos - Productos
			document.getElementById('uso-productos').textContent = uso.productos;
			document.getElementById('limite-productos').textContent = limites.productos || '∞';
			document.getElementById('porcentaje-productos').textContent = porcentajes.productos + '%';

			const barraProductos = document.getElementById('barra-productos');
			barraProductos.style.width = Math.min(porcentajes.productos, 100) + '%';
			barraProductos.setAttribute('aria-valuenow', porcentajes.productos);
			barraProductos.querySelector('span').textContent = porcentajes.productos + '%';
			barraProductos.className = 'progress-bar ' + getProgressColor(porcentajes.productos);

			// Uso de recursos - Pedidos
			document.getElementById('uso-pedidos').textContent = uso.pedidos_mes;
			document.getElementById('limite-pedidos').textContent = limites.pedidos_mes || '∞';
			document.getElementById('porcentaje-pedidos').textContent = porcentajes.pedidos_mes + '%';

			const barraPedidos = document.getElementById('barra-pedidos');
			barraPedidos.style.width = Math.min(porcentajes.pedidos_mes, 100) + '%';
			barraPedidos.setAttribute('aria-valuenow', porcentajes.pedidos_mes);
			barraPedidos.querySelector('span').textContent = porcentajes.pedidos_mes + '%';
			barraPedidos.className = 'progress-bar ' + getProgressColor(porcentajes.pedidos_mes);

			// Mostrar alerta si algún uso está por encima del 80%
			const maxUso = Math.max(porcentajes.categorias, porcentajes.productos, porcentajes.pedidos_mes);
			if (maxUso >= 80) {
				document.getElementById('alerta-uso').style.display = 'block';
			} else {
				document.getElementById('alerta-uso').style.display = 'none';
			}
		},

		bindUI: function () {
			// Botón Upgrade
			document.getElementById('btn-upgrade').addEventListener('click', function () {
				alert('📧 Contacta a soporte@imenu.com para mejorar tu plan\n\nTe responderemos en menos de 24 horas.');
			});

			// Botón Soporte
			document.getElementById('btn-soporte').addEventListener('click', function () {
				alert('📞 Soporte iMenu\n\nEmail: soporte@imenu.com\nWhatsApp: +52 55 1234 5678\nHorario: Lun-Vie 9am-6pm');
			});

			// Link upgrade desde alerta
			const linkUpgrade = document.getElementById('link-upgrade');
			if (linkUpgrade) {
				linkUpgrade.addEventListener('click', function (e) {
					e.preventDefault();
					document.getElementById('btn-upgrade').click();
				});
			}
		},

		init: function () {
			Plan.bindUI();
			Plan.load();
		}
	};

	// Inicializar
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', Plan.init);
	} else {
		Plan.init();
	}

})();
