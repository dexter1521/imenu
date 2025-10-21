-- Script para agregar campos de suscripción a la tabla tenants
-- Ejecutar solo si los campos no existen

-- Verificar si la columna suscripcion_activa existe antes de agregarla
SET @dbname = DATABASE
();
SET @tablename = 'tenants';
SET @columnname = 'suscripcion_activa';
SET @preparedStatement = (SELECT
IF(
  (
    SELECT COUNT(*)
FROM INFORMATION_SCHEMA.COLUMNS
WHERE
      (table_name = @tablename)
	AND (table_schema = @dbname)
	AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT
('ALTER TABLE ', @tablename, ' ADD ', @columnname, ' TINYINT(1) DEFAULT 1 COMMENT ''Estado de la suscripción: 1=Activa, 0=Inactiva''')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar si la columna suscripcion_fin existe antes de agregarla
SET @columnname = 'suscripcion_fin';
SET @preparedStatement = (SELECT
IF(
  (
    SELECT COUNT(*)
FROM INFORMATION_SCHEMA.COLUMNS
WHERE
      (table_name = @tablename)
	AND (table_schema = @dbname)
	AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT
('ALTER TABLE ', @tablename, ' ADD ', @columnname, ' DATETIME NULL COMMENT ''Fecha de fin de la suscripción''')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar si la columna plan_id existe antes de agregarla
SET @columnname = 'plan_id';
SET @preparedStatement = (SELECT
IF(
  (
    SELECT COUNT(*)
FROM INFORMATION_SCHEMA.COLUMNS
WHERE
      (table_name = @tablename)
	AND (table_schema = @dbname)
	AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT
('ALTER TABLE ', @tablename, ' ADD ', @columnname, ' INT(11) NULL COMMENT ''ID del plan asociado''')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Crear tabla de planes si no existe
CREATE TABLE
IF NOT EXISTS `planes`
(
  `id` int
(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar
(100) NOT NULL COMMENT 'Nombre del plan (ej: Básico, Pro, Premium)',
  `descripcion` text DEFAULT NULL COMMENT 'Descripción del plan',
  `precio_mensual` decimal
(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio mensual del plan',
  `limite_categorias` int
(11) DEFAULT NULL COMMENT 'Límite de categorías (NULL = ilimitado)',
  `limite_items` int
(11) DEFAULT NULL COMMENT 'Límite de productos (NULL = ilimitado)',
  `limite_pedidos_mes` int
(11) DEFAULT NULL COMMENT 'Límite de pedidos por mes (NULL = ilimitado)',
  `activo` tinyint
(1) DEFAULT 1 COMMENT 'Estado del plan: 1=Activo, 0=Inactivo',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON
UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY
(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar planes de ejemplo si la tabla está vacía
INSERT INTO `planes` (`
nombre`,
`descripcion
`, `precio_mensual`, `limite_categorias`, `limite_items`, `limite_pedidos_mes`, `activo`)
SELECT *
FROM (SELECT 'Gratis', 'Plan básico gratuito', 0.00, 5, 20, 50, 1) AS tmp
WHERE NOT EXISTS (
    SELECT nombre
FROM `planes
` WHERE nombre = 'Gratis'
) LIMIT 1;

INSERT INTO `planes` (`
nombre`,
`descripcion
`, `precio_mensual`, `limite_categorias`, `limite_items`, `limite_pedidos_mes`, `activo`)
SELECT *
FROM (SELECT 'Básico', 'Plan básico con límites moderados', 99.00, 15, 100, 200, 1) AS tmp
WHERE NOT EXISTS (
    SELECT nombre
FROM `planes
` WHERE nombre = 'Básico'
) LIMIT 1;

INSERT INTO `planes` (`
nombre`,
`descripcion
`, `precio_mensual`, `limite_categorias`, `limite_items`, `limite_pedidos_mes`, `activo`)
SELECT *
FROM (SELECT 'Pro', 'Plan profesional con límites amplios', 299.00, 50, 500, 1000, 1) AS tmp
WHERE NOT EXISTS (
    SELECT nombre
FROM `planes
` WHERE nombre = 'Pro'
) LIMIT 1;

INSERT INTO `planes` (`
nombre`,
`descripcion
`, `precio_mensual`, `limite_categorias`, `limite_items`, `limite_pedidos_mes`, `activo`)
SELECT *
FROM (SELECT 'Premium', 'Plan premium sin límites', 599.00, NULL, NULL, NULL, 1) AS tmp
WHERE NOT EXISTS (
    SELECT nombre
FROM `planes
` WHERE nombre = 'Premium'
) LIMIT 1;

-- Agregar índice para plan_id si no existe
SET @indexname = 'idx_plan_id';
SET @preparedStatement = (SELECT
IF(
  (
    SELECT COUNT(*)
FROM INFORMATION_SCHEMA.STATISTICS
WHERE
      (table_name = @tablename)
	AND (table_schema = @dbname)
	AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT
('ALTER TABLE ', @tablename, ' ADD INDEX ', @indexname, ' (plan_id)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SELECT 'Script ejecutado exitosamente. Campos de suscripción agregados.' AS resultado;
