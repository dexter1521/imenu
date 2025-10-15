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
