<?php

/**
 * ✅ SVG CORREGIDO - SIN LÍNEAS RECTAS, SOLO ARCOS SEPARADOS
 * Soluciona el problema de líneas que cruzan la página
 */

class SVGSpiralForFacturaFixed
{

    /**
     * ✅ VERSIÓN CORREGIDA - Genera arcos separados en SVG
     * Soluciona el problema de líneas rectas no deseadas
     * 
     * @param float $width Ancho en mm
     * @param float $height Alto en mm  
     * @param float $opacity Opacidad del patrón
     * @return string Código SVG optimizado
     */
    public static function generateOptimizedSVG($width = 210, $height = 297, $opacity = 0.20)
    {

        // Configuración del patrón
        $center_x = $width / 2;
        $center_y = -$height / 1 + 30 + 220;
        $max_radius = min($width, $height) / 3.5;

        $num_spirals = 20;
        $noise_factor = 0.60;
        $max_angle = 26000;
        $angle_step = 8;
        $max_arc_length = 80; // ✅ CLAVE: Longitud máxima por arco

        // Márgenes de seguridad
        $margin = 1;
        $min_x = $margin;
        $max_x = $width - $margin;
        $min_y = $margin;
        $max_y = $height - $margin;

        // Iniciar SVG
        $svg = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $svg .= '<svg width="' . $width . 'mm" height="' . $height . 'mm" ';
        $svg .= 'viewBox="0 0 ' . $width . ' ' . $height . '" ';
        $svg .= 'xmlns="http://www.w3.org/2000/svg">' . "\n";
        $svg .= '<g opacity="' . $opacity . '" stroke="#D0D0D0" stroke-width="0.2" fill="none">' . "\n";

        // ✅ GENERAR ESPIRALES COMO ARCOS SEPARADOS
        for ($spiral_idx = 0; $spiral_idx < $num_spirals; $spiral_idx++) {
            $radius_offset = $spiral_idx * ($max_radius / $num_spirals);

            // ✅ LLAMAR FUNCIÓN QUE MANEJA ARCOS SEPARADOS
            $arcs = self::generateSeparatedArcs(
                $radius_offset,
                $center_x,
                $center_y,
                $noise_factor,
                $max_angle,
                $angle_step,
                $min_x,
                $max_x,
                $min_y,
                $max_y,
                $max_arc_length
            );

            // Añadir cada arco como path separado
            foreach ($arcs as $arc) {
                if (!empty($arc)) {
                    $svg .= '<path d="' . $arc . '" />' . "\n";
                }
            }
        }

        $svg .= '</g>' . "\n";
        $svg .= '</svg>';

        return $svg;
    }

    /**
     * ✅ FUNCIÓN CLAVE: Genera arcos separados para evitar líneas rectas
     * 
     * @return array Array de strings con paths SVG separados
     */
    private static function generateSeparatedArcs($radius_offset, $center_x, $center_y, $noise_factor, $max_angle, $angle_step, $min_x, $max_x, $min_y, $max_y, $max_arc_length)
    {

        $arcs = [];
        $current_arc_points = [];

        for ($angle = 0; $angle < $max_angle; $angle += $angle_step) {
            $rad = deg2rad($angle / 10.0);

            // Calcular posición (algoritmo idéntico al tuyo)
            $r = $radius_offset + ($angle / 120) * 2;
            $noise = $noise_factor * 20 * sin($rad * 15) * cos($rad * 20);
            $r += $noise;

            $x = $center_x + $r * cos($rad);
            $y = $center_y + $r * sin($rad);

            // ✅ LÓGICA CLAVE: Detectar si está en zona segura
            $is_inside_safe_zone = ($x >= $min_x && $x <= $max_x && $y >= $min_y && $y <= $max_y);

            if ($is_inside_safe_zone) {
                // ✅ Punto dentro: añadir al arco actual
                $current_arc_points[] = ['x' => round($x, 2), 'y' => round($y, 2)];

                // Si el arco alcanza longitud máxima, terminarlo y empezar nuevo
                if (count($current_arc_points) >= $max_arc_length) {
                    $arcs[] = self::createSVGPath($current_arc_points);
                    // Continuar desde los últimos 2 puntos para suavidad
                    $current_arc_points = array_slice($current_arc_points, -2);
                }
            } else {
                // ❌ Punto fuera: TERMINAR arco actual y empezar nuevo
                if (count($current_arc_points) > 2) {
                    $arcs[] = self::createSVGPath($current_arc_points);
                }
                $current_arc_points = []; // ✅ REINICIAR - esto evita líneas rectas
            }
        }

        // Terminar último arco si tiene puntos
        if (count($current_arc_points) > 2) {
            $arcs[] = self::createSVGPath($current_arc_points);
        }

        return $arcs;
    }

    /**
     * ✅ Convierte array de puntos en path SVG
     */
    private static function createSVGPath($points)
    {
        if (count($points) < 2) return '';

        $path = 'M ' . $points[0]['x'] . ',' . $points[0]['y'];

        for ($i = 1; $i < count($points); $i++) {
            $path .= ' L ' . $points[$i]['x'] . ',' . $points[$i]['y'];
        }

        return $path;
    }

