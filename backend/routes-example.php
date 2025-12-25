<?php

use App\Http\Controllers\API\Moex\MoexController;
use App\Http\Controllers\API\Moex\BillWilliamsIndicatorsController;

Route::prefix('moex')->middleware(['auth:api'])->group(function () {
    Route::get('stocks', [MoexController::class, 'getStocks']);
    Route::get('stocks/{secid}/history', [MoexController::class, 'getStockHistory']);
    Route::get('stocks/{secid}/indicators', [BillWilliamsIndicatorsController::class, 'getIndicators']);
});
