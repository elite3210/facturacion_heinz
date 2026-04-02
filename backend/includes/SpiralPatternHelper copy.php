<?php
require_once '../assets/TCPDF/tcpdf.php';
// ============================================================================
// ENFOQUE 1: TCPDF (Limitado pero funcional)
// ============================================================================

/**
 * Clase para generar patrón de espiral con TCPDF
 */
/**
 * Clase helper para generar patrón de espiral en cualquier instancia TCPDF
 */
class SpiralPatternHelper {
    
    /**
     * Genera patrón de espiral directamente en la instancia TCPDF proporcionada
     * 
     * @param TCPDF $pdf Instancia de TCPDF donde dibujar
     * @param float $width Ancho de la página
     * @param float $height Alto de la página
     * @param float $opacity Opacidad del patrón (0.1 = muy transparente, 1.0 = opaco)
     */
    public static function generateSpiralPattern($pdf, $width, $height, $opacity = 0.25) {
        
        // Guardar estado actual del PDF
        //$pdf->saveState();
        
        $center_x = $width / 2;
        $center_y = $height / 1 + 80;
        $max_radius = min($width, $height) / 3; // Escalado para TCPDF
        
        $num_spirals = 41;        // Ajustado para mejor visualización
        $noise_factor = 0.70;
        
        // TCPDF no maneja transparencia real, simularemos con colores claros
        for ($spiral_idx = 0; $spiral_idx < $num_spirals; $spiral_idx++) {
            $radius_offset = $spiral_idx * ($max_radius / $num_spirals);
            $points = array();
            
            // Generar puntos de la espiral
            for ($angle_step = 0; $angle_step < 18000; $angle_step += 3) { // Más denso
                $rad = deg2rad($angle_step / 10.0);
                
                // Espiral logarítmica básica
                $r = $radius_offset + ($angle_step / 103) * 2; // Mejor escalado
                
                // Ruido fractal tipo sinusoidal
                $noise = $noise_factor * 20 * sin($rad * 15) * cos($rad * 20);
                $r += $noise;
                
                // Coordenadas polares → cartesianas
                $x = $center_x + $r * cos($rad);
                $y = $center_y + $r * sin($rad);
                
                // Solo agregar si está dentro de los márgenes
                if ($x >= 0 && $x <= $width && $y >= 0 && $y <= $height) {
                    $points[] = array($x, $y);
                }

            }
            
            // Dibujar la espiral con TCPDF
            if (count($points) > 2) {
                // Calcular transparencia simulada
                $base_gray = 240; // Color base más claro 110 muy oscuro
                $alpha_effect = ($spiral_idx * 1); // Efecto de transparencia
                //$gray_value = min(230, $base_gray + $alpha_effect);
                $gray_value = $base_gray;
                
                $pdf->SetDrawColor($gray_value, $gray_value, $gray_value);
                $pdf->SetLineWidth(0.3); // Líneas más finas
                
                // Dibujar líneas conectadas
                for ($i = 0; $i < count($points) - 1; $i++) {
                    $pdf->Line(
                        $points[$i][0], $points[$i][1],
                        $points[$i+1][0], $points[$i+1][1]
                    );
                }
            }
        }
        
        // Restaurar estado del PDF
        //$pdf->restoreState();
    }
    
    /**
     * Versión alternativa con mejor transparencia usando SetAlpha (si está disponible)
     */
    public static function generateSpiralPatternWithAlpha($pdf, $width, $height, $opacity = 0.15) {
        
        // Guardar estado
        $pdf->saveState();
        
        // Intentar usar transparencia real si está disponible
        if (method_exists($pdf, 'SetAlpha')) {
            $pdf->SetAlpha($opacity);
        }
        
        $center_x = $width / 2;
        $center_y = $height / 2;
        $max_radius = min($width, $height) / 6;
        
        $num_spirals = 35;
        $noise_factor = 0.70;
        
        for ($spiral_idx = 0; $spiral_idx < $num_spirals; $spiral_idx++) {
            $radius_offset = $spiral_idx * ($max_radius / $num_spirals);
            $points = array();
            
            for ($angle_step = 0; $angle_step < 18000; $angle_step += 3) {
                $rad = deg2rad($angle_step / 10.0);
                
                $r = $radius_offset + ($angle_step / 44) * 2;
                $noise = $noise_factor * 15 * sin($rad * 15) * cos($rad * 20);
                $r += $noise;
                
                $x = $center_x + $r * cos($rad);
                $y = $center_y + $r * sin($rad);
                
                if ($x >= 0 && $x <= $width && $y >= 0 && $y <= $height) {
                    $points[] = array($x, $y);
                }
            }
            
            if (count($points) > 2) {
                // Color gris más definido para transparencia real
                $pdf->SetDrawColor(150, 150, 150);
                $pdf->SetLineWidth(0.4);
                
                for ($i = 0; $i < count($points) - 1; $i++) {
                    $pdf->Line(
                        $points[$i][0], $points[$i][1],
                        $points[$i+1][0], $points[$i+1][1]
                    );
                }
            }
        }
        
        // Restaurar transparencia
        if (method_exists($pdf, 'SetAlpha')) {
            $pdf->SetAlpha(1);
        }
        
        $pdf->restoreState();
    }
}