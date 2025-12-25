<?php

namespace App\Services\Moex;

class MarketAnalysisService
{
    public function analyze(array $history, array $alligator, array $ao, array $reversalBars): ?array
    {
        if (empty($history)) {
            return null;
        }

        $currentIndex = count($history) - 1;
        $currentPrice = $history[$currentIndex]['y'][3];
        $currentAlligator = $alligator[$currentIndex] ?? null;
        $currentAO = $ao[$currentIndex] ?? null;

        $analysis = [
            'currentSituation' => '',
            'trend' => 'sideways',
            'alligatorState' => 'sleeping',
            'potentialEntryPoints' => [],
            'riskLevel' => 'medium',
            'recommendations' => []
        ];

        if ($currentAlligator && $currentAlligator['jaw'] !== null) {
            $jaw = $currentAlligator['jaw'];
            $teeth = $currentAlligator['teeth'];
            $lips = $currentAlligator['lips'];

            $isAwakening = $lips > $teeth && $teeth > $jaw;
            $isSleeping = $lips < $teeth && $teeth < $jaw;

            if ($isAwakening) {
                $analysis['alligatorState'] = 'awakening';
                if ($currentPrice > $lips) {
                    $analysis['trend'] = 'bullish';
                    $analysis['currentSituation'] .= 'Аллигатор пробуждается, цена выше губ. ';
                    $analysis['recommendations'][] = 'Потенциально хороший момент для входа в длинную позицию';
                }
            } elseif ($isSleeping) {
                $analysis['alligatorState'] = 'sleeping';
                if ($currentPrice < $lips) {
                    $analysis['trend'] = 'bearish';
                    $analysis['currentSituation'] .= 'Аллигатор спит, цена ниже губ. ';
                    $analysis['recommendations'][] = 'Рынок в консолидации, лучше подождать пробуждения';
                }
            }
        }

        if ($currentAO && $currentAO['value'] !== null && $currentIndex > 0) {
            $aoPrev = $ao[$currentIndex - 1]['value'] ?? null;
            if ($aoPrev !== null) {
                if ($aoPrev < 0 && $currentAO['value'] > 0) {
                    $analysis['recommendations'][] = 'AO пересек нулевую линию вверх - бычий сигнал';
                } elseif ($aoPrev > 0 && $currentAO['value'] < 0) {
                    $analysis['recommendations'][] = 'AO пересек нулевую линию вниз - медвежий сигнал';
                }
            }
        }

        $lastBarIndex = count($history) - 1;
        $todayReversalBar = null;
        foreach ($reversalBars as $bar) {
            if ($bar['index'] === $lastBarIndex) {
                $todayReversalBar = $bar;
                break;
            }
        }

        if ($todayReversalBar) {
            $analysis['recommendations'][] = "Разворотный бар сегодня: {$todayReversalBar['pattern']} ({$todayReversalBar['type']})";
        }

        return $analysis;
    }
}
