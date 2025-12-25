<?php

namespace App\Services\Moex;

class AwesomeOscillatorService
{
    public function calculate(array $highs, array $lows, array $history): array
    {
        $medianPrices = [];
        foreach ($highs as $i => $high) {
            $medianPrices[] = ($high + $lows[$i]) / 2;
        }

        $smmaService = new SMMAService();
        $smma5 = $smmaService->calculate($medianPrices, 5, 0);
        $smma34 = $smmaService->calculate($medianPrices, 34, 0);

        $result = [];
        foreach ($history as $i => $h) {
            $ao = null;
            if ($i >= 33 && isset($smma5[$i]) && isset($smma34[$i - 33])) {
                $ao = $smma5[$i] - $smma34[$i - 33];
            }
            $result[] = [
                'x' => $h['x'],
                'value' => $ao
            ];
        }

        return $result;
    }
}
