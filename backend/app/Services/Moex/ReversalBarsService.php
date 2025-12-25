<?php

namespace App\Services\Moex;

class ReversalBarsService
{
    public function calculate(array $history): array
    {
        $reversalBars = [];

        if (count($history) < 3) {
            return $reversalBars;
        }

        for ($i = 1; $i < count($history) - 1; $i++) {
            $prev = $history[$i - 1];
            $current = $history[$i];

            $prevOpen = $prev['y'][0];
            $prevHigh = $prev['y'][1];
            $prevLow = $prev['y'][2];
            $prevClose = $prev['y'][3];

            $currOpen = $current['y'][0];
            $currHigh = $current['y'][1];
            $currLow = $current['y'][2];
            $currClose = $current['y'][3];

            $currBody = abs($currClose - $currOpen);
            $currRange = $currHigh - $currLow;

            if ($currRange <= 0) continue;

            $pattern = null;
            $type = 'neutral';
            $confidence = 'low';

            if ($prevClose < $prevOpen && $currClose > $currOpen &&
                $currOpen < $prevClose && $currClose > $prevOpen) {
                $pattern = 'Поглощающий бычий';
                $type = 'buy';
                $confidence = 'high';
            }
            elseif ($prevClose > $prevOpen && $currClose < $currOpen &&
                     $currOpen > $prevClose && $currClose < $prevOpen) {
                $pattern = 'Поглощающий медвежий';
                $type = 'sell';
                $confidence = 'high';
            }
            elseif ($currBody < $currRange * 0.3 &&
                     $currLow < min($currOpen, $currClose) - $currBody * 2 &&
                     ($currHigh - max($currOpen, $currClose)) < $currBody &&
                     $prevClose < $prevOpen) {
                $pattern = 'Молот';
                $type = 'buy';
                $confidence = 'medium';
            }
            elseif ($currBody < $currRange * 0.3 &&
                     $currLow < min($currOpen, $currClose) - $currBody * 2 &&
                     ($currHigh - max($currOpen, $currClose)) < $currBody &&
                     $prevClose > $prevOpen) {
                $pattern = 'Повешенный';
                $type = 'sell';
                $confidence = 'medium';
            }
            elseif ($currBody < $currRange * 0.4 &&
                     $currLow < min($currOpen, $currClose) - $currBody * 3 &&
                     ($currHigh - max($currOpen, $currClose)) < $currBody * 0.5 &&
                     $prevClose < $prevOpen) {
                $pattern = 'Пин-бар бычий';
                $type = 'buy';
                $confidence = 'high';
            }
            elseif ($currBody < $currRange * 0.4 &&
                     ($currHigh - max($currOpen, $currClose)) > $currBody * 3 &&
                     (min($currOpen, $currClose) - $currLow) < $currBody * 0.5 &&
                     $prevClose > $prevOpen) {
                $pattern = 'Пин-бар медвежий';
                $type = 'sell';
                $confidence = 'high';
            }
            elseif ($currBody < $currRange * 0.3 &&
                     ($currHigh - max($currOpen, $currClose)) > $currBody * 2 &&
                     (min($currOpen, $currClose) - $currLow) < $currBody &&
                     $prevClose > $prevOpen) {
                $pattern = 'Падающая звезда';
                $type = 'sell';
                $confidence = 'medium';
            }
            elseif ($currBody < $currRange * 0.3 &&
                     ($currHigh - max($currOpen, $currClose)) > $currBody * 2 &&
                     (min($currOpen, $currClose) - $currLow) < $currBody &&
                     $prevClose < $prevOpen) {
                $pattern = 'Перевернутый молот';
                $type = 'buy';
                $confidence = 'medium';
            }

            if ($pattern && $type !== 'neutral') {
                $reversalBars[] = [
                    'x' => $current['x'],
                    'y' => $currClose,
                    'pattern' => $pattern,
                    'type' => $type,
                    'confidence' => $confidence,
                    'index' => $i,
                    'price' => $currClose,
                    'open' => $currOpen,
                    'high' => $currHigh,
                    'low' => $currLow,
                    'close' => $currClose
                ];
            }
        }

        return $reversalBars;
    }
}
