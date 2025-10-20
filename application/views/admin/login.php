<!DOCTYPE html>
<html lang="en">

<head>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>IMenu - Login</title>

	<!-- Custom fonts for this template-->
	<link href="<?php echo base_url('assets/vendor/fontawesome-free/css/all.min.css'); ?>" rel="stylesheet" type="text/css">
	<link
		href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
		rel="stylesheet">

	<!-- Custom styles for this template-->
	<link href="<?php echo base_url('assets/css/sb-admin-2.min.css'); ?>" rel="stylesheet">

</head>

<body class="bg-gradient-primary">

	<div class="container">

		<!-- Outer Row -->
		<div class="row justify-content-center">

			<div class="col-xl-10 col-lg-12 col-md-9">

				<div class="card o-hidden border-0 shadow-lg my-5">
					<div class="card-body p-0">
						<!-- Nested Row within Card Body -->
						<div class="row">
							<div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
							<div class="col-lg-6">
								<div class="p-5">
									<div class="text-center">
										<h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
									</div>
									<form class="user" id="admin-login-form">
										<div class="form-group">
											<input type="email" name="email" class="form-control form-control-user"
												id="login-email" aria-describedby="emailHelp"
												placeholder="Enter Email Address..." required>
										</div>
										<div class="form-group">
											<input type="password" name="password" class="form-control form-control-user"
												id="login-password" placeholder="Password" required>
										</div>
										<div class="form-group">
											<div class="custom-control custom-checkbox small">
												<input type="checkbox" class="custom-control-input" id="customCheck">
												<label class="custom-control-label" for="customCheck">Remember
													Me</label>
											</div>
										</div>
										<button type="submit" id="btn-login" class="btn btn-primary btn-user btn-block">
											Iniciar sesión
										</button>
										<div id="login-alert" class="mt-3" style="display:none;"></div>

									</form>
									<hr>
									<div class="text-center">
										<a class="small" href="forgot-password.html">Forgot Password?</a>
									</div>
									<div class="text-center">
										<a class="small" href="register.html">Create an Account!</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

			</div>

		</div>

	</div>

	<!-- Bootstrap core JavaScript-->
	<script src="<?php echo base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
	<script src="<?php echo base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>

	<!-- Core plugin JavaScript-->
	<script src="<?php echo base_url('assets/vendor/jquery-easing/jquery.easing.min.js'); ?>"></script>

	<!-- Custom scripts for all pages-->
	<script src="<?php echo base_url('assets/js/sb-admin-2.min.js'); ?>"></script>

	<!-- Login admin script -->
	<!-- SweetAlert2 -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		// Rutas para el JS de login; generadas por CodeIgniter para respetar index.php y base_url
		window.IMENU = window.IMENU || {};
		window.IMENU.routes = {
			login: '<?php echo site_url('adminauth/login'); ?>',
			// Redirigir al dashboard principal del admin
			admin: '<?php echo site_url('admin/dashboard'); ?>'
		};
		// Endpoints API útiles para verificaciones desde JS
		window.IMENU.api = window.IMENU.api || {};
		window.IMENU.api.tenants = '<?php echo site_url('admin/tenants'); ?>';

		// CSRF token (CodeIgniter) - necesario si csrf_protection = TRUE
		window.IMENU.csrf = {
			name: '<?php echo $this->security->get_csrf_token_name(); ?>',
			hash: '<?php echo $this->security->get_csrf_hash(); ?>',
			cookie_name: '<?php echo $this->config->item('csrf_cookie_name'); ?>'
		};
	</script>
	<script src="<?php echo base_url('assets/js/login-admin.js'); ?>"></script>

</body>

</html>
