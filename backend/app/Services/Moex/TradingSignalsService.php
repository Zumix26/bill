<?php

namespace App\Services\Moex;

class TradingSignalsService
{
    public function calculate(array $history, array $alligator, array $ao, array $reversalBars): array
    {
        $signals = [];
        $closePrices = array_column(array_column($history, 'y'), 3);

        foreach ($history as $index => $h) {
            if ($index < 1) continue;

            $reasons = [];
            $signalType = null;
            $confidence = 'low';

            if ($index >= 33) {
                $aoCurrent = $ao[$index]['value'] ?? null;
                $aoPrev = $ao[$index - 1]['value'] ?? null;

                if ($aoCurrent !== null && $aoPrev !== null) {
                    if ($aoPrev < 0 && $aoCurrent > 0) {
                        $reasons[] = 'AO: пересечение нулевой линии вверх';
                        $signalType = 'buy';
                        $confidence = 'high';
                    } elseif ($aoPrev > 0 && $aoCurrent < 0) {
                        $reasons[] = 'AO: пересечение нулевой линии вниз';
                        $signalType = 'sell';
                        $confidence = 'medium';
                    }
                }
            }

            foreach ($reversalBars as $bar) {
                if ($bar['index'] === $index && $bar['confidence'] === 'high') {
                    $reasons[] = "Разворотный бар: {$bar['pattern']}";
                    if (!$signalType) {
                        $signalType = $bar['type'];
                        $confidence = $bar['confidence'];
                    }
                    break;
                }
            }

            if ($signalType && !empty($reasons)) {
                $signals[] = [
                    'date' => $h['x'],
                    'type' => $signalType,
                    'price' => $closePrices[$index],
                    'reason' => implode(', ', $reasons),
                    'confidence' => $confidence
                ];
            }
        }

        return $signals;
    }
}
