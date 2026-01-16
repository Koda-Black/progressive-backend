<?php
/**
 * QR Code Generation Service
 */

declare(strict_types=1);

namespace ProgressiveBar\Services;

use ProgressiveBar\Core\App;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeService
{
    private string $baseUrl;
    private string $tableParam;

    public function __construct()
    {
        $app = App::getInstance();
        $this->baseUrl = $app->config('qr.base_url', 'http://localhost:3000');
        $this->tableParam = $app->config('qr.table_param', 'table');
    }

    /**
     * Generate QR code for a specific table
     * 
     * @param string $tableNumber Format: T01, T02, etc.
     * @param string $format 'png' | 'svg' | 'base64'
     * @return string QR code data
     */
    public function generateForTable(string $tableNumber, string $format = 'base64'): string
    {
        $url = $this->buildTableUrl($tableNumber);
        
        $options = new QROptions([
            'outputType' => $this->getOutputType($format),
            'eccLevel' => QRCode::ECC_M, // Medium error correction
            'scale' => 10,
            'imageBase64' => $format === 'base64',
            'bgColor' => [0, 0, 0],      // Black background
            'moduleValues' => [
                // Coral color for QR modules (#FF6B6B)
                1536 => [255, 107, 107],
                6 => [255, 107, 107],
            ],
        ]);
        
        $qrcode = new QRCode($options);
        
        return $qrcode->render($url);
    }

    /**
     * Generate QR codes for multiple tables
     * 
     * @param int $from Start table number (1-99)
     * @param int $to End table number (1-99)
     * @return array Array of [tableNumber => qrCodeData]
     */
    public function generateBatch(int $from, int $to): array
    {
        $from = max(1, min(99, $from));
        $to = max(1, min(99, $to));
        
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }
        
        $results = [];
        
        for ($i = $from; $i <= $to; $i++) {
            $tableNumber = sprintf('T%02d', $i);
            $results[$tableNumber] = $this->generateForTable($tableNumber, 'base64');
        }
        
        return $results;
    }

    /**
     * Build the URL that the QR code points to
     */
    public function buildTableUrl(string $tableNumber): string
    {
        $tableNumber = strtoupper($tableNumber);
        
        return sprintf(
            '%s/?%s=%s',
            rtrim($this->baseUrl, '/'),
            $this->tableParam,
            urlencode($tableNumber)
        );
    }

    /**
     * Validate table number format
     */
    public function isValidTableNumber(string $tableNumber): bool
    {
        return (bool)preg_match('/^T\d{2}$/i', $tableNumber);
    }

    private function getOutputType(string $format): int
    {
        return match ($format) {
            'png' => QRCode::OUTPUT_IMAGE_PNG,
            'svg' => QRCode::OUTPUT_MARKUP_SVG,
            default => QRCode::OUTPUT_IMAGE_PNG,
        };
    }
}
