<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * AuthHook – Middleware global con validación JWT + Roles + Permisos DB
 * Se ejecuta antes de cada controlador para proteger el sistema automáticamente.
 */

class AuthHook
{
	public function check_access()
	{
		$CI = &get_instance();
		$router = $CI->router;
		$class  = strtolower($router->fetch_class());
		$method = strtolower($router->fetch_method());

		// Rutas públicas que no requieren autenticación
		$public_controllers = ['publicuser', 'tenantauth', 'adminauth'];
		$public_methods     = ['login', 'register', 'forgot_password', 'api_menu'];

		if (in_array($class, $public_controllers) || in_array($method, $public_methods)) {
			return; // no validar nada aquí
		}

		// Validar token JWT
		if (function_exists('jwt_require')) {
			try {
				jwt_require(); // lanza 401 si el token no existe o es inválido
			} catch (Exception $e) {
				show_error('Token inválido o expirado: ' . $e->getMessage(), 401, 'No autorizado');
			}
		}

		// Validar rol básico
		if (!function_exists('current_role') || !current_role()) {
			show_error('No tienes rol asignado o tu sesión no es válida.', 403, 'Acceso denegado');
		}

		$rol        = current_role();
		$user_id    = current_user_id();
		$tenant_id  = current_tenant_id();

		// 4Excluir admin global de cualquier restricción
		if ($rol === 'admin') {
			return; // acceso total
		}

		// Cargar permisos desde la base de datos
		$CI->load->database();
		$permRow = $CI->db
			->get_where('permisos', ['user_id' => $user_id, 'tenant_id' => $tenant_id], 1)
			->row();

		// Si el usuario no tiene fila de permisos, solo puede ver estadísticas básicas
		if (!$permRow) {
			if (!in_array($class, ['dashboard'])) {
				show_error('No tienes permisos configurados para acceder a este módulo.', 403, 'Acceso denegado');
			}
			return;
		}

		// Mapear controladores con sus permisos correspondientes
		$permission_map = [
			'productos'    => 'can_products',
			'categorias'   => 'can_categories',
			'ajustes'      => 'can_adjustments',
			'pedidos'      => 'can_manage_orders',
			'reportes'     => 'can_view_stats',
			'dashboard'    => 'can_view_stats',
		];

		if (isset($permission_map[$class])) {
			$perm_col = $permission_map[$class];

			// si no existe la columna (permiso nuevo), se permite temporalmente
			if (!property_exists($permRow, $perm_col)) {
				return;
			}

			// 7️⃣ Verificar permiso
			if ((int) $permRow->$perm_col !== 1) {
				show_error("Tu cuenta no tiene permiso para acceder a <b>{$class}</b>.", 403, 'Acceso denegado');
			}
		}
	}
}
