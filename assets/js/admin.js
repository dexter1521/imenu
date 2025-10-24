// admin.js - consuma endpoints de Admin.php y renderiza tablas en vistas admin
(function () {
	'use strict';

	// Obtener base URL desde variable global (definida en header.php)
	const BASE_URL = window.IMENU_BASE_URL || '';

	// Función auxiliar para construir URLs
	function url(path) {
		return BASE_URL + path;
	}

	const api = {
		tenants: url('TenantsService/tenants'),
		tenant_create: url('TenantsService/tenant_create'),
		tenant_update: (id) => url('TenantsService/tenant_update/' + encodeURIComponent(id)),
		tenant_delete: (id) => url('TenantsService/tenant_delete/' + encodeURIComponent(id)),
		tenant_toggle: (id) => url('TenantsService/tenant_toggle/' + encodeURIComponent(id)),
		tenant_show: (id) => url('TenantsService/tenant_show/' + encodeURIComponent(id)),
		planes: url('PlanService/planes'),
		plan_create: url('PlanService/plan_create'),
		plan_update: (id) => url('PlanService/plan_update/' + encodeURIComponent(id)),
		plan_delete: (id) => url('PlanService/plan_delete/' + encodeURIComponent(id)),
		pagos: url('PagosService/pagos'),
		pago_stats: url('PagosService/pago_stats'),
		pago_detail: (id) => url('PagosService/pago_detail/' + encodeURIComponent(id)),
		pago_export: url('PagosService/pago_export'),
		suscripciones: url('SuscripcionesService/suscripciones'),
		suscripcion_create: url('SuscripcionesService/suscripcion_create'),
		suscripcion_update: (id) => url('SuscripcionesService/suscripcion_update/' + encodeURIComponent(id)),
		suscripcion_delete: (id) => url('SuscripcionesService/suscripcion_delete/' + encodeURIComponent(id)),
		tenant_suscripciones: (id) => url('SuscripcionesService/tenant_suscripciones/' + encodeURIComponent(id)),
		dashboard_stats: url('admin/dashboard_stats'),
		auth_login: url('adminpanel/login')
	};

	function getAuthHeaders() {
		const token = localStorage.getItem('imenu_token');
		const headers = {};
		if (token) headers['Authorization'] = 'Bearer ' + token;
		return headers;
	}

	function clearAuthAndRedirect() {
		// eliminar token de storage y cookie
		try { localStorage.removeItem('imenu_token'); localStorage.removeItem('imenu_role'); localStorage.removeItem('imenu_tenant'); } catch (e) { }
		document.cookie = 'imenu_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
		// redirigir al login con indicador de expiración
		const loginUrl = (window.IMENU && window.IMENU.routes && window.IMENU.routes.login) ? window.IMENU.routes.login : api.auth_login;
		window.location.href = loginUrl + (loginUrl.indexOf('?') === -1 ? '?expired=1' : '&expired=1');
	}

	// Función para actualizar el token CSRF desde la cookie
	function updateCsrfToken() {
		if (!window.IMENU_CSRF_TOKEN_NAME) return;

		const cookieName = window.IMENU && window.IMENU.csrf && window.IMENU.csrf.cookie_name
			? window.IMENU.csrf.cookie_name
			: 'csrf_cookie_name';

		try {
			const match = document.cookie.match(new RegExp('(^|; )' + cookieName.replace(/([.*+?^${}()|[\]\\])/g, '\\$1') + '=([^;]*)'));
			if (match) {
				const newToken = decodeURIComponent(match[2]);
				window.IMENU_CSRF_TOKEN_VALUE = newToken;
				if (window.IMENU && window.IMENU.csrf) {
					window.IMENU.csrf.hash = newToken;
				}
			}
		} catch (e) {
			console.warn('Error al actualizar token CSRF:', e);
		}
	}

	async function fetchJson(url, opts = {}) {
		opts.headers = Object.assign({}, opts.headers || {}, getAuthHeaders());

		// Si es POST, agregar token CSRF al body
		if (opts.method === 'POST' && window.IMENU_CSRF_TOKEN_NAME && window.IMENU_CSRF_TOKEN_VALUE) {
			if (!opts.body) {
				const params = new URLSearchParams();
				params.append(window.IMENU_CSRF_TOKEN_NAME, window.IMENU_CSRF_TOKEN_VALUE);
				opts.body = params.toString();
				opts.headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
			} else if (typeof opts.body === 'string') {
				const params = new URLSearchParams(opts.body);
				params.append(window.IMENU_CSRF_TOKEN_NAME, window.IMENU_CSRF_TOKEN_VALUE);
				opts.body = params.toString();
			}
		}

		// Enviar cookies (cookie HttpOnly) para autenticación
		if (!opts.credentials) opts.credentials = 'same-origin';
		const res = await fetch(url, opts);

		// Actualizar token CSRF después de cada petición
		updateCsrfToken();

		if (!res.ok) {
			if (res.status === 401) {
				// token expirado o no autorizado: cerrar sesión automáticamente
				try { /* consume body to allow devtools inspect */ await res.text(); } catch (e) { }
				clearAuthAndRedirect();
				return; // no se retorna json
			}
			const txt = await res.text();
			let json = null;
			try { json = JSON.parse(txt); } catch (e) { }
			throw new Error((json && json.msg) ? json.msg : res.status + ' ' + res.statusText);
		}
		return res.json();
	}

	// Util: enviar formulario como application/x-www-form-urlencoded
	async function postForm(url, payload) {
		const params = new URLSearchParams();

		// Agregar token CSRF si está disponible
		if (window.IMENU_CSRF_TOKEN_NAME && window.IMENU_CSRF_TOKEN_VALUE) {
			params.append(window.IMENU_CSRF_TOKEN_NAME, window.IMENU_CSRF_TOKEN_VALUE);
		}

		for (const k in payload) {
			if (payload[k] === null || payload[k] === undefined) continue;
			params.append(k, payload[k]);
		}
		const headers = Object.assign({ 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, getAuthHeaders());
		const fetchOpts = { method: 'POST', headers: headers, body: params.toString() };
		if (!fetchOpts.credentials) fetchOpts.credentials = 'same-origin';
		const res = await fetch(url, fetchOpts);

		// Actualizar token CSRF después de cada petición
		updateCsrfToken();

		if (!res.ok) {
			if (res.status === 401) {
				await res.text();
				clearAuthAndRedirect();
				return;
			}
			const txt = await res.text();
			let json = null;
			try { json = JSON.parse(txt); } catch (e) { }
			throw new Error((json && json.msg) ? json.msg : res.status + ' ' + res.statusText);
		}
		return res.json();
	}

	/**
	 * Mostrar alerta con SweetAlert2 mejorado
	 * @param {string} msg - Mensaje a mostrar
	 * @param {string} type - Tipo de alerta: 'success', 'error', 'warning', 'info'
	 */
	function showAlert(msg, type = 'info') {
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
				confirmButtonText: 'OK',
				confirmButtonColor: colors[type] || colors.info,
				timer: type === 'success' ? 3000 : undefined,
				timerProgressBar: type === 'success',
				showClass: {
					popup: 'animate__animated animate__fadeInDown'
				},
				hideClass: {
					popup: 'animate__animated animate__fadeOutUp'
				}
			});
			return;
		}
		// Fallback para cuando SweetAlert2 no está disponible
		const el = document.getElementById('admin-alert');
		if (!el) return;
		el.innerText = msg;
		el.className = 'alert alert-' + (type === 'error' ? 'danger' : type);
		el.style.display = 'block';
		setTimeout(() => { el.style.display = 'none'; }, 4000);
	}

	/**
	 * Confirmación amigable con SweetAlert2 mejorado
	 * @param {string} message - Mensaje de confirmación
	 * @param {string} title - Título del diálogo (opcional)
	 * @returns {Promise<boolean>} true si el usuario confirma
	 */
	function confirmAction(message, title = '¿Estás seguro?') {
		if (window.Swal) {
			return Swal.fire({
				title: title,
				html: message,
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: '<i class="fas fa-check"></i> Sí, confirmar',
				cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				reverseButtons: true,
				showClass: {
					popup: 'animate__animated animate__zoomIn'
				},
				hideClass: {
					popup: 'animate__animated animate__zoomOut'
				}
			}).then(res => !!res.isConfirmed);
		}
		return Promise.resolve(confirm(message));
	}

	// Tenants
	async function fetchTenants() {
		const data = await fetchJson(api.tenants);
		if (!data.ok) throw new Error('Error cargando tenants');
		renderTenants(data.data || []);
	}

	function renderTenants(rows) {
		const tbody = document.getElementById('tenants-tbody');
		if (!tbody) return;
		tbody.innerHTML = '';
		rows.forEach(r => {
			const tr = document.createElement('tr');
			tr.innerHTML = `
                <td>${r.id}</td>
                <td>${escapeHtml(r.nombre)}</td>
                <td>${escapeHtml(r.slug || '')}</td>
                <td>${r.plan_nombre ? '<span class="badge badge-success">ID:' + r.plan_nombre + '</span>' : '<span class="badge badge-secondary">-</span>'}</td>
                <td>${r.activo == 1 ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-secondary">Inactivo</span>'}</td>
                <td>${r.created_at || ''}</td>
                <td class="text-right">
					<button class="btn btn-sm btn-info btn-tenant-show" data-id="${r.id}" title="Ver Ficha"><i class="fas fa-eye"></i></button>
					<button class="btn btn-sm btn-primary btn-tenant-edit" data-id="${r.id}">Editar</button>
					<button class="btn btn-sm btn-secondary btn-tenant-qr" data-id="${r.id}">Ver QR</button>
					<button class="btn btn-sm btn-warning btn-tenant-toggle" data-id="${r.id}">${r.activo == 1 ? 'Suspender' : 'Activar'}</button>
					<button class="btn btn-sm btn-danger btn-tenant-delete" data-id="${r.id}">Eliminar</button>
                </td>
            `;
			tbody.appendChild(tr);
		});

		// attach edit/delete/toggle/qr handlers
		tbody.querySelectorAll('.btn-tenant-show').forEach(b => b.addEventListener('click', onTenantShow));
		tbody.querySelectorAll('.btn-tenant-show').forEach(b => b.addEventListener('click', onTenantShow));
		tbody.querySelectorAll('.btn-tenant-edit').forEach(b => b.addEventListener('click', onTenantEdit));
		tbody.querySelectorAll('.btn-tenant-delete').forEach(b => b.addEventListener('click', onTenantDelete));
		tbody.querySelectorAll('.btn-tenant-toggle').forEach(b => b.addEventListener('click', onTenantToggle));
		tbody.querySelectorAll('.btn-tenant-qr').forEach(b => b.addEventListener('click', onTenantQR));
	}

	// Planes
	async function fetchPlanes() {
		const data = await fetchJson(api.planes);
		if (!data.ok) throw new Error('Error cargando planes');
		renderPlanes(data.data || []);
	}

	function renderPlanes(rows) {
		const tbody = document.getElementById('planes-tbody');
		if (!tbody) return;
		tbody.innerHTML = '';
		rows.forEach(r => {
			const tr = document.createElement('tr');
			tr.innerHTML = `
                <td>${r.id}</td>
                <td><span class="badge badge-info">${escapeHtml(r.nombre)}</span></td>
                <td>$${parseFloat(r.precio_mensual || 0).toFixed(2)}</td>
                <td>${r.limite_categorias || 0}</td>
                <td>${r.limite_items || 0}</td>
                <td>${r.ads ? '<span class="text-warning">Sí</span>' : '<span class="text-success">No</span>'}</td>
                <td>--</td>
                <td>
					<button class="btn btn-sm btn-primary btn-plan-edit" data-id="${r.id}">Editar</button>
					<button class="btn btn-sm btn-danger btn-plan-delete" data-id="${r.id}">Eliminar</button>
                </td>
            `;
			tbody.appendChild(tr);
		});

		// attach handlers
		tbody.querySelectorAll('.btn-plan-edit').forEach(b => b.addEventListener('click', onPlanEdit));
		tbody.querySelectorAll('.btn-plan-delete').forEach(b => b.addEventListener('click', onPlanDelete));
	}

	// Tenant edit/delete handlers
	function onTenantShow(e) {
		const id = e.currentTarget.getAttribute('data-id');
		// Navegar a la nueva vista de ficha de tenant
		window.location.href = api.tenant_show(id);
	}

	function onTenantEdit(e) {
		const id = e.currentTarget.getAttribute('data-id');
		// fetch tenant details from current table rows or from server
		fetchJson(api.tenants).then(d => {
			const t = (d.data || []).find(x => String(x.id) === String(id));
			if (!t) return showAlert('Tenant no encontrado', 'error');
			// fill form
			const form = document.getElementById('tenant-form');
			if (!form) return;
			form.querySelector('#tenant-id').value = t.id;
			form.querySelector('#tenant-nombre').value = t.nombre || '';
			form.querySelector('#tenant-slug').value = t.slug || '';
			form.querySelector('#tenant-whatsapp').value = t.whatsapp || '';
			// Cargar y seleccionar el plan
			const planSelect = form.querySelector('#tenant-plan-id');
			if (planSelect) {
				fetchJson(api.planes).then(planesRes => {
					populatePlanSelect(planSelect, planesRes.data || []);
					planSelect.value = t.plan_id || '';
				});
			}
			form.querySelector('#tenant-activo').checked = t.activo == 1;
			if (window.jQuery && $('#tenantModal').modal) $('#tenantModal').modal('show');
		}).catch(err => showAlert(err.message, 'error'));
	}

	async function onTenantDelete(e) {
		const id = e.currentTarget.getAttribute('data-id');
		const ok = await confirmAction(
			'Esta acción eliminará el tenant y <strong>todos sus datos asociados</strong> (usuarios, productos, categorías, pedidos, etc.).<br><br>Esta acción <strong>no se puede deshacer</strong>.',
			'¿Eliminar tenant permanentemente?'
		);
		if (!ok) return;
		fetchJson(api.tenant_delete(id), { method: 'POST' }).then(res => {
			if (res.ok) {
				showAlert(res.msg || '✓ Tenant eliminado correctamente', 'success');
				fetchTenants();
			}
		}).catch(err => showAlert(err.message, 'error'));
	}

	async function onTenantToggle(e) {
		const id = e.currentTarget.getAttribute('data-id');
		const ok = await confirmAction(
			'Esto cambiará el estado activo/inactivo del tenant.<br>Un tenant inactivo no podrá acceder al sistema.',
			'¿Cambiar estado del tenant?'
		);
		if (!ok) return;
		fetchJson(api.tenant_toggle(id), { method: 'POST' }).then(res => {
			if (res.ok) {
				showAlert(res.msg || '✓ Estado actualizado correctamente', 'success');
				fetchTenants();
			}
		}).catch(err => showAlert(err.message, 'error'));
	}

	function onTenantQR(e) {
		const id = e.currentTarget.getAttribute('data-id');
		// Abrir la ruta de QR (asumiendo uploads/tenants/{id}/qr.png)
		const url = '/uploads/tenants/' + encodeURIComponent(id) + '/qr.png';
		window.open(url, '_blank');
	}

	// Plan edit/delete handlers
	function onPlanEdit(e) {
		const id = e.currentTarget.getAttribute('data-id');
		fetchJson(api.planes).then(d => {
			const p = (d.data || []).find(x => String(x.id) === String(id));
			if (!p) return showAlert('Plan no encontrado', 'error');
			const form = document.getElementById('plan-form');
			if (!form) return;
			form.querySelector('#plan-id').value = p.id;
			form.querySelector('#plan-nombre').value = p.nombre || '';
			form.querySelector('#plan-precio').value = p.precio_mensual || 0;
			form.querySelector('#plan-cats').value = p.limite_categorias || 0;
			form.querySelector('#plan-items').value = p.limite_items || 0;
			form.querySelector('#plan-ads').checked = p.ads == 1;
			if (window.jQuery && $('#planModal').modal) $('#planModal').modal('show');
		}).catch(err => showAlert(err.message, 'error'));
	}

	async function onPlanDelete(e) {
		const id = e.currentTarget.getAttribute('data-id');
		const ok = await confirmAction(
			'Al eliminar este plan, los tenants asociados quedarán sin plan activo.<br><br>¿Deseas continuar?',
			'¿Eliminar plan?'
		);
		if (!ok) return;
		fetchJson(api.plan_delete(id), { method: 'POST' }).then(res => {
			if (res.ok) {
				showAlert('✓ Plan eliminado correctamente', 'success');
				fetchPlanes();
			}
		}).catch(err => showAlert(err.message, 'error'));
	}

	// Pagos
	// ===== DASHBOARD =====

	let ingresosChartInstance = null; // Guardar instancia de Chart

	/**
	 * Fetch estadísticas del dashboard
	 */
	async function fetchDashboardStats() {
		try {
			const data = await fetchJson(api.dashboard_stats);
			if (!data.ok) throw new Error('Error cargando estadísticas');

			renderDashboardStats(data.data);
		} catch (err) {
			showAlert(err.message, 'error');
		}
	}

	/**
	 * Renderizar todas las estadísticas del dashboard
	 */
	function renderDashboardStats(stats) {
		// KPIs Principales
		updateKPICards(stats);

		// Gráfica de ingresos
		renderIngresosChart(stats.grafica_ingresos || []);

		// Planes populares
		renderPlanesPopulares(stats.planes_populares || []);

		// Resúmenes detallados
		updateResumenTenants(stats.tenants);
		updateResumenIngresos(stats.ingresos);
		updateResumenPedidos(stats.pedidos);
	}

	/**
	 * Actualizar tarjetas de KPIs
	 */
	function updateKPICards(stats) {
		// Tenants
		document.getElementById('kpi-tenants-activos').textContent = stats.tenants.activos || 0;
		document.getElementById('kpi-tenants-total').textContent = stats.tenants.total || 0;

		// Ingresos
		document.getElementById('kpi-ingresos-mes').textContent = '$' + parseFloat(stats.ingresos.mes_actual || 0).toFixed(2);

		// Crecimiento
		const crecimiento = stats.ingresos.crecimiento_porcentaje || 0;
		const crecimientoElem = document.getElementById('kpi-crecimiento');
		if (crecimiento >= 0) {
			crecimientoElem.innerHTML = '<i class="fas fa-arrow-up text-success"></i> ' + Math.abs(crecimiento) + '%';
			crecimientoElem.className = 'text-xs mt-1 text-success';
		} else {
			crecimientoElem.innerHTML = '<i class="fas fa-arrow-down text-danger"></i> ' + Math.abs(crecimiento) + '%';
			crecimientoElem.className = 'text-xs mt-1 text-danger';
		}

		// Suscripciones
		document.getElementById('kpi-suscripciones-activas').textContent = stats.suscripciones.activas || 0;
		document.getElementById('kpi-suscripciones-expirando').textContent = stats.suscripciones.expirando_pronto || 0;

		// Pedidos
		document.getElementById('kpi-pedidos-mes').textContent = stats.pedidos.mes_actual || 0;
		document.getElementById('kpi-pedidos-total').textContent = stats.pedidos.total || 0;

		// Pagos
		document.getElementById('kpi-pagos-exitosos').textContent = stats.pagos.pagos_exitosos || 0;
		document.getElementById('kpi-pagos-pendientes').textContent = stats.pagos.pagos_pendientes || 0;

		// Métricas generales
		document.getElementById('kpi-tasa-retencion').textContent = (stats.metricas_generales.tasa_retencion || 0) + '%';
		document.getElementById('kpi-ingreso-promedio').textContent = '$' + parseFloat(stats.metricas_generales.ingreso_promedio_por_tenant || 0).toFixed(2);
	}

	/**
	 * Renderizar gráfica de ingresos con Chart.js
	 */
	function renderIngresosChart(data) {
		const canvas = document.getElementById('ingresosChart');
		if (!canvas) return;

		const ctx = canvas.getContext('2d');

		// Destruir instancia anterior si existe
		if (ingresosChartInstance) {
			ingresosChartInstance.destroy();
		}

		// Preparar datos
		const labels = data.map(item => {
			const [year, month] = item.mes.split('-');
			const monthNames = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
			return monthNames[parseInt(month) - 1] + ' ' + year;
		});

		const ingresos = data.map(item => parseFloat(item.ingresos || 0));
		const pagos = data.map(item => parseInt(item.pagos_exitosos || 0));

		// Crear gráfica
		ingresosChartInstance = new Chart(ctx, {
			type: 'line',
			data: {
				labels: labels,
				datasets: [{
					label: 'Ingresos ($)',
					data: ingresos,
					borderColor: '#4e73df',
					backgroundColor: 'rgba(78, 115, 223, 0.1)',
					borderWidth: 2,
					fill: true,
					tension: 0.4,
					yAxisID: 'y'
				}, {
					label: 'Pagos Exitosos',
					data: pagos,
					borderColor: '#1cc88a',
					backgroundColor: 'rgba(28, 200, 138, 0.1)',
					borderWidth: 2,
					fill: true,
					tension: 0.4,
					yAxisID: 'y1'
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						display: true,
						position: 'bottom'
					},
					tooltip: {
						mode: 'index',
						intersect: false,
						callbacks: {
							label: function (context) {
								let label = context.dataset.label || '';
								if (label) label += ': ';
								if (context.dataset.yAxisID === 'y') {
									label += '$' + context.parsed.y.toFixed(2);
								} else {
									label += context.parsed.y;
								}
								return label;
							}
						}
					}
				},
				scales: {
					y: {
						type: 'linear',
						display: true,
						position: 'left',
						ticks: {
							callback: function (value) {
								return '$' + value.toFixed(0);
							}
						},
						title: {
							display: true,
							text: 'Ingresos ($)'
						}
					},
					y1: {
						type: 'linear',
						display: true,
						position: 'right',
						grid: {
							drawOnChartArea: false
						},
						title: {
							display: true,
							text: 'Cantidad de Pagos'
						}
					}
				}
			}
		});
	}

	/**
	 * Renderizar planes más populares
	 */
	function renderPlanesPopulares(planes) {
		const container = document.getElementById('planes-populares-container');
		if (!container) return;

		if (planes.length === 0) {
			container.innerHTML = '<p class="text-muted text-center">No hay planes disponibles</p>';
			return;
		}

		let html = '';
		planes.forEach((plan, index) => {
			const percentage = plan.tenant_count > 0 ? 100 : 0;
			const colorClass = index === 0 ? 'success' : index === 1 ? 'info' : 'warning';

			html += `
				<div class="mb-3">
					<h4 class="small font-weight-bold">
						${escapeHtml(plan.nombre)}
						<span class="float-right">${plan.tenant_count || 0} tenants</span>
					</h4>
					<div class="progress">
						<div class="progress-bar bg-${colorClass}" role="progressbar" 
							style="width: ${percentage}%" 
							aria-valuenow="${plan.tenant_count}" 
							aria-valuemin="0" 
							aria-valuemax="100">
						</div>
					</div>
					<small class="text-muted">$${parseFloat(plan.precio_mensual || 0).toFixed(2)}/mes</small>
				</div>
			`;
		});

		container.innerHTML = html;
	}

	/**
	 * Actualizar resumen de tenants
	 */
	function updateResumenTenants(tenants) {
		const total = tenants.total || 0;
		const activos = tenants.activos || 0;
		const suspendidos = tenants.suspendidos || 0;

		const activosPorc = total > 0 ? Math.round((activos / total) * 100) : 0;
		const suspendidosPorc = total > 0 ? Math.round((suspendidos / total) * 100) : 0;

		document.getElementById('tenants-activos-porcentaje').textContent = activosPorc + '%';
		document.getElementById('tenants-activos-barra').style.width = activosPorc + '%';

		document.getElementById('tenants-suspendidos-porcentaje').textContent = suspendidosPorc + '%';
		document.getElementById('tenants-suspendidos-barra').style.width = suspendidosPorc + '%';

		document.getElementById('tenants-nuevos-mes').textContent = tenants.nuevos_mes || 0;
		document.getElementById('tenants-nuevos-semana').textContent = tenants.nuevos_semana || 0;
	}

	/**
	 * Actualizar resumen de ingresos
	 */
	function updateResumenIngresos(ingresos) {
		document.getElementById('ingresos-total').textContent = '$' + parseFloat(ingresos.total || 0).toFixed(2);
		document.getElementById('ingresos-mes-actual').textContent = '$' + parseFloat(ingresos.mes_actual || 0).toFixed(2);
		document.getElementById('ingresos-mes-anterior').textContent = '$' + parseFloat(ingresos.mes_anterior || 0).toFixed(2);
		document.getElementById('ingresos-promedio-diario').textContent = '$' + parseFloat(ingresos.promedio_diario || 0).toFixed(2);

		// Proyección mensual basada en promedio diario
		const proyeccion = (ingresos.promedio_diario || 0) * 30;
		document.getElementById('ingresos-proyeccion').textContent = '$' + proyeccion.toFixed(2);
	}

	/**
	 * Actualizar resumen de pedidos
	 */
	function updateResumenPedidos(pedidos) {
		document.getElementById('pedidos-total-sistema').textContent = pedidos.total || 0;
		document.getElementById('pedidos-mes-actual').textContent = pedidos.mes_actual || 0;
		document.getElementById('pedidos-ultima-semana').textContent = pedidos.ultima_semana || 0;
		document.getElementById('pedidos-promedio-diario').textContent = parseFloat(pedidos.promedio_diario || 0).toFixed(1);
	}

	// ===== PAGOS =====

	/**
	 * Fetch pagos con filtros opcionales
	 */
	async function fetchPagos(filters = {}) {
		// Construir query string
		const params = new URLSearchParams();
		if (filters.tenant_id) params.append('tenant_id', filters.tenant_id);
		if (filters.status) params.append('status', filters.status);
		if (filters.metodo) params.append('metodo', filters.metodo);
		if (filters.fecha_inicio) params.append('fecha_inicio', filters.fecha_inicio);
		if (filters.fecha_fin) params.append('fecha_fin', filters.fecha_fin);
		if (filters.concepto) params.append('concepto', filters.concepto);

		const queryString = params.toString();
		const url = queryString ? api.pagos + '?' + queryString : api.pagos;

		const data = await fetchJson(url);
		if (!data.ok) throw new Error('Error cargando pagos');

		renderPagos(data.data || []);

		// Actualizar estadísticas
		fetchPagosStats(filters);
	}

	/**
	 * Fetch estadísticas de pagos
	 */
	async function fetchPagosStats(filters = {}) {
		const params = new URLSearchParams();
		if (filters.fecha_inicio) params.append('fecha_inicio', filters.fecha_inicio);
		if (filters.fecha_fin) params.append('fecha_fin', filters.fecha_fin);
		if (filters.tenant_id) params.append('tenant_id', filters.tenant_id);

		const queryString = params.toString();
		const url = queryString ? api.pago_stats + '?' + queryString : api.pago_stats;

		const data = await fetchJson(url);
		if (data.ok && data.data) {
			updatePagosStats(data.data);
		}
	}

	/**
	 * Actualizar tarjetas de estadísticas
	 */
	function updatePagosStats(stats) {
		const ingresosMes = document.getElementById('stat-ingresos-mes');
		const pagosExitosos = document.getElementById('stat-pagos-exitosos');
		const pagosPendientes = document.getElementById('stat-pagos-pendientes');
		const pagosFallidos = document.getElementById('stat-pagos-fallidos');

		if (ingresosMes) ingresosMes.textContent = '$' + parseFloat(stats.ingresos_mes || 0).toFixed(2);
		if (pagosExitosos) pagosExitosos.textContent = stats.pagos_exitosos || 0;
		if (pagosPendientes) pagosPendientes.textContent = stats.pagos_pendientes || 0;
		if (pagosFallidos) pagosFallidos.textContent = stats.pagos_fallidos || 0;
	}

	/**
	 * Renderizar tabla de pagos con badges de estado
	 */
	function renderPagos(rows) {
		const tbody = document.getElementById('pagos-tbody');
		if (!tbody) return;
		tbody.innerHTML = '';

		if (rows.length === 0) {
			tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">No hay pagos para mostrar</td></tr>';
			return;
		}

		rows.forEach(r => {
			const tr = document.createElement('tr');

			// Badge de estado
			let statusBadge = '';
			if (r.status === 'pagado') {
				statusBadge = '<span class="badge badge-success">Pagado</span>';
			} else if (r.status === 'pendiente') {
				statusBadge = '<span class="badge badge-warning">Pendiente</span>';
			} else if (r.status === 'fallido') {
				statusBadge = '<span class="badge badge-danger">Fallido</span>';
			} else {
				statusBadge = '<span class="badge badge-secondary">' + escapeHtml(r.status) + '</span>';
			}

			tr.innerHTML = `
                <td>${r.id}</td>
                <td>
                    <strong>${escapeHtml(r.tenant_nombre || 'N/A')}</strong><br>
                    <small class="text-muted">${escapeHtml(r.tenant_slug || '')}</small>
                </td>
                <td>${escapeHtml(r.concepto || '')}</td>
                <td class="text-right font-weight-bold text-success">$${parseFloat(r.monto || 0).toFixed(2)}</td>
                <td>
                    <span class="badge badge-info">${escapeHtml(r.metodo || '')}</span>
                </td>
                <td><small>${escapeHtml(r.referencia || 'N/A')}</small></td>
                <td>${statusBadge}</td>
                <td><small>${formatDate(r.fecha) || ''}</small></td>
                <td>
                    <button class="btn btn-sm btn-info btn-ver-pago" data-id="${r.id}" title="Ver detalles">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            `;
			tbody.appendChild(tr);
		});

		// Adjuntar eventos a botones
		document.querySelectorAll('.btn-ver-pago').forEach(btn => {
			btn.addEventListener('click', onVerPago);
		});
	}

	/**
	 * Ver detalles de un pago
	 */
	async function onVerPago(e) {
		e.preventDefault();
		const id = e.currentTarget.getAttribute('data-id');
		if (!id) return;

		try {
			const data = await fetchJson(api.pago_detail(id));
			if (!data.ok || !data.data) {
				showAlert('Error cargando detalles del pago', 'error');
				return;
			}

			mostrarDetallePago(data.data);
		} catch (err) {
			showAlert(err.message, 'error');
		}
	}

	/**
	 * Mostrar modal con detalles completos del pago
	 */
	function mostrarDetallePago(pago) {
		// Información del pago
		document.getElementById('pago-detalle-id').textContent = pago.id;
		document.getElementById('pago-detalle-concepto').textContent = pago.concepto || 'N/A';
		document.getElementById('pago-detalle-monto').textContent = '$' + parseFloat(pago.monto || 0).toFixed(2);
		document.getElementById('pago-detalle-metodo').textContent = pago.metodo || 'N/A';
		document.getElementById('pago-detalle-referencia').textContent = pago.referencia || 'N/A';

		// Badge de estado
		const estadoElem = document.getElementById('pago-detalle-estado');
		if (pago.status === 'pagado') {
			estadoElem.innerHTML = '<span class="badge badge-success badge-lg">Pagado</span>';
		} else if (pago.status === 'pendiente') {
			estadoElem.innerHTML = '<span class="badge badge-warning badge-lg">Pendiente</span>';
		} else if (pago.status === 'fallido') {
			estadoElem.innerHTML = '<span class="badge badge-danger badge-lg">Fallido</span>';
		} else {
			estadoElem.textContent = pago.status || 'N/A';
		}

		document.getElementById('pago-detalle-fecha').textContent = formatDate(pago.fecha) || 'N/A';
		document.getElementById('pago-detalle-notas').textContent = pago.notas || 'Sin notas';

		// Información del tenant
		document.getElementById('pago-detalle-tenant-nombre').textContent = pago.tenant_nombre || 'N/A';
		document.getElementById('pago-detalle-tenant-email').textContent = pago.tenant_email || 'N/A';
		document.getElementById('pago-detalle-tenant-slug').textContent = pago.tenant_slug || 'N/A';

		const tenantEstadoElem = document.getElementById('pago-detalle-tenant-estado');
		if (pago.tenant_activo == 1) {
			tenantEstadoElem.innerHTML = '<span class="badge badge-success">Activo</span>';
		} else {
			tenantEstadoElem.innerHTML = '<span class="badge badge-secondary">Suspendido</span>';
		}

		// Información de la suscripción (si existe)
		const suscripcionCard = document.getElementById('pago-detalle-suscripcion-card');
		if (pago.suscripcion_id) {
			suscripcionCard.style.display = 'block';
			document.getElementById('pago-detalle-plan-nombre').textContent = pago.plan_nombre || 'N/A';
			document.getElementById('pago-detalle-plan-precio').textContent = '$' + parseFloat(pago.plan_precio || 0).toFixed(2);
			document.getElementById('pago-detalle-suscripcion-inicio').textContent = formatDate(pago.suscripcion_inicio) || 'N/A';
			document.getElementById('pago-detalle-suscripcion-fin').textContent = formatDate(pago.suscripcion_fin) || 'N/A';

			const susEstadoElem = document.getElementById('pago-detalle-suscripcion-estado');
			susEstadoElem.innerHTML = '<span class="badge badge-' + getBadgeClass(pago.suscripcion_estatus) + '">' +
				ucfirst(pago.suscripcion_estatus || 'N/A') + '</span>';
		} else {
			suscripcionCard.style.display = 'none';
		}

		// Abrir modal
		if (window.jQuery && $('#pagoDetalleModal').modal) {
			$('#pagoDetalleModal').modal('show');
		}
	}

	/**
	 * Cargar tenants para el select de filtros
	 */
	async function loadTenantsForFilterSelect() {
		try {
			const data = await fetchJson(api.tenants);
			if (!data.ok) return;

			const select = document.getElementById('filtro-tenant');
			if (!select) return;

			select.innerHTML = '<option value="">Todos los tenants</option>';
			(data.data || []).forEach(tenant => {
				const option = document.createElement('option');
				option.value = tenant.id;
				option.textContent = tenant.nombre + ' (' + tenant.slug + ')';
				select.appendChild(option);
			});
		} catch (err) {
			console.error('Error cargando tenants:', err);
		}
	}

	/**
	 * Aplicar filtros al formulario
	 */
	function aplicarFiltrosPagos(e) {
		e.preventDefault();

		const filters = {
			tenant_id: document.getElementById('filtro-tenant')?.value || '',
			status: document.getElementById('filtro-estado')?.value || '',
			metodo: document.getElementById('filtro-metodo')?.value || '',
			concepto: document.getElementById('filtro-concepto')?.value || '',
			fecha_inicio: document.getElementById('filtro-fecha-inicio')?.value || '',
			fecha_fin: document.getElementById('filtro-fecha-fin')?.value || ''
		};

		fetchPagos(filters).catch(e => showAlert(e.message, 'error'));
	}

	/**
	 * Limpiar filtros
	 */
	function limpiarFiltrosPagos() {
		document.getElementById('filtro-tenant').value = '';
		document.getElementById('filtro-estado').value = '';
		document.getElementById('filtro-metodo').value = '';
		document.getElementById('filtro-concepto').value = '';
		document.getElementById('filtro-fecha-inicio').value = '';
		document.getElementById('filtro-fecha-fin').value = '';

		fetchPagos().catch(e => showAlert(e.message, 'error'));
	}

	/**
	 * Exportar pagos
	 */
	function exportarPagos(e) {
		e.preventDefault();

		const formato = document.getElementById('export-formato').value;
		const fechaInicio = document.getElementById('export-fecha-inicio').value;
		const fechaFin = document.getElementById('export-fecha-fin').value;

		// Obtener filtros actuales
		const filters = {
			formato: formato,
			tenant_id: document.getElementById('filtro-tenant')?.value || '',
			status: document.getElementById('filtro-estado')?.value || '',
			metodo: document.getElementById('filtro-metodo')?.value || '',
			fecha_inicio: fechaInicio || document.getElementById('filtro-fecha-inicio')?.value || '',
			fecha_fin: fechaFin || document.getElementById('filtro-fecha-fin')?.value || ''
		};

		// Construir URL de exportación
		const params = new URLSearchParams();
		Object.keys(filters).forEach(key => {
			if (filters[key]) params.append(key, filters[key]);
		});

		const exportUrl = api.pago_export + '?' + params.toString();

		// Abrir en nueva ventana para descargar
		window.open(exportUrl, '_blank');

		// Cerrar modal
		if (window.jQuery && $('#exportPagosModal').modal) {
			$('#exportPagosModal').modal('hide');
		}

		showAlert('Exportación iniciada. El archivo se descargará automáticamente.', 'success');
	}

	/**
	 * Obtener clase de badge según estado
	 */
	function getBadgeClass(status) {
		const map = {
			'activa': 'success',
			'pagado': 'success',
			'pendiente': 'warning',
			'fallido': 'danger',
			'expirada': 'danger',
			'cancelada': 'secondary'
		};
		return map[status] || 'secondary';
	}

	// ===== HELPERS =====
	function escapeHtml(str) {
		if (str === null || str === undefined) return '';
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;');
	}

	function populatePlanSelect(selectElement, planes) {
		if (!selectElement) return;
		const currentVal = selectElement.value;
		selectElement.innerHTML = '<option value="">-- Sin Plan --</option>';
		planes.forEach(plan => {
			const option = document.createElement('option');
			option.value = plan.id;
			option.textContent = `${plan.nombre} ($${plan.precio_mensual})`;
			selectElement.appendChild(option);
		});
		selectElement.value = currentVal;
	}

	async function createTenant(payload) {
		const res = await postForm(api.tenant_create, payload);
		if (res.ok) {
			showAlert('✓ Tenant creado correctamente<br><small>Ya puedes configurar sus datos y asignarle un plan</small>', 'success');
			fetchTenants();
		}
		return res;
	}

	async function createPlan(payload) {
		const res = await postForm(api.plan_create, payload);
		if (res.ok) {
			showAlert('✓ Plan creado correctamente<br><small>Ahora puedes asignarlo a los tenants</small>', 'success');
			fetchPlanes();
		}
		return res;
	}

	// Init on load for admin views
	function initAdmin() {
		// attach alert container
		const container = document.createElement('div');
		container.id = 'admin-alert';
		container.style.display = 'none';
		document.body.insertBefore(container, document.body.firstChild);

		// fetch data if elements exist
		if (document.getElementById('ingresosChart')) fetchDashboardStats().catch(e => showAlert(e.message, 'error'));
		if (document.getElementById('tenants-tbody')) fetchTenants().catch(e => showAlert(e.message, 'error'));
		if (document.getElementById('planes-tbody')) fetchPlanes().catch(e => showAlert(e.message, 'error'));
		if (document.getElementById('pagos-tbody')) {
			fetchPagos().catch(e => showAlert(e.message, 'error'));
			loadTenantsForFilterSelect(); // Cargar tenants en filtro
		}

		// hook new tenant button -> show modal
		const btnNewTenant = document.getElementById('btn-new-tenant');
		if (btnNewTenant) {
			btnNewTenant.addEventListener('click', function (e) {
				e.preventDefault();
				const modal = document.getElementById('tenantModal');
				if (!modal) return showAlert('Modal de tenant no encontrado', 'error');
				// reset form
				const form = document.getElementById('tenant-form');
				if (form) form.reset();
				// Cargar planes en el select
				const planSelect = document.getElementById('tenant-plan-id');
				if (planSelect) {
					fetchJson(api.planes).then(res => populatePlanSelect(planSelect, res.data || []));
				}

				if (window.jQuery && $('#tenantModal').modal) {
					$('#tenantModal').modal('show');
				} else {
					modal.style.display = 'block';
					modal.classList.add('show');
				}
			});
		}

		// handle tenant form submit
		const tenantForm = document.getElementById('tenant-form');
		if (tenantForm) {
			tenantForm.addEventListener('submit', function (e) {
				e.preventDefault();
				const fd = new FormData(tenantForm);
				const payload = {};
				fd.forEach((v, k) => { payload[k] = v; });
				// normalize activo
				payload.activo = tenantForm.querySelector('#tenant-activo').checked ? 1 : 0;
				const id = payload['tenant-id'];
				if (id) {
					postForm(api.tenant_update(id), payload).then(res => {
						if (res.ok) {
							showAlert(res.msg || '✓ Tenant actualizado correctamente', 'success');
							fetchTenants();
						}
					}).catch(err => showAlert(err.message, 'error')).then(() => {
						if (window.jQuery && $('#tenantModal').modal) {
							$('#tenantModal').modal('hide');
						} else {
							const modal = document.getElementById('tenantModal');
							if (modal) { modal.style.display = 'none'; modal.classList.remove('show'); }
						}
					});
				} else {
					createTenant(payload).catch(err => showAlert(err.message, 'error')).then(() => {
						if (window.jQuery && $('#tenantModal').modal) {
							$('#tenantModal').modal('hide');
						} else {
							const modal = document.getElementById('tenantModal');
							if (modal) { modal.style.display = 'none'; modal.classList.remove('show'); }
						}
					});
				}
			});
		}

		const btnNewPlan = document.getElementById('btn-new-plan');
		if (btnNewPlan) {
			btnNewPlan.addEventListener('click', function (e) {
				e.preventDefault();
				const modal = document.getElementById('planModal');
				if (!modal) return showAlert('Modal de plan no encontrado', 'error');
				const form = document.getElementById('plan-form');
				if (form) form.reset();
				if (window.jQuery && $('#planModal').modal) {
					$('#planModal').modal('show');
				} else {
					modal.style.display = 'block';
					modal.classList.add('show');
				}
			});
		}

		const planForm = document.getElementById('plan-form');
		if (planForm) {
			planForm.addEventListener('submit', function (e) {
				e.preventDefault();
				const fd = new FormData(planForm);
				const payload = {};
				fd.forEach((v, k) => { payload[k] = v; });
				payload.ads = payload.ads === 'on' ? 1 : 0;
				const id = payload['id'];
				if (id) {
					postForm(api.plan_update(id), payload).then(res => {
						if (res.ok) {
							showAlert('✓ Plan actualizado correctamente', 'success');
							fetchPlanes();
						}
					}).catch(err => showAlert(err.message, 'error')).then(() => {
						if (window.jQuery && $('#planModal').modal) {
							$('#planModal').modal('hide');
						} else {
							const modal = document.getElementById('planModal');
							if (modal) { modal.style.display = 'none'; modal.classList.remove('show'); }
						}
					});
				} else {
					createPlan(payload).catch(err => showAlert(err.message, 'error')).then(() => {
						if (window.jQuery && $('#planModal').modal) {
							$('#planModal').modal('hide');
						} else {
							const modal = document.getElementById('planModal');
							if (modal) { modal.style.display = 'none'; modal.classList.remove('show'); }
						}
					});
				}
			});
		}

		// pagos export modal hook
		const btnExportPagos = document.getElementById('btn-export-pagos');
		if (btnExportPagos) {
			btnExportPagos.addEventListener('click', function (e) {
				e.preventDefault();
				const modal = document.getElementById('exportPagosModal');
				if (!modal) return showAlert('Modal de exportación no encontrado', 'error');
				if (window.jQuery && $('#exportPagosModal').modal) {
					$('#exportPagosModal').modal('show');
				} else {
					modal.style.display = 'block';
					modal.classList.add('show');
				}
			});
		}

		const exportForm = document.getElementById('export-pagos-form');
		if (exportForm) {
			exportForm.addEventListener('submit', function (e) {
				e.preventDefault();
				const fd = new FormData(exportForm);
				const params = new URLSearchParams();
				for (const [k, v] of fd.entries()) {
					if (v) params.append(k, v);
				}
				// Abrir en nueva pestaña la ruta /app/pedidos_export?formato=...
				const url = '/app/pedidos_export?' + params.toString();
				window.open(url, '_blank');
				if (window.jQuery && $('#exportPagosModal').modal) {
					$('#exportPagosModal').modal('hide');
				} else {
					const modal = document.getElementById('exportPagosModal');
					if (modal) { modal.style.display = 'none'; modal.classList.remove('show'); }
				}
			});
		}

		// ===== SUSCRIPCIONES =====

		// Cargar suscripciones si estamos en esa vista
		const suscripcionesTbody = document.getElementById('suscripciones-tbody');
		if (suscripcionesTbody) {
			fetchSuscripciones();
		}

		// Botón nueva suscripción
		const btnNewSuscripcion = document.getElementById('btn-new-suscripcion');
		if (btnNewSuscripcion) {
			btnNewSuscripcion.addEventListener('click', function (e) {
				e.preventDefault();
				openSuscripcionModal();
			});
		}

		// Form de suscripción
		const suscripcionForm = document.getElementById('suscripcion-form');
		if (suscripcionForm) {
			// Cargar tenants y planes en los selects
			loadTenantsForSelect();
			loadPlanesForSelect('suscripcion-plan');

			suscripcionForm.addEventListener('submit', function (e) {
				e.preventDefault();
				const id = document.getElementById('suscripcion-id').value;
				const fd = new FormData(suscripcionForm);
				const payload = {};
				for (const [k, v] of fd.entries()) {
					if (k !== 'id') payload[k] = v;
				}

				if (id) {
					updateSuscripcion(id, payload).catch(err => showAlert(err.message, 'error')).then(() => {
						if (window.jQuery && $('#suscripcionModal').modal) {
							$('#suscripcionModal').modal('hide');
						}
					});
				} else {
					createSuscripcion(payload).catch(err => showAlert(err.message, 'error')).then(() => {
						if (window.jQuery && $('#suscripcionModal').modal) {
							$('#suscripcionModal').modal('hide');
						}
					});
				}
			});
		}
	}

	// ===== Funciones de Suscripciones =====

	async function fetchSuscripciones() {
		try {
			const data = await fetchJson(api.suscripciones);
			if (!data.ok) throw new Error('Error cargando suscripciones');
			renderSuscripciones(data.data || []);
			updateSuscripcionesStats(data.data || []);
		} catch (err) {
			showAlert(err.message, 'error');
		}
	}

	function renderSuscripciones(rows) {
		const tbody = document.getElementById('suscripciones-tbody');
		if (!tbody) return;
		tbody.innerHTML = '';

		if (rows.length === 0) {
			tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No hay suscripciones registradas</td></tr>';
			return;
		}

		rows.forEach(r => {
			const tr = document.createElement('tr');

			// Calcular días restantes
			const hoy = new Date();
			const fin = new Date(r.fin);
			const diffTime = fin - hoy;
			const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

			let diasRestantes = '-';
			let badgeClass = 'secondary';

			if (r.estatus === 'activa') {
				if (diffDays > 30) {
					diasRestantes = `<span class="text-success">${diffDays} días</span>`;
					badgeClass = 'success';
				} else if (diffDays > 0) {
					diasRestantes = `<span class="text-warning">${diffDays} días</span>`;
					badgeClass = 'warning';
				} else {
					diasRestantes = `<span class="text-danger">Vencida hace ${Math.abs(diffDays)} días</span>`;
					badgeClass = 'danger';
				}
			} else if (r.estatus === 'pendiente') {
				badgeClass = 'warning';
				diasRestantes = '-';
			} else if (r.estatus === 'expirada' || r.estatus === 'cancelada') {
				badgeClass = 'danger';
				diasRestantes = '-';
			}

			tr.innerHTML = `
				<td>${r.id}</td>
				<td>${escapeHtml(r.tenant_nombre)}</td>
				<td>${escapeHtml(r.plan_nombre)}</td>
				<td>${formatDate(r.inicio)}</td>
				<td>${formatDate(r.fin)}</td>
				<td><span class="badge badge-${badgeClass}">${ucfirst(r.estatus)}</span></td>
				<td>${diasRestantes}</td>
				<td class="text-right">
					<button class="btn btn-sm btn-info btn-suscripcion-historico" data-tenant="${r.tenant_id}" title="Ver Histórico">
						<i class="fas fa-history"></i>
					</button>
					<button class="btn btn-sm btn-primary btn-suscripcion-edit" data-id="${r.id}">
						<i class="fas fa-edit"></i>
					</button>
					<button class="btn btn-sm btn-danger btn-suscripcion-delete" data-id="${r.id}">
						<i class="fas fa-trash"></i>
					</button>
				</td>
			`;
			tbody.appendChild(tr);
		});

		// Attach handlers
		tbody.querySelectorAll('.btn-suscripcion-edit').forEach(b => b.addEventListener('click', onSuscripcionEdit));
		tbody.querySelectorAll('.btn-suscripcion-delete').forEach(b => b.addEventListener('click', onSuscripcionDelete));
		tbody.querySelectorAll('.btn-suscripcion-historico').forEach(b => b.addEventListener('click', onSuscripcionHistorico));
	}

	function updateSuscripcionesStats(rows) {
		const hoy = new Date();
		let activas = 0;
		let proximas = 0;
		let expiradas = 0;

		rows.forEach(r => {
			const fin = new Date(r.fin);
			const diffTime = fin - hoy;
			const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

			if (r.estatus === 'activa') {
				if (diffDays > 30) {
					activas++;
				} else if (diffDays > 0) {
					proximas++;
				} else {
					expiradas++;
				}
			} else if (r.estatus === 'expirada') {
				expiradas++;
			}
		});

		const statActivas = document.getElementById('stat-activas');
		const statVencer = document.getElementById('stat-vencer');
		const statExpiradas = document.getElementById('stat-expiradas');

		if (statActivas) statActivas.textContent = activas;
		if (statVencer) statVencer.textContent = proximas;
		if (statExpiradas) statExpiradas.textContent = expiradas;
	}

	function openSuscripcionModal(suscripcion = null) {
		const form = document.getElementById('suscripcion-form');
		if (!form) return;

		form.reset();
		document.getElementById('suscripcion-id').value = '';
		document.getElementById('suscripcion-modal-title').textContent = suscripcion ? 'Editar Suscripción' : 'Crear Suscripción';

		if (suscripcion) {
			document.getElementById('suscripcion-id').value = suscripcion.id;
			document.getElementById('suscripcion-tenant').value = suscripcion.tenant_id;
			document.getElementById('suscripcion-plan').value = suscripcion.plan_id;
			document.getElementById('suscripcion-inicio').value = suscripcion.inicio;
			document.getElementById('suscripcion-fin').value = suscripcion.fin;
			document.getElementById('suscripcion-estatus').value = suscripcion.estatus;
		} else {
			// Establecer fecha de inicio por defecto (hoy)
			const hoy = new Date().toISOString().split('T')[0];
			document.getElementById('suscripcion-inicio').value = hoy;

			// Establecer fecha de fin por defecto (1 mes después)
			const fin = new Date();
			fin.setMonth(fin.getMonth() + 1);
			document.getElementById('suscripcion-fin').value = fin.toISOString().split('T')[0];
		}

		if (window.jQuery && $('#suscripcionModal').modal) {
			$('#suscripcionModal').modal('show');
		}
	}

	async function loadTenantsForSelect() {
		try {
			const data = await fetchJson(api.tenants);
			const select = document.getElementById('suscripcion-tenant');
			if (!select) return;

			select.innerHTML = '<option value="">-- Seleccione un tenant --</option>';
			(data.data || []).forEach(t => {
				const option = document.createElement('option');
				option.value = t.id;
				option.textContent = `${t.nombre} (${t.slug})`;
				select.appendChild(option);
			});
		} catch (err) {
			console.error('Error cargando tenants:', err);
		}
	}

	async function loadPlanesForSelect(selectId) {
		try {
			const data = await fetchJson(api.planes);
			const select = document.getElementById(selectId);
			if (!select) return;

			select.innerHTML = '<option value="">-- Seleccione un plan --</option>';
			(data.data || []).forEach(p => {
				const option = document.createElement('option');
				option.value = p.id;
				option.textContent = `${p.nombre} - $${parseFloat(p.precio_mensual || 0).toFixed(2)}/mes`;
				select.appendChild(option);
			});
		} catch (err) {
			console.error('Error cargando planes:', err);
		}
	}

	async function createSuscripcion(payload) {
		const data = await postForm(api.suscripcion_create, payload);
		if (!data.ok) throw new Error(data.msg || 'Error creando suscripción');
		showAlert(data.msg || 'Suscripción creada correctamente', 'success');
		fetchSuscripciones();
	}

	async function updateSuscripcion(id, payload) {
		const data = await postForm(api.suscripcion_update(id), payload);
		if (!data.ok) throw new Error(data.msg || 'Error actualizando suscripción');
		showAlert(data.msg || 'Suscripción actualizada correctamente', 'success');
		fetchSuscripciones();
	}

	async function onSuscripcionEdit(e) {
		const id = e.currentTarget.getAttribute('data-id');
		try {
			const data = await fetchJson(api.suscripciones);
			const suscripcion = (data.data || []).find(s => String(s.id) === String(id));
			if (!suscripcion) return showAlert('Suscripción no encontrada', 'error');
			openSuscripcionModal(suscripcion);
		} catch (err) {
			showAlert(err.message, 'error');
		}
	}

	async function onSuscripcionDelete(e) {
		const id = e.currentTarget.getAttribute('data-id');
		const confirmed = await confirmAction(
			'¿Está seguro que desea eliminar esta suscripción?<br><small class="text-danger">Esta acción no se puede deshacer.</small>',
			'Eliminar Suscripción'
		);
		if (!confirmed) return;

		try {
			const data = await postForm(api.suscripcion_delete(id), {});
			if (!data.ok) throw new Error(data.msg || 'Error eliminando suscripción');
			showAlert(data.msg || 'Suscripción eliminada correctamente', 'success');
			fetchSuscripciones();
		} catch (err) {
			showAlert(err.message, 'error');
		}
	}

	async function onSuscripcionHistorico(e) {
		const tenantId = e.currentTarget.getAttribute('data-tenant');
		try {
			const data = await fetchJson(api.tenant_suscripciones(tenantId));
			if (!data.ok) throw new Error('Error cargando histórico');

			renderHistoricoSuscripciones(data.data || [], data.tenant);

			if (window.jQuery && $('#historicoModal').modal) {
				$('#historicoModal').modal('show');
			}
		} catch (err) {
			showAlert(err.message, 'error');
		}
	}

	function renderHistoricoSuscripciones(rows, tenant) {
		const infoDiv = document.getElementById('historico-tenant-info');
		const tbody = document.getElementById('historico-tbody');

		if (!infoDiv || !tbody) return;

		// Información del tenant
		if (tenant) {
			infoDiv.innerHTML = `
				<div class="alert alert-info">
					<h6 class="mb-1"><strong>${escapeHtml(tenant.nombre)}</strong></h6>
					<small>Slug: <code>${escapeHtml(tenant.slug)}</code> | Estado: ${tenant.activo ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-secondary">Inactivo</span>'}</small>
				</div>
			`;
		}

		// Tabla de suscripciones
		tbody.innerHTML = '';
		if (rows.length === 0) {
			tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay suscripciones registradas para este tenant</td></tr>';
			return;
		}

		rows.forEach(r => {
			const badgeClass = r.estatus === 'activa' ? 'success' :
				r.estatus === 'pendiente' ? 'warning' : 'danger';

			const tr = document.createElement('tr');
			tr.innerHTML = `
				<td>${r.id}</td>
				<td>${escapeHtml(r.plan_nombre || '-')}</td>
				<td>${formatDate(r.inicio)}</td>
				<td>${formatDate(r.fin)}</td>
				<td><span class="badge badge-${badgeClass}">${ucfirst(r.estatus)}</span></td>
				<td>$${parseFloat(r.precio_mensual || 0).toFixed(2)}</td>
			`;
			tbody.appendChild(tr);
		});
	}

	// Funciones auxiliares
	function formatDate(dateStr) {
		if (!dateStr) return '-';
		const d = new Date(dateStr);
		const day = String(d.getDate()).padStart(2, '0');
		const month = String(d.getMonth() + 1).padStart(2, '0');
		const year = d.getFullYear();
		return `${day}/${month}/${year}`;
	}

	function ucfirst(str) {
		if (!str) return '';
		return str.charAt(0).toUpperCase() + str.slice(1);
	}

	// ===== EVENT LISTENERS =====

	// Event listeners para pagos
	document.addEventListener('DOMContentLoaded', function () {
		// Formulario de filtros de pagos
		const filtrosForm = document.getElementById('filtros-form');
		if (filtrosForm) {
			filtrosForm.addEventListener('submit', aplicarFiltrosPagos);
		}

		// Botón limpiar filtros
		const btnLimpiarFiltros = document.getElementById('btn-limpiar-filtros');
		if (btnLimpiarFiltros) {
			btnLimpiarFiltros.addEventListener('click', limpiarFiltrosPagos);
		}

		// Botón exportar pagos
		const btnExportPagos = document.getElementById('btn-export-pagos');
		if (btnExportPagos) {
			btnExportPagos.addEventListener('click', function (e) {
				e.preventDefault();
				if (window.jQuery && $('#exportPagosModal').modal) {
					$('#exportPagosModal').modal('show');
				}
			});
		}

		// Formulario de exportación
		const exportForm = document.getElementById('export-pagos-form');
		if (exportForm) {
			exportForm.addEventListener('submit', exportarPagos);
		}

		// Botón refresh dashboard
		const btnRefreshDashboard = document.getElementById('btn-refresh-dashboard');
		if (btnRefreshDashboard) {
			btnRefreshDashboard.addEventListener('click', function (e) {
				e.preventDefault();
				fetchDashboardStats().catch(err => showAlert(err.message, 'error'));
			});
		}
	});

	// Expose init
	document.addEventListener('DOMContentLoaded', initAdmin);

})();
