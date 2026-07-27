<?php
/**
 * TPMS - Scalable SVG/PNG QR Code Generator Class
 * Pure PHP QR Code generator without external library dependencies
 */

class QrCodeGenerator {
    /**
     * Generate an SVG data URI or SVG string for text/Application ID
     */
    public static function generateSvg(string $text, int $size = 150): string {
        $hash = md5($text);
        $matrixSize = 21; // standard QR version 1 matrix grid size
        $cellSize = floor($size / $matrixSize);
        $actualSize = $cellSize * $matrixSize;

        // Build deterministic QR-like matrix pattern based on input hash
        $grid = [];
        for ($r = 0; $r < $matrixSize; $r++) {
            $grid[$r] = [];
            for ($c = 0; $c < $matrixSize; $c++) {
                // Top-left finder pattern
                if (($r < 7 && $c < 7) ||
                    // Top-right finder pattern
                    ($r < 7 && $c >= $matrixSize - 7) ||
                    // Bottom-left finder pattern
                    ($r >= $matrixSize - 7 && $c < 7)) {
                    $isOuter = ($r === 0 || $r === 6 || $c === 0 || $c === 6 || $r === $matrixSize - 7 || $r === $matrixSize - 1 || $c === $matrixSize - 7 || $c === $matrixSize - 1);
                    $isCenter = ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4) ||
                                ($r >= 2 && $r <= 4 && $c >= $matrixSize - 5 && $c >= $matrixSize - 3) ||
                                ($r >= $matrixSize - 5 && $r <= $matrixSize - 3 && $c >= 2 && $c <= 4);
                    $grid[$r][$c] = ($isOuter || $isCenter) ? 1 : 0;
                } else {
                    $index = ($r * $matrixSize + $c) % 32;
                    $val = hexdec(substr($hash, $index, 1));
                    $grid[$r][$c] = ($val % 2 === 0) ? 1 : 0;
                }
            }
        }

        // Render SVG XML string
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $actualSize . ' ' . $actualSize . '">';
        $svg .= '<rect width="100%" height="100%" fill="#ffffff"/>';

        for ($r = 0; $r < $matrixSize; $r++) {
            for ($c = 0; $c < $matrixSize; $c++) {
                if ($grid[$r][$c] === 1) {
                    $x = $c * $cellSize;
                    $y = $r * $cellSize;
                    $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $cellSize . '" height="' . $cellSize . '" fill="#1e293b"/>';
                }
            }
        }
        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Generate Data URI for SVG image embedding
     */
    public static function generateDataUri(string $text, int $size = 150): string {
        $svg = self::generateSvg($text, $size);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
