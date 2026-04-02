-- Esquema de base de datos para facturas electrónicas
CREATE DATABASE IF NOT EXISTS factura_electronica_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE factura_electronica_db;

CREATE TABLE IF NOT EXISTS `facturas_electronicas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_archivo` varchar(255) NOT NULL,
  `contenido_xml` mediumblob NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `numero_factura` varchar(50) DEFAULT NULL,
  `fecha_emision` date DEFAULT NULL,
  `ruc_emisor` varchar(20) DEFAULT NULL,
  `ruc_receptor` varchar(20) DEFAULT NULL,
  `monto_total` decimal(12,2) DEFAULT NULL,
  `serie_numero_guia` varchar(11) DEFAULT NULL,
   PRIMARY KEY (`id`),
   UNIQUE KEY `idx_serie_numero_guia` (`serie_numero_guia`),
   KEY `idx_fecha_emision` (`fecha_emision`),
   KEY `idx_ruc_emisor` (`ruc_emisor`),
   KEY `idx_ruc_receptor` (`ruc_receptor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;