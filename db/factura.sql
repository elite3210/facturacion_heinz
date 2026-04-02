-- Creación de la tabla para almacenar facturas electrónicas
CREATE TABLE facturas_electronicas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_archivo VARCHAR(255) NOT NULL,
    contenido_xml MEDIUMBLOB NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Campos adicionales opcionales para mejorar las búsquedas
    numero_factura VARCHAR(50) NULL,
    fecha_emision DATE NULL,
    ruc_emisor VARCHAR(20) NULL,
    ruc_receptor VARCHAR(20) NULL,
    monto_total DECIMAL(12,2) NULL
);

-- Índices para mejorar el rendimiento en búsquedas comunes
CREATE INDEX idx_numero_factura ON facturas_electronicas(numero_factura);
CREATE INDEX idx_fecha_emision ON facturas_electronicas(fecha_emision);
CREATE INDEX idx_ruc_emisor ON facturas_electronicas(ruc_emisor);
CREATE INDEX idx_ruc_receptor ON facturas_electronicas(ruc_receptor);