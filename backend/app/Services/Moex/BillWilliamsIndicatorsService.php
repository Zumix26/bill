<?php

namespace App\Services\Moex;

use App\Services\MoexService;
readonly class BillWilliamsIndicatorsService
{
    public function __construct(
        private MoexService               $moexService,
        private AlligatorIndicatorService $alligatorService,
        private AwesomeOscillatorService  $aoService,
        private ReversalBarsService       $reversalBarsService,
        private MarketAnalysisService     $analysisService,
        private TradingSignalsService     $signalsService
    ) {}

    public function getIndicators(string $secid, int $days = 30): array
    {
        $history = $this->moexService->getStockHistory($secid, $days);

        if (empty($history)) {
            return $this->getEmptyResponse();
        }

        $close = array_column(array_column($history, 'y'), 3);
        $high = array_column(array_column($history, 'y'), 1);
        $low = array_column(array_column($history, 'y'), 2);
        $open = array_column(array_column($history, 'y'), 0);

        $alligator = $this->alligatorService->calculate($close, $high, $low, $history);
        $ao = $this->aoService->calculate($high, $low, $history);
        $reversalBars = $this->reversalBarsService->calculate($history);

        $analysis = $this->analysisService->analyze($history, $alligator, $ao, $reversalBars);
        $tradeSignals = $this->signalsService->calculate($history, $alligator, $ao, $reversalBars);

        $lastBarIndex = count($history) - 1;
        $todayReversalBar = null;
        foreach ($reversalBars as $bar) {
            if ($bar['index'] === $lastBarIndex) {
                $todayReversalBar = $bar;
                break;
            }
        }

        return [
            'alligator' => $alligator,
            'ao' => $ao,
            'reversalBars' => $reversalBars,
            'tradeSignals' => $tradeSignals,
            'marketAnalysis' => $analysis,
            'todayReversalBar' => $todayReversalBar
        ];
    }

    private function getEmptyResponse(): array
    {
        return [
            'alligator' => [],
            'ao' => [],
            'reversalBars' => [],
            'tradeSignals' => [],
            'marketAnalysis' => null,
            'todayReversalBar' => null
        ];
    }
}
