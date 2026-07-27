<?php

declare(strict_types=1);

namespace App;

final class Currency
{
    public function toCents(mixed $amount): int
    {
        if (!is_numeric($amount)) {
            throw new \InvalidArgumentException('Стойността трябва да е число.');
        }

        $cents = (int) round((float) $amount * 100);

        if ($cents <= 0) {
            throw new \InvalidArgumentException('Стойността трябва да по - голяма от 0.');
        }
        return $cents;
    }

    public function format(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '') . ' лв. ';
    }
}
