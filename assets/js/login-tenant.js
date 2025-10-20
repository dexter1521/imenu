// Tenant login script: hace POST a /tenantauth/login, guarda metadatos y redirige al panel del tenant.
(function () {
	'use strict';

	// ============================================
	// LIMPIEZA PREVENTIVA AL CARGAR LA PÁGINA
	// ============================================
	// Limpiar localStorage para evitar conflictos
	localStorage.removeItem('imenu_role');
	localStorage.removeItem('imenu_tenant');

	// Intentar limpiar cookie imenu_token (aunque es HttpOnly, intentamos por si acaso)
	document.cookie = 'imenu_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';

	// ============================================

	const form = document.getElementById('tenant-login-form');
	if (!form) return;

	const btn = document.getElementById('btn-login');
	const alertBox = document.getElementById('login-alert');

	function showAlert(message, type = 'danger') {
		// use SweetAlert2 if available
		if (window.Swal) {
			Swal.fire({
				title: type === 'warning' ? 'Atención' : (type === 'danger' ? 'Error' : 'Información'),
				text: message,
				icon: type === 'danger' ? 'error' : (type === 'warning' ? 'warning' : 'info'),
				confirmButtonText: 'OK'
			});
			return;
		}
		alertBox.style.display = 'block';
		alertBox.className = `alert alert-${type}`;
		alertBox.textContent = message;
	}

	function clearAlert() {
		alertBox.style.display = 'none';
		alertBox.className = '';
		alertBox.textContent = '';
	}

	// Mensaje de sesión expirada
	try {
		const params = new URLSearchParams(window.location.search);
		if (params.get('expired') === '1') {
			setTimeout(() => { showAlert('Tu sesión ha expirado. Por favor, ingresa de nuevo.', 'warning'); }, 50);
			const url = new URL(window.location.href);
			url.searchParams.delete('expired');
			window.history.replaceState(null, '', url.toString());
		}
	} catch (e) { /* ignore */ }

	form.addEventListener('submit', async (e) => {
		e.preventDefault();
		clearAlert();
		btn.disabled = true;
		btn.textContent = 'Verificando...';

		const email = document.getElementById('login-email').value.trim();
		const password = document.getElementById('login-password').value;

		if (!email || !password) {
			showAlert('Email y contraseña son requeridos', 'warning');
			btn.disabled = false;
			btn.textContent = 'Entrar';
			return;
		}

		try {
			const loginUrl = window.IMENU.routes.login;
			const params = new URLSearchParams({ email, password });

			// Adjuntar token CSRF si está disponible
			if (window.IMENU && window.IMENU.csrf && window.IMENU.csrf.name) {
				const cookieName = window.IMENU.csrf.cookie_name || null;
				let csrfValue = window.IMENU.csrf.hash || '';
				if (cookieName) {
					try {
						const match = document.cookie.match(new RegExp('(^|; )' + cookieName.replace(/([.*+?^${}()|[\]\\])/g, '\\$1') + '=([^;]*)'));
						if (match) csrfValue = decodeURIComponent(match[2]);
					} catch (e) { /* ignore */ }
				}
				params.append(window.IMENU.csrf.name, csrfValue);
			}

			const resp = await fetch(loginUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: params.toString()
			});

			const text = await resp.text();
			let data;
			try {
				data = text ? JSON.parse(text) : {};
			} catch (err) {
				console.error('Respuesta no-JSON del servidor:', text);
				showAlert('Error inesperado del servidor. Revisa la consola.');
				btn.disabled = false;
				btn.textContent = 'Entrar';
				return;
			}

			if (!resp.ok || data.ok === false) {
				showAlert(data.msg || `Error ${resp.status}`);
				btn.disabled = false;
				btn.textContent = 'Entrar';
				return;
			}

		// Guardar metadatos en localStorage
		localStorage.setItem('imenu_role', data.rol || '');
		localStorage.setItem('imenu_tenant', data.tenant_id !== undefined ? String(data.tenant_id) : '0');

		// Dar tiempo al navegador para procesar la cookie antes de redirigir
		setTimeout(() => {
			window.location.href = window.IMENU.routes.dashboard;
		}, 100);

	} catch (err) {
			console.error(err);
			showAlert('Error de red o de conexión. Intenta de nuevo.');
			btn.disabled = false;
			btn.textContent = 'Entrar';
		}
	});
})();
