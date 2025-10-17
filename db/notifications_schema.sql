-- Tabla para almacenar logs de notificaciones
CREATE TABLE IF NOT EXISTS notification_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    tenant_id INT NOT NULL,
    email_sent TINYINT(1) DEFAULT 0,
    webhook_sent TINYINT(1) DEFAULT 0,
    whatsapp_sent TINYINT(1) DEFAULT 0,
    sent_at DATETIME NOT NULL,
    INDEX idx_pedido_id (pedido_id),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_sent_at (sent_at),
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- Agregar campos de notificación a la tabla tenants si no existen
ALTER TABLE tenants 
ADD COLUMN IF NOT EXISTS notif_email VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS notif_webhook VARCHAR(500) NULL,
ADD COLUMN IF NOT EXISTS notif_whatsapp VARCHAR(20) NULL;

-- Agregar nuevos permisos para control granular del sistema
ALTER TABLE permisos
ADD COLUMN can_manage_orders TINYINT DEFAULT 0 AFTER can_adjustments,
ADD COLUMN can_manage_payments TINYINT DEFAULT 0 AFTER can_manage_orders,
ADD COLUMN can_manage_subscriptions TINYINT DEFAULT 0 AFTER can_manage_payments,
ADD COLUMN can_manage_users TINYINT DEFAULT 0 AFTER can_manage_subscriptions,
ADD COLUMN can_manage_plans TINYINT DEFAULT 0 AFTER can_manage_users,
ADD COLUMN can_manage_reports TINYINT DEFAULT 0 AFTER can_manage_plans;

-- Actualizar permisos existentes para usuarios con rol 'owner' (darles acceso completo)
UPDATE permisos p
JOIN users u ON p.user_id = u.id
SET 
    p.can_products = 1,
    p.can_categories = 1,
    p.can_adjustments = 1,
    p.can_manage_orders = 1,
    p.can_manage_payments = 1,
    p.can_manage_subscriptions = 1,
    p.can_manage_users = 1,
    p.can_manage_plans = 1,
    p.can_manage_reports = 1,
    p.can_view_stats = 1
WHERE u.rol = 'owner';

-- Para staff puedes decidir qué permisos otorgar por defecto (ejemplo: solo productos y pedidos)
UPDATE permisos p
JOIN users u ON p.user_id = u.id
SET 
    p.can_products = 1,
    p.can_categories = 1,
    p.can_manage_orders = 1,
    p.can_view_stats = 1
WHERE u.rol = 'staff';

-- 🧹 Limpieza previa opcional (solo si estás en desarrollo)
-- DELETE FROM permisos;

-- ✅ Insertar permisos por cada usuario que aún no tenga registro
INSERT INTO permisos (user_id, tenant_id, can_products, can_categories, can_adjustments, can_manage_orders, can_manage_payments, can_manage_subscriptions, can_manage_users, can_manage_plans, can_manage_reports, can_view_stats, created_at)
SELECT 
    u.id,
    u.tenant_id,
    CASE 
        WHEN u.rol = 'owner' THEN 1
        WHEN u.rol = 'staff' THEN 1
        ELSE 0
    END AS can_products,
    CASE 
        WHEN u.rol = 'owner' THEN 1
        WHEN u.rol = 'staff' THEN 1
        ELSE 0
    END AS can_categories,
    CASE 
        WHEN u.rol = 'owner' THEN 1
        ELSE 0
    END AS can_adjustments,
    CASE 
        WHEN u.rol IN ('owner','staff') THEN 1
        ELSE 0
    END AS can_manage_orders,
    CASE 
        WHEN u.rol = 'owner' THEN 1
        ELSE 0
    END AS can_manage_payments,
    CASE 
        WHEN u.rol = 'owner' THEN 1
        ELSE 0
    END AS can_manage_subscriptions,
    CASE 
        WHEN u.rol = 'owner' THEN 1
        ELSE 0
    END AS can_manage_users,
    CASE 
        WHEN u.rol = 'owner' THEN 1
        ELSE 0
    END AS can_manage_plans,
    CASE 
        WHEN u.rol = 'owner' THEN 1
        ELSE 0
    END AS can_manage_reports,
    1 AS can_view_stats,
    NOW()
FROM users u
LEFT JOIN permisos p ON p.user_id = u.id AND p.tenant_id = u.tenant_id
WHERE p.id IS NULL;
