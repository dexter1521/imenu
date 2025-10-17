// admin.js - consuma endpoints de Admin.php y renderiza tablas en vistas admin
(function () {
	'use strict';

	const api = {
		tenants: '/admin/tenants',
		tenant_create: '/admin/tenant_create',
		planes: '/admin/planes',
		plan_create: '/admin/plan_create',
		pagos: '/admin/pagos'
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
		const loginUrl = (window.IMENU && window.IMENU.routes && window.IMENU.routes.login) ? window.IMENU.routes.login : '/admin/auth';
		window.location.href = loginUrl + (loginUrl.indexOf('?') === -1 ? '?expired=1' : '&expired=1');
	}

	async function fetchJson(url, opts = {}) {
		opts.headers = Object.assign({}, opts.headers || {}, getAuthHeaders());
		// Enviar cookies (cookie HttpOnly) para autenticación
		if (!opts.credentials) opts.credentials = 'same-origin';
		const res = await fetch(url, opts);
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
		for (const k in payload) {
			if (payload[k] === null || payload[k] === undefined) continue;
			params.append(k, payload[k]);
		}
		const headers = Object.assign({ 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, getAuthHeaders());
		const fetchOpts = { method: 'POST', headers: headers, body: params.toString() };
		if (!fetchOpts.credentials) fetchOpts.credentials = 'same-origin';
		const res = await fetch(url, fetchOpts);
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

	function showAlert(msg, type = 'info') {
		if (window.Swal) {
			Swal.fire({
				title: type === 'error' ? 'Error' : (type === 'warning' ? 'Atención' : 'Información'),
				text: msg,
				icon: type === 'error' ? 'error' : (type === 'warning' ? 'warning' : 'info'),
				confirmButtonText: 'OK'
			});
			return;
		}
		const el = document.getElementById('admin-alert');
		if (!el) return;
		el.innerText = msg;
		el.className = 'alert alert-' + (type === 'error' ? 'danger' : type);
		el.style.display = 'block';
		setTimeout(() => { el.style.display = 'none'; }, 4000);
	}

	/**
	 * Confirmación amigable - usa SweetAlert2 si está disponible, si no usa confirm()
	 * @param {string} message
	 * @returns {Promise<boolean>} true si el usuario confirma
	 */
	function confirmAction(message) {
		if (window.Swal) {
			return Swal.fire({
				title: 'Confirmar',
				text: message,
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Sí',
				cancelButtonText: 'Cancelar'
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
                <td>${r.plan_id ? '<span class="badge badge-success">ID:' + r.plan_id + '</span>' : '<span class="badge badge-secondary">-</span>'}</td>
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
		window.location.href = '/admin/tenant_show/' + encodeURIComponent(id);
	}

	function onTenantEdit(e) {
		const id = e.currentTarget.getAttribute('data-id');
		// fetch tenant details from current table rows or from server
		fetchJson('/admin/tenants').then(d => {
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
		const ok = await confirmAction('¿Eliminar tenant? Esta acción es irreversible.');
		if (!ok) return;
		fetchJson('/admin/tenant_delete/' + encodeURIComponent(id), { method: 'POST' }).then(res => {
			if (res.ok) {
				showAlert(res.msg || 'Tenant eliminado', 'success');
				fetchTenants();
			}
		}).catch(err => showAlert(err.message, 'error'));
	}

	async function onTenantToggle(e) {
		const id = e.currentTarget.getAttribute('data-id');
		const ok = await confirmAction('¿Cambiar estado del tenant?');
		if (!ok) return;
		fetchJson('/admin/tenant_toggle/' + encodeURIComponent(id), { method: 'POST' }).then(res => {
			if (res.ok) {
				showAlert(res.msg || 'Estado actualizado', 'success');
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
		fetchJson('/admin/planes').then(d => {
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
		const ok = await confirmAction('¿Eliminar plan?');
		if (!ok) return;
		fetchJson('/admin/plan_delete/' + encodeURIComponent(id), { method: 'POST' }).then(res => {
			if (res.ok) {
				showAlert('Plan eliminado', 'success');
				fetchPlanes();
			}
		}).catch(err => showAlert(err.message, 'error'));
	}

	// Pagos
	async function fetchPagos() {
		const data = await fetchJson(api.pagos);
		if (!data.ok) throw new Error('Error cargando pagos');
		renderPagos(data.data || []);
	}

	function renderPagos(rows) {
		const tbody = document.getElementById('pagos-tbody');
		if (!tbody) return;
		tbody.innerHTML = '';
		rows.forEach(r => {
			const tr = document.createElement('tr');
			tr.innerHTML = `
                <td>${r.id}</td>
                <td>${escapeHtml(r.tenant_id || '')}</td>
                <td>${escapeHtml(r.concepto || '')}</td>
                <td>$${parseFloat(r.monto || 0).toFixed(2)}</td>
                <td>${escapeHtml(r.metodo || '')}</td>
                <td>${escapeHtml(r.referencia || '')}</td>
                <td>${escapeHtml(r.status || '')}</td>
                <td>${r.fecha || ''}</td>
                <td>
                    <button class="btn btn-sm btn-info">Ver</button>
                </td>
            `;
			tbody.appendChild(tr);
		});
	}

	// Helpers
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
			showAlert('Tenant creado correctamente', 'success');
			fetchTenants();
		}
		return res;
	}

	async function createPlan(payload) {
		const res = await postForm(api.plan_create, payload);
		if (res.ok) {
			showAlert('Plan creado correctamente', 'success');
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
		if (document.getElementById('tenants-tbody')) fetchTenants().catch(e => showAlert(e.message, 'error'));
		if (document.getElementById('planes-tbody')) fetchPlanes().catch(e => showAlert(e.message, 'error'));
		if (document.getElementById('pagos-tbody')) fetchPagos().catch(e => showAlert(e.message, 'error'));

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
					postForm('/admin/tenant_update/' + encodeURIComponent(id), payload).then(res => {
						if (res.ok) {
							showAlert(res.msg || 'Tenant actualizado', 'success');
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
				const id = payload['plan-id'];
				if (id) {
					postForm('/admin/plan_update/' + encodeURIComponent(id), payload).then(res => {
						if (res.ok) {
							showAlert('Plan actualizado', 'success');
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
	}

	// Expose init
	document.addEventListener('DOMContentLoaded', initAdmin);

})();
