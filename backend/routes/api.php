<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Moex\BillWilliamsIndicatorsController;

Route::prefix('moex')->group(function () {
    Route::get('stocks/{secid}/indicators', [BillWilliamsIndicatorsController::class, 'getIndicators']);
});
