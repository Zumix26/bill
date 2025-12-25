<?php

namespace App\Http\Controllers\API\Moex;

use App\Http\Controllers\Controller;
use App\Services\Moex\BillWilliamsIndicatorsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class BillWilliamsIndicatorsController extends Controller
{
    public function __construct(
        private readonly BillWilliamsIndicatorsService $indicatorsService
    ) {}

    public function getIndicators(string $secid): JsonResponse
    {
        try {
            $days = request()->get('days', 30);
            $indicators = $this->indicatorsService->getIndicators($secid, (int)$days);

            return response()->json([
                'success' => true,
                'data' => $indicators
            ]);
        } catch (\Exception $e) {
            Log::error('Error in BillWilliamsIndicatorsController::getIndicators', [
                'secid' => $secid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при расчете индикаторов',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
