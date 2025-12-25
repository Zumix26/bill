<?php

namespace App\Services\Moex;

class SMMAService
{
    public function calculate(array $values, int $period, int $shift = 0): array
    {
        $result = [];
        $sum = 0;

        for ($i = 0; $i < count($values); $i++) {
            if ($i < $period) {
                $sum += $values[$i];
                if ($i === $period - 1) {
                    $result[] = $sum / $period;
                }
            } else {
                $result[] = ($result[count($result) - 1] * ($period - 1) + $values[$i]) / $period;
            }
        }

        for ($i = 0; $i < $shift; $i++) {
            array_unshift($result, null);
        }

        return $result;
    }
}
