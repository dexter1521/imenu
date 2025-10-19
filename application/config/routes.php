<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['r/(:any)'] = 'public/menu/$1';
$route['api/auth/login'] = 'TenantAuth/login';

$route['api/app/dashboard'] = 'app/dashboard';
$route['api/app/categorias']['get'] = 'app/categorias';
$route['api/app/categoria']['post'] = 'app/categoria_create';
$route['api/app/categoria/(:num)']['post'] = 'app/categoria_update/$1';
$route['api/app/categoria/(:num)']['delete'] = 'app/categoria_delete/$1';

$route['api/app/productos']['get'] = 'app/productos';
$route['api/app/producto']['post'] = 'app/producto_create';
$route['api/app/producto/(:num)']['post'] = 'app/producto_update/$1';
$route['api/app/producto/(:num)']['delete'] = 'app/producto_delete/$1';

$route['api/app/ajustes']['get'] = 'app/ajustes_get';
$route['api/app/ajustes']['post'] = 'app/ajustes_update';

$route['api/admin/tenants']['get'] = 'admin/tenants';
$route['api/admin/tenant']['post'] = 'admin/tenant_create';
$route['api/admin/planes']['get'] = 'admin/planes';
$route['api/admin/plan']['post'] = 'admin/plan_create';
$route['api/admin/pagos']['get'] = 'admin/pagos';

$route['api/public/menu'] = 'public/api_menu';

// ===== Rutas sugeridas (application/config/routes.php) =====
$route['api/app/usuarios']['get']            = 'app/usuarios_list';
$route['api/app/usuario']['post']            = 'app/usuario_create';
$route['api/app/usuario/(:num)']['post']     = 'app/usuario_update/$1';
$route['api/app/usuario/(:num)']['delete']   = 'app/usuario_delete/$1';
$route['api/app/usuario/(:num)/permisos']['get']  = 'app/permisos_get/$1';
$route['api/app/usuario/(:num)/permisos']['post'] = 'app/permisos_update/$1';


// Público
$route['api/public/pedido']['post'] = 'public/crear_pedido';

// Panel (JWT)
$route['api/app/pedidos']['get']        = 'app/pedidos';
$route['api/app/pedido/(:num)']['get']  = 'app/pedido/$1';


// Ruta para el login de administradores saas (global)
$route['admin/auth'] = 'AdminAuth/login';
$route['adminauth/login'] = 'AdminAuth/login';
$route['adminauth/logout'] = 'AdminAuth/logout';

// Panel administrativo SaaS - Landing de login (sin autenticación)
$route['adminpanel/login'] = 'AdminPanel/login';

// Vistas del panel admin (requieren autenticación JWT con rol admin)
$route['admin/dashboard'] = 'Admin/dashboard';
$route['admin/tenants_view'] = 'Admin/tenants_view';
$route['admin/planes_view'] = 'Admin/planes_view';
$route['admin/pagos_view'] = 'Admin/pagos_view';
$route['admin/suscripciones_view'] = 'Admin/suscripciones_view';
$route['admin/dashboard_view'] = 'Admin/dashboard_view';

// API endpoints del panel admin
$route['admin/dashboard_stats'] = 'Admin/dashboard_stats';
$route['api/admin/tenants']['get'] = 'Admin/tenants';
$route['api/admin/tenant']['post'] = 'Admin/tenant_create';
$route['api/admin/tenant/(:num)']['post'] = 'Admin/tenant_update/$1';
$route['api/admin/tenant/(:num)']['delete'] = 'Admin/tenant_delete/$1';
$route['api/admin/planes']['get'] = 'Admin/planes';
$route['api/admin/plan']['post'] = 'Admin/plan_create';
$route['api/admin/plan/(:num)']['post'] = 'Admin/plan_update/$1';
$route['api/admin/plan/(:num)']['delete'] = 'Admin/plan_delete/$1';
$route['api/admin/pagos']['get'] = 'Admin/pagos';
$route['admin/pago_stats'] = 'Admin/pago_stats';
$route['admin/pago_detail/(:num)'] = 'Admin/pago_detail/$1';
$route['admin/pago_export'] = 'Admin/pago_export';

// Ruta para el login de tenants (global)
$route['app/auth'] = 'TenantAuth/login';
$route['tenantauth/login'] = 'TenantAuth/login';
$route['tenantauth/logout'] = 'TenantAuth/logout';

// Compatibilidad para logout antiguo
$route['api/auth/logout'] = 'TenantAuth/logout';
