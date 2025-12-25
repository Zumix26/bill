<?php

namespace App\Services\Moex;
readonly class AlligatorIndicatorService
{

    public function __construct(
        private SMMAService $smmaService
    )
    {}

    public function calculate(array $closePrices, array $highs, array $lows, array $history): array
    {
        $medianPrices = [];
        foreach ($closePrices as $i => $closePrice) {
            $medianPrices[] = ($highs[$i] + $lows[$i] + $closePrice) / 3;
        }

        $jaw = $this->smmaService->calculate($medianPrices, 13, 8);
        $teeth = $this->smmaService->calculate($medianPrices, 8, 5);
        $lips = $this->smmaService->calculate($medianPrices, 5, 3);

        $result = [];
        foreach ($history as $i => $h) {
            $result[] = [
                'x' => $h['x'],
                'jaw' => $i >= 8 ? $jaw[$i - 8] : null,
                'teeth' => $i >= 5 ? $teeth[$i - 5] : null,
                'lips' => $i >= 3 ? $lips[$i - 3] : null
            ];
        }

        return $result;
    }
}

