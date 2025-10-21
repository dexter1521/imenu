<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>iMenu - Acceso al Panel</title>

	<!-- Custom fonts for this template-->
	<link href="<?php echo base_url('assets/vendor/fontawesome-free/css/all.min.css'); ?>" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

	<!-- Custom styles for this template-->
	<link href="<?php echo base_url('assets/css/sb-admin-2.min.css'); ?>" rel="stylesheet">
</head>

<body class="bg-gradient-success">

	<div class="container">

		<!-- Outer Row -->
		<div class="row justify-content-center">

			<div class="col-xl-10 col-lg-12 col-md-9">

				<div class="card o-hidden border-0 shadow-lg my-5">
					<div class="card-body p-0">
						<!-- Nested Row within Card Body -->
						<div class="row">
							<div class="col-lg-6 d-none d-lg-block" style="background: url(https://source.unsplash.com/random/800x600?restaurant); background-position: center; background-size: cover;"></div>
							<div class="col-lg-6">
								<div class="p-5">
									<div class="text-center">
										<h1 class="h4 text-gray-900 mb-4">¡Bienvenido a tu Menú!</h1>
										<p class="mb-4">Ingresa para administrar tu restaurante.</p>
									</div>
									<form class="user" id="tenant-login-form">
										<div class="form-group">
											<input type="email" name="email" class="form-control form-control-user" id="login-email" placeholder="Tu correo electrónico..." required>
										</div>
										<div class="form-group">
											<input type="password" name="password" class="form-control form-control-user" id="login-password" placeholder="Contraseña" required>
										</div>
										<button type="submit" id="btn-login" class="btn btn-success btn-user btn-block">
											Entrar
										</button>
										<div id="login-alert" class="mt-3" style="display:none;"></div>
									</form>
									<hr>
									<div class="text-center">
										<a class="small" href="#">¿Olvidaste tu contraseña?</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

			</div>

		</div>

	</div>

	<!-- Core JavaScript-->
	<script src="<?php echo base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
	<script src="<?php echo base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<!-- Login script -->
	<script>
		// Definimos las rutas para el script de login
		window.IMENU = window.IMENU || {};
		window.IMENU.routes = {
			login: '<?php echo site_url('tenantauth/login'); ?>',
			dashboard: '<?php echo site_url('app/dashboard'); ?>'
		};

		// Configurar CSRF token para el formulario de login
		window.IMENU.csrf = {
			name: '<?php echo $this->security->get_csrf_token_name(); ?>',
			hash: '<?php echo $this->security->get_csrf_hash(); ?>',
			cookie_name: '<?php echo $this->config->item('csrf_cookie_name'); ?>'
		};
	</script>
	<script src="<?php echo base_url('assets/js/login-tenant.js'); ?>"></script>

</body>

</html>
