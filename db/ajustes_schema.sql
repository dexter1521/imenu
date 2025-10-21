-- Script para crear/actualizar tabla de ajustes del restaurante
-- Ejecutar este script para tener todos los campos necesarios

-- Crear tabla si no existe
CREATE TABLE IF NOT EXISTS `ajustes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `nombre_negocio` varchar(255) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `color_primario` varchar(7) DEFAULT '#F50087',
  `logo_url` varchar(500) DEFAULT NULL,
  `mostrar_precios` tinyint(1) DEFAULT 1,
  `mostrar_imagenes` tinyint(1) DEFAULT 1,
  `aceptar_pedidos` tinyint(1) DEFAULT 1,
  `idioma` varchar(5) DEFAULT 'es',
  `moneda` varchar(3) DEFAULT 'MXN',
  `formato_precio` varchar(20) DEFAULT '$0.00',
  `zona_horaria` varchar(100) DEFAULT 'America/Mexico_City',
  `mensaje_bienvenida` text DEFAULT NULL,
  `notas_menu` text DEFAULT NULL,
  `mensaje_pedido` text DEFAULT NULL,
  `pie_menu` varchar(500) DEFAULT NULL,
  `lunes_abierto` tinyint(1) DEFAULT 1,
  `lunes_inicio` time DEFAULT '09:00:00',
  `lunes_fin` time DEFAULT '22:00:00',
  `martes_abierto` tinyint(1) DEFAULT 1,
  `martes_inicio` time DEFAULT '09:00:00',
  `martes_fin` time DEFAULT '22:00:00',
  `miercoles_abierto` tinyint(1) DEFAULT 1,
  `miercoles_inicio` time DEFAULT '09:00:00',
  `miercoles_fin` time DEFAULT '22:00:00',
  `jueves_abierto` tinyint(1) DEFAULT 1,
  `jueves_inicio` time DEFAULT '09:00:00',
  `jueves_fin` time DEFAULT '22:00:00',
  `viernes_abierto` tinyint(1) DEFAULT 1,
  `viernes_inicio` time DEFAULT '09:00:00',
  `viernes_fin` time DEFAULT '23:00:00',
  `sabado_abierto` tinyint(1) DEFAULT 1,
  `sabado_inicio` time DEFAULT '09:00:00',
  `sabado_fin` time DEFAULT '23:00:00',
  `domingo_abierto` tinyint(1) DEFAULT 0,
  `domingo_inicio` time DEFAULT '10:00:00',
  `domingo_fin` time DEFAULT '20:00:00',
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_id` (`tenant_id`),
  CONSTRAINT `fk_ajustes_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agregar columnas si ya existe la tabla pero le faltan campos
-- (Estos comandos fallarán si las columnas ya existen, pero es seguro)

ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `nombre_negocio` varchar(255) DEFAULT NULL;
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `telefono` varchar(50) DEFAULT NULL;
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `email` varchar(255) DEFAULT NULL;
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `direccion` text DEFAULT NULL;
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `color_primario` varchar(7) DEFAULT '#F50087';
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `logo_url` varchar(500) DEFAULT NULL;
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `mostrar_precios` tinyint(1) DEFAULT 1;
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `mostrar_imagenes` tinyint(1) DEFAULT 1;
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `aceptar_pedidos` tinyint(1) DEFAULT 1;
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `zona_horaria` varchar(100) DEFAULT 'America/Mexico_City';
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `mensaje_bienvenida` text DEFAULT NULL;
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `notas_menu` text DEFAULT NULL;
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `mensaje_pedido` text DEFAULT NULL;
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `pie_menu` varchar(500) DEFAULT NULL;
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `lunes_abierto` tinyint(1) DEFAULT 1;
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `lunes_inicio` time DEFAULT '09:00:00';
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `lunes_fin` time DEFAULT '22:00:00';
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `martes_abierto` tinyint(1) DEFAULT 1;
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `martes_inicio` time DEFAULT '09:00:00';
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `martes_fin` time DEFAULT '22:00:00';
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `miercoles_abierto` tinyint(1) DEFAULT 1;
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `miercoles_inicio` time DEFAULT '09:00:00';
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `miercoles_fin` time DEFAULT '22:00:00';
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `jueves_abierto` tinyint(1) DEFAULT 1;
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `jueves_inicio` time DEFAULT '09:00:00';
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `jueves_fin` time DEFAULT '22:00:00';
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `viernes_abierto` tinyint(1) DEFAULT 1;
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `viernes_inicio` time DEFAULT '09:00:00';
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `viernes_fin` time DEFAULT '23:00:00';
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `sabado_abierto` tinyint(1) DEFAULT 1;
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `sabado_inicio` time DEFAULT '09:00:00';
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `sabado_fin` time DEFAULT '23:00:00';
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `domingo_abierto` tinyint(1) DEFAULT 0;
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `domingo_inicio` time DEFAULT '10:00:00';
ALTER TABLE `ajustes` ADD COLUMN IF NOT EXISTS `domingo_fin` time DEFAULT '20:00:00';

-- Nota: Ejecuta este script en tu base de datos MySQL
-- Puedes ejecutarlo múltiples veces de forma segura
