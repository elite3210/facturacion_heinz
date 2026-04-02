CREATE TABLE guias_remision (
    id INT AUTO_INCREMENT PRIMARY KEY,
    serie_numero VARCHAR(20) NOT NULL,
    fecha_emision DATE NOT NULL,
    ruc_emisor VARCHAR(11) NOT NULL,
    razon_social_emisor VARCHAR(200) NOT NULL,
    ruc_destinatario VARCHAR(11) NOT NULL,
    razon_social_destinatario VARCHAR(200) NOT NULL,
    motivo_traslado VARCHAR(100) NOT NULL,
    peso_bruto DECIMAL(12,2) NULL,
    punto_partida TEXT NOT NULL,
    punto_llegada TEXT NOT NULL,
    fecha_traslado DATE NOT NULL,
    contenido_xml MEDIUMBLOB NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (ruc_emisor, serie_numero)
);

CREATE INDEX idx_serie_numero ON guias_remision(serie_numero);
CREATE INDEX idx_fecha_emision ON guias_remision(fecha_emision);
CREATE INDEX idx_ruc_emisor ON guias_remision(ruc_emisor);
CREATE INDEX idx_ruc_destinatario ON guias_remision(ruc_destinatario);