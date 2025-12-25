<?php

require __DIR__.'/../vendor/autoload.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);
$path = rtrim($path, '/');
if (empty($path)) {
    $path = '/';
}

if ($path === '/api/moex/stocks' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $moexService = new \App\Services\MoexService();
        $stocks = $moexService->getStocks();
        
        echo json_encode([
            'success' => true,
            'data' => $stocks
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (preg_match('#^/api/moex/stocks/([^/]+)/history#', $path, $matches)) {
    $secid = $matches[1];
    $days = isset($_GET['days']) ? (int)$_GET['days'] : 90;
    
    try {
        $moexService = new \App\Services\MoexService();
        $history = $moexService->getStockHistory($secid, $days);
        
        echo json_encode([
            'success' => true,
            'data' => $history
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (preg_match('#^/api/moex/stocks/([^/]+)/indicators#', $path, $matches)) {
    $secid = $matches[1];
    $days = isset($_GET['days']) ? (int)$_GET['days'] : 90;
    
    try {
        $moexService = new \App\Services\MoexService();
        $smmaService = new \App\Services\Moex\SMMAService();
        $alligatorService = new \App\Services\Moex\AlligatorIndicatorService($smmaService);
        $aoService = new \App\Services\Moex\AwesomeOscillatorService();
        $reversalBarsService = new \App\Services\Moex\ReversalBarsService();
        $analysisService = new \App\Services\Moex\MarketAnalysisService();
        $signalsService = new \App\Services\Moex\TradingSignalsService();
        
        $indicatorsService = new \App\Services\Moex\BillWilliamsIndicatorsService(
            $moexService,
            $alligatorService,
            $aoService,
            $reversalBarsService,
            $analysisService,
            $signalsService
        );
        
        $indicators = $indicatorsService->getIndicators($secid, $days);
        
        echo json_encode([
            'success' => true,
            'data' => $indicators
        ], JSON_UNESCAPED_UNICODE);
        exit;
        
    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if ($path === '/' || $path === '') {
    echo json_encode([
        'success' => true,
        'message' => 'Bill Williams Indicators API',
        'version' => '1.0.0',
        'endpoints' => [
            'GET /api/moex/stocks' => 'Список акций MOEX',
            'GET /api/moex/stocks/{secid}/history?days={days}' => 'Исторические данные акции',
            'GET /api/moex/stocks/{secid}/indicators?days={days}' => 'Индикаторы Билла Вильямса'
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

http_response_code(404);
echo json_encode([
    'success' => false,
    'error' => 'Endpoint not found',
    'available_endpoints' => [
        'GET /api/moex/stocks',
        'GET /api/moex/stocks/{secid}/history?days={days}',
        'GET /api/moex/stocks/{secid}/indicators?days={days}'
    ]
], JSON_UNESCAPED_UNICODE);
