<?php
// ================================================
// iMenu - Seeder de permisos iniciales por rol
// Uso: http://tusitio.com/tools/seed_permissions.php
// ================================================

define('BASEPATH', __DIR__ . '/../'); // Ajusta si es necesario
require_once __DIR__ . '/../index.php'; // Carga CodeIgniter

$CI = &get_instance();
$CI->load->database();

echo "<h2>🔑 Seeder de permisos iMenu</h2>";
echo "<pre>";

// Verificar que la tabla exista
if (!$CI->db->table_exists('permisos')) {
	exit("❌ La tabla 'permisos' no existe. Ejecuta primero la migración o el install.php.\n");
}

// Obtener todos los usuarios sin permisos asignados
$usuarios = $CI->db
	->select('u.id, u.tenant_id, u.rol, u.email')
	->from('users u')
	->join('permisos p', 'p.user_id = u.id AND p.tenant_id = u.tenant_id', 'left')
	->where('p.id IS NULL')
	->get()
	->result();

if (empty($usuarios)) {
	exit("✅ Todos los usuarios ya tienen permisos asignados.\n");
}

// Insertar permisos iniciales por rol
foreach ($usuarios as $u) {

	$permisos = [
		'user_id'                => $u->id,
		'tenant_id'              => $u->tenant_id,
		'can_products'           => ($u->rol === 'owner' || $u->rol === 'staff') ? 1 : 0,
		'can_categories'         => ($u->rol === 'owner' || $u->rol === 'staff') ? 1 : 0,
		'can_adjustments'        => ($u->rol === 'owner') ? 1 : 0,
		'can_manage_orders'      => ($u->rol === 'owner' || $u->rol === 'staff') ? 1 : 0,
		'can_manage_payments'    => ($u->rol === 'owner') ? 1 : 0,
		'can_manage_subscriptions' => ($u->rol === 'owner') ? 1 : 0,
		'can_manage_users'       => ($u->rol === 'owner') ? 1 : 0,
		'can_manage_plans'       => ($u->rol === 'owner') ? 1 : 0,
		'can_manage_reports'     => ($u->rol === 'owner') ? 1 : 0,
		'can_view_stats'         => 1,
		'created_at'             => date('Y-m-d H:i:s')
	];

	$CI->db->insert('permisos', $permisos);
	echo "✅ Permisos creados para usuario: {$u->email} [rol: {$u->rol}]\n";
}

echo "\n🎉 Seeder completado con éxito. Ahora todos los usuarios tienen permisos base.\n";
echo "</pre>";
