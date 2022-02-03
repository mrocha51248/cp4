<?php

namespace App\Service;

use DateInterval;

class DateIntervalConverter
{
    public function getMillisecondsTotal(DateInterval $dateInterval): int
    {
        $total = intval($dateInterval->f * 1000);
        $total += $dateInterval->s * 1000;
        $total += $dateInterval->i * 1000 * 60;
        $total += $dateInterval->h * 1000 * 60 * 60;
        $total += $dateInterval->days * 1000 * 60 * 60 * 24;
        $total *= $dateInterval->invert ? -1 : 1;
        return $total;
    }
}
