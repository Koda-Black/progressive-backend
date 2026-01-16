<?php
/**
 * QR Code Controller - Generate QR codes for tables
 */

declare(strict_types=1);

namespace ProgressiveBar\Controllers;

use ProgressiveBar\Core\Request;
use ProgressiveBar\Core\Response;
use ProgressiveBar\Core\App;

class QrController
{
    /**
     * POST /api/admin/qr/generate
     * Generate a single QR code for a table
     */
    public function generate(Request $request): void
    {
        $body = $request->getBody();
        $tableNumber = $body['tableNumber'] ?? null;
        
        if (!$tableNumber) {
            Response::error('Table number is required', 400);
            return;
        }
        
        // Validate table format
        $tableNumber = strtoupper(trim($tableNumber));
        if (!preg_match('/^T\d{2}$/', $tableNumber)) {
            Response::error('Invalid table number format. Use T01-T99', 400);
            return;
        }
        
        $app = App::getInstance();
        $baseUrl = $app->config('qr.base_url', 'http://localhost:3001');
        $tableParam = $app->config('qr.table_param', 'table');
        
        // Generate the URL that the QR code will point to
        $qrUrl = "{$baseUrl}?{$tableParam}={$tableNumber}";
        
        // Generate QR code using Google Charts API (simple, no library needed)
        $qrSize = $body['size'] ?? 300;
        $qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$qrSize}x{$qrSize}&data=" . urlencode($qrUrl);
        
        Response::success([
            'tableNumber' => $tableNumber,
            'url' => $qrUrl,
            'qrCodeUrl' => $qrImageUrl,
            'size' => $qrSize,
        ]);
    }

    /**
     * GET /api/admin/qr/batch
     * Generate QR codes for multiple tables
     */
    public function batchGenerate(Request $request): void
    {
        $startTable = (int) $request->getQuery('start', 1);
        $endTable = (int) $request->getQuery('end', 10);
        $size = (int) $request->getQuery('size', 300);
        
        // Validate range
        if ($startTable < 1 || $startTable > 99) {
            Response::error('Start table must be between 1 and 99', 400);
            return;
        }
        
        if ($endTable < $startTable || $endTable > 99) {
            Response::error('End table must be between start and 99', 400);
            return;
        }
        
        if ($endTable - $startTable > 50) {
            Response::error('Maximum 50 tables per batch', 400);
            return;
        }
        
        $app = App::getInstance();
        $baseUrl = $app->config('qr.base_url', 'http://localhost:3001');
        $tableParam = $app->config('qr.table_param', 'table');
        
        $qrCodes = [];
        
        for ($i = $startTable; $i <= $endTable; $i++) {
            $tableNumber = sprintf('T%02d', $i);
            $qrUrl = "{$baseUrl}?{$tableParam}={$tableNumber}";
            $qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($qrUrl);
            
            $qrCodes[] = [
                'tableNumber' => $tableNumber,
                'url' => $qrUrl,
                'qrCodeUrl' => $qrImageUrl,
            ];
        }
        
        Response::success([
            'qrCodes' => $qrCodes,
            'count' => count($qrCodes),
            'size' => $size,
        ]);
    }
}