    /**
     * ✅ VERSIÓN ULTRA-LIGERA con arcos separados
     */
    public static function generateUltraLightSVG($width = 210, $height = 297, $opacity = 0.20)
    {

        $center_x = $width / 2;
        $center_y = $height / 2 + 30;
        $max_radius = min($width, $height) / 6;

        // Configuración ultra-ligera
        $num_spirals = 8;
        $angle_step = 15;
        $max_angle = 6000;
        $max_arc_length = 40; // Arcos más cortos

        $margin = 12;
        $min_x = $margin;
        $max_x = $width - $margin;
        $min_y = $margin;
        $max_y = $height - $margin;

        $svg = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $svg .= '<svg width="' . $width . 'mm" height="' . $height . 'mm" ';
        $svg .= 'viewBox="0 0 ' . $width . ' ' . $height . '" ';
        $svg .= 'xmlns="http://www.w3.org/2000/svg">' . "\n";
        $svg .= '<g opacity="' . $opacity . '" stroke="#E8E8E8" stroke-width="0.15" fill="none">' . "\n";

        for ($spiral_idx = 0; $spiral_idx < $num_spirals; $spiral_idx++) {
            $radius_offset = $spiral_idx * ($max_radius / $num_spirals);

            // Usar misma lógica de arcos separados
            $arcs = self::generateSeparatedArcsLight(
                $radius_offset,
                $center_x,
                $center_y,
                $max_angle,
                $angle_step,
                $min_x,
                $max_x,
                $min_y,
                $max_y,
                $max_arc_length
            );

            foreach ($arcs as $arc) {
                if (!empty($arc)) {
                    $svg .= '<path d="' . $arc . '" />' . "\n";
                }
            }
        }

        $svg .= '</g></svg>';
        return $svg;
    }

    /**
     * ✅ Versión ligera de arcos separados
     */
    private static function generateSeparatedArcsLight($radius_offset, $center_x, $center_y, $max_angle, $angle_step, $min_x, $max_x, $min_y, $max_y, $max_arc_length)
    {

        $arcs = [];
        $current_arc_points = [];

        for ($angle = 0; $angle < $max_angle; $angle += $angle_step) {
            $rad = deg2rad($angle / 10.0);

            $r = $radius_offset + ($angle / 150) * 2;
            $noise = 0.5 * 10 * sin($rad * 12) * cos($rad * 16);
            $r += $noise;

            $x = $center_x + $r * cos($rad);
            $y = $center_y + $r * sin($rad);

            $is_inside = ($x >= $min_x && $x <= $max_x && $y >= $min_y && $y <= $max_y);

            if ($is_inside) {
                $current_arc_points[] = ['x' => round($x, 1), 'y' => round($y, 1)];

                if (count($current_arc_points) >= $max_arc_length) {
                    $arcs[] = self::createSVGPath($current_arc_points);
                    $current_arc_points = array_slice($current_arc_points, -2);
                }
            } else {
                if (count($current_arc_points) > 2) {
                    $arcs[] = self::createSVGPath($current_arc_points);
                }
                $current_arc_points = [];
            }
        }

        if (count($current_arc_points) > 2) {
            $arcs[] = self::createSVGPath($current_arc_points);
        }

        return $arcs;
    }

    /**
     * ✅ Aplicar SVG corregido al PDF
     */
    public static function applyToFacturaPDF($pdf, $width, $height, $version = 'optimized')
    {

        try {
            // Seleccionar versión del SVG
            if ($version === 'ultra-light') {
                $svg_content = self::generateUltraLightSVG($width, $height, 0.30);
            } else {
                $svg_content = self::generateOptimizedSVG($width, $height, 0.30);
            }

            // Embeber SVG en PDF
            $pdf->ImageSVG('@' . $svg_content, 0, 0, $width, $height, '', '', '', 0, false);

            return true;
        } catch (Exception $e) {
            // Fallback mejorado sin líneas rectas
            error_log("SVG falló, usando fallback mejorado: " . $e->getMessage());
            self::drawImprovedFallback($pdf, $width, $height);
            return false;
        }
    }

    /**
     * ✅ Fallback mejorado con arcos separados (sin SVG)
     */
    private static function drawImprovedFallback($pdf, $width, $height)
    {

        $center_x = $width / 2;
        $center_y = $height / 2 + 80;
        $max_radius = min($width, $height) / 6;

        $pdf->SetDrawColor(245, 245, 245);
        $pdf->SetLineWidth(0.1);

        $margin = 1;

        // Solo 5 espirales con arcos separados
        for ($spiral = 0; $spiral < 5; $spiral++) {
            $radius = $spiral * ($max_radius / 5);
            $arc_points = [];

            for ($angle = 0; $angle < 8000; $angle += 12) {
                $rad = deg2rad($angle / 10.0);
                $r = $radius + ($angle / 200) * 2;

                $x = $center_x + $r * cos($rad);
                $y = $center_y + $r * sin($rad);

                $is_safe = ($x >= $margin && $x <= $width - $margin &&
                    $y >= $margin && $y <= $height - $margin);

                if ($is_safe) {
                    $arc_points[] = [$x, $y];

                    // Dibujar arcos de max 30 puntos
                    if (count($arc_points) >= 30) {
                        self::drawPointsAsLines($pdf, $arc_points);
                        $arc_points = [$arc_points[29]]; // Continuar
                    }
                } else {
                    if (count($arc_points) > 2) {
                        self::drawPointsAsLines($pdf, $arc_points);
                    }
                    $arc_points = [];
                }
            }

            if (count($arc_points) > 2) {
                self::drawPointsAsLines($pdf, $arc_points);
            }
        }
    }

    /**
     * ✅ Dibujar puntos como líneas conectadas
     */
    private static function drawPointsAsLines($pdf, $points)
    {
        for ($i = 0; $i < count($points) - 1; $i++) {
            $pdf->Line(
                $points[$i][0],
                $points[$i][1],
                $points[$i + 1][0],
                $points[$i + 1][1]
            );
        }
    }
}

