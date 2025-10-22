                </div>
                <!-- /.container-fluid -->

                </div>
                <!-- End of Main Content -->

                <!-- Footer -->
                <footer class="sticky-footer bg-white">
                	<div class="container my-auto">
                		<div class="copyright text-center my-auto">
                			<span>Copyright &copy; iMenu <?php echo date('Y'); ?></span>
                		</div>
                	</div>
                </footer>
                <!-- End of Footer -->

                </div>
                <!-- End of Content Wrapper -->

                </div>
                <!-- End of Page Wrapper -->

                <!-- Scroll to Top Button-->
                <a class="scroll-to-top rounded" href="#page-top">
                	<i class="fas fa-angle-up"></i>
                </a>

                <!-- Logout Modal-->
                <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                	<div class="modal-dialog" role="document">
                		<div class="modal-content">
                			<div class="modal-header">
                				<h5 class="modal-title" id="exampleModalLabel">¿Listo para salir?</h5>
                				<button class="close" type="button" data-dismiss="modal" aria-label="Close">
                					<span aria-hidden="true">×</span>
                				</button>
                			</div>
                			<div class="modal-body">Selecciona "Cerrar Sesión" si estás listo para terminar tu sesión actual.</div>
                			<div class="modal-footer">
                				<button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                				<button class="btn btn-primary" id="btn-logout" onclick="handleLogout()">Cerrar Sesión</button>
                			</div>
                		</div>
                	</div>
                </div>

                <script>
                	function handleLogout() {
                		// Determinar la URL de logout según el contexto
                		const logoutUrl = <?php echo json_encode(isset($logout_url) ? $logout_url : site_url('app/auth/logout')); ?>;
                		const isAdmin = <?php echo json_encode(isset($logout_url)); ?>;

                		// Limpiar localStorage INMEDIATAMENTE
                		localStorage.removeItem('imenu_role');
                		localStorage.removeItem('imenu_tenant');

                		// Hacer request de logout para eliminar cookie en servidor
                		fetch(logoutUrl, {
                				method: 'POST',
                				credentials: 'same-origin',
                				headers: {
                					'Content-Type': 'application/json'
                				}
                			})
                			.then(response => {
                				// No importa si falla, de todas formas redirigir
                				return response.json().catch(() => ({
                					ok: true
                				}));
                			})
                			.then(data => {
                				console.log('Logout response:', data);
                			})
                			.catch(error => {
                				console.error('Error en logout:', error);
                			})
                			.finally(() => {
                				// Pequeño delay para que el navegador procese la eliminación de cookie
                				setTimeout(() => {
                					// SIEMPRE redirigir al login
                					if (isAdmin) {
                						window.location.href = '<?php echo site_url('adminpanel/login'); ?>';
                					} else {
                						window.location.href = '<?php echo site_url('tenantpanel/login'); ?>';
                					}
                				}, 100);
                			});
                	}
                </script>
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

                <!-- SweetAlert2 para alertas bonitas -->
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

                <?php if (isset($extra_js)) echo $extra_js; ?>

                </body>

                </html>
