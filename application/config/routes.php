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

// ========================================
// RUTAS PÚBLICAS (sin autenticación)
// ========================================

// Menú público por código QR
$route['r/(:any)'] = 'PublicUser/menu/$1';

// API pública
$route['api/public/menu'] = 'PublicUser/api_menu';
$route['api/public/pedido']['post'] = 'PublicUser/crear_pedido';

// ========================================
// AUTENTICACIÓN TENANT
// ========================================

$route['app/auth'] = 'TenantAuth/login';
$route['tenantauth/login'] = 'TenantAuth/login';
$route['tenantauth/logout'] = 'TenantAuth/logout';
$route['tenantpanel/login'] = 'TenantPanel/login';

// Compatibilidad legacy
$route['api/auth/login'] = 'TenantAuth/login';
$route['api/auth/logout'] = 'TenantAuth/logout';

// ========================================
// PANEL TENANT - VISTAS (requieren JWT)
// ========================================

$route['app/panel/dashboard'] = 'App/dashboard';
$route['app/panel/categorias'] = 'App/categorias_view';
$route['app/panel/productos'] = 'App/productos_view';
$route['app/panel/pedidos'] = 'App/pedidos_view';
$route['app/panel/usuarios'] = 'App/usuarios_view';
$route['app/panel/plan'] = 'App/plan_view';
$route['app/panel/ajustes'] = 'App/ajustes_view';

// ========================================
// PANEL TENANT - API ENDPOINTS (requieren JWT)
// ========================================

// Dashboard
$route['api/app/dashboard'] = 'app/dashboard_data';

// Información del tenant (debug)
$route['api/app/tenant_info']['get'] = 'app/tenant_info';

// Plan y Suscripción
$route['api/app/plan_info']['get'] = 'app/plan_info';

// Categorías
$route['api/app/categorias']['get'] = 'CategoriasService/categorias';
$route['api/app/categoria']['post'] = 'CategoriasService/categoria_create';
$route['api/app/categoria/(:num)']['post'] = 'CategoriasService/categoria_update/$1';
$route['api/app/categoria/(:num)']['delete'] = 'CategoriasService/categoria_delete/$1';

// Productos
$route['api/app/productos']['get'] = 'ProductosService/productos';
$route['api/app/producto']['post'] = 'ProductosService/producto_create';
$route['api/app/producto/(:num)']['post'] = 'ProductosService/producto_update/$1';
$route['api/app/producto/(:num)']['delete'] = 'ProductosService/producto_delete/$1';

// Pedidos
$route['api/app/pedidos']['get'] = 'PedidosService/pedidos';
$route['api/app/pedido/(:num)']['get'] = 'PedidosService/pedido/$1';
$route['api/app/pedido']['post'] = 'PedidosService/pedido_create';
$route['api/app/pedido_update_estado/(:num)']['post'] = 'PedidosService/pedido_update_estado/$1';
$route['api/app/pedido/(:num)']['delete'] = 'PedidosService/pedido_delete/$1';
$route['api/app/pedidos_export']['get'] = 'PedidosService/pedidos_export';

// Staff/Usuarios
$route['api/app/usuarios']['get'] = 'UsuariosService/usuarios_list';
$route['api/app/usuario']['post'] = 'UsuariosService/usuario_create';
$route['api/app/usuario/(:num)']['post'] = 'UsuariosService/usuario_update/$1';
$route['api/app/usuario/(:num)']['delete'] = 'UsuariosService/usuario_delete/$1';
$route['api/app/usuario/(:num)/permisos']['get'] = 'UsuariosService/permisos_get/$1';
$route['api/app/usuario/(:num)/permisos']['post'] = 'UsuariosService/permisos_update/$1';

// Ajustes
$route['api/app/ajustes']['get'] = 'AjustesService/ajustes_get';
$route['api/app/ajustes']['post'] = 'AjustesService/ajustes_update';

// ========================================
// AUTENTICACIÓN ADMIN SaaS
// ========================================

$route['admin/auth'] = 'AdminAuth/login';
$route['adminauth/login'] = 'AdminAuth/login';
$route['adminauth/logout'] = 'AdminAuth/logout';
$route['adminpanel/login'] = 'AdminPanel/login';

// ========================================
// PANEL ADMIN - VISTAS (requieren JWT admin)
// ========================================

$route['admin/dashboard'] = 'Admin/dashboard';
$route['admin/tenants_view'] = 'Admin/tenants_view';
$route['admin/planes_view'] = 'Admin/planes_view';
$route['admin/pagos_view'] = 'Admin/pagos_view';
$route['admin/suscripciones_view'] = 'Admin/suscripciones_view';

// ========================================
// PANEL ADMIN - API ENDPOINTS (requieren JWT admin)
// ========================================

// Dashboard
$route['admin/dashboard_stats'] = 'Admin/dashboard_stats';

// Tenants
$route['api/admin/tenants']['get'] = 'TenantsService/tenants';
$route['api/admin/tenant']['post'] = 'TenantsService/tenant_create';
$route['api/admin/tenant/(:num)']['post'] = 'TenantsService/tenant_update/$1';
$route['api/admin/tenant/(:num)']['delete'] = 'TenantsService/tenant_delete/$1';

// Planes
$route['api/admin/planes']['get'] = 'PlanService/planes';
$route['api/admin/plan']['post'] = 'PlanService/plan_create';
$route['api/admin/plan/(:num)']['post'] = 'PlanService/plan_update/$1';
$route['api/admin/plan/(:num)']['delete'] = 'PlanService/plan_delete/$1';

// Pagos
$route['api/admin/pagos']['get'] = 'PagosService/pagos';
$route['admin/pago_stats'] = 'PagosService/pago_stats';
$route['admin/pago_detail/(:num)'] = 'PagosService/pago_detail/$1';
$route['admin/pago_export'] = 'PagosService/pago_export';
