// Admin login script: hace POST a /admin/auth (AdminAuth controller), guarda metadatos y redirige al panel

(async function () {
	'use strict';
	const form = document.getElementById('admin-login-form');
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

	// Si la URL contiene ?expired=1 mostrar mensaje de sesión expirada
	try {
		const _params = new URLSearchParams(window.location.search);
		if (_params.get('expired') === '1') {
			// esperar al siguiente tick para asegurarnos que showAlert está disponible
			setTimeout(() => { showAlert('Tu sesión ha expirado. Por favor inicia sesión de nuevo.', 'warning'); }, 50);
			// limpiar el parámetro expired de la URL después de mostrar el mensaje
			setTimeout(() => {
				try {
					const u = new URL(window.location.href);
					u.searchParams.delete('expired');
					window.history.replaceState(null, document.title, u.pathname + u.search + u.hash);
				} catch (e) { /* ignore */ }
			}, 2000);
		}
	} catch (e) { /* ignore */ }

	form.addEventListener('submit', async (e) => {
		e.preventDefault();
		clearAlert();
		btn.disabled = true;
		const email = document.getElementById('login-email').value.trim();
		const password = document.getElementById('login-password').value;
		if (!email || !password) {
			showAlert('Email y contraseña son requeridos', 'warning');
			btn.disabled = false;
			return;
		}

		try {
			const loginUrl = (window.IMENU && window.IMENU.routes && window.IMENU.routes.login) ? window.IMENU.routes.login : (window.location.origin + '/admin/auth');
			const params = new URLSearchParams({ email, password });
			// adjuntar token CSRF si está disponible
			if (window.IMENU && window.IMENU.csrf && window.IMENU.csrf.name) {
				// Preferir leer el valor actual de la cookie CSRF (evita desincronización si CI regeneró el token)
				const cookieName = window.IMENU.csrf.cookie_name || null;
				let csrfValue = window.IMENU.csrf.hash || '';
				if (cookieName) {
					// obtener valor de cookie
					try {
						const match = document.cookie.match(new RegExp('(^|; )' + cookieName.replace(/([.*+?^${}()|[\]\\])/g, '\\$1') + '=([^;]*)'));
						if (match) csrfValue = decodeURIComponent(match[2]);
					} catch (e) { /* ignore */ }
				}
				params.append(window.IMENU.csrf.name, csrfValue);
			}

			// Envío de formulario de login (debug removido)

			const resp = await fetch(loginUrl, {
				method: 'POST',
				credentials: 'same-origin', // importante: enviar cookies para validación CSRF
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: params.toString()
			});

			// Leer como texto y luego intentar parsear JSON (evita "body stream already read")
			const text = await resp.text();
			let data;
			try {
				data = text ? JSON.parse(text) : {};
			} catch (e) {
				// respuesta no-JSON (por ejemplo HTML de error) — lo volcamos en consola para depuración
				console.error('Respuesta no-JSON al hacer login (status ' + resp.status + '):', text);
				if (resp.status === 403) {
					showAlert('Acceso denegado (403). Es probable que la validación CSRF haya fallado: revisa que la cookie y el token coincidan. Revisa la consola.');
				} else {
					showAlert('Respuesta inesperada del servidor. Revisa la consola.');
				}
				btn.disabled = false;
				return;
			}
			if (!resp.ok || data.ok === false) {
				showAlert(data.msg || `Error ${resp.status}`);
				btn.disabled = false;
				return;
			}
			// Guardar token y metadatos
			// Nota: el servidor establece la cookie HttpOnly; no almacenar el token en client-side
			localStorage.setItem('imenu_role', data.rol || '');
			localStorage.setItem('imenu_tenant', data.tenant_id !== undefined ? String(data.tenant_id) : '0');

			// Esperar un momento para que la cookie se establezca correctamente
			// antes de redirigir
			setTimeout(() => {
				const adminUrl = (window.IMENU && window.IMENU.routes && window.IMENU.routes.admin)
					? window.IMENU.routes.admin
					: (window.location.origin + '/admin/dashboard');
				window.location.href = adminUrl;
			}, 100); // 100ms es suficiente para que el navegador procese la cookie

		} catch (err) {
			console.error(err);
			showAlert('Error de red. Revisa la consola.');
			btn.disabled = false;
		}
	});
})();
