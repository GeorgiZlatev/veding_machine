<?php

declare(strict_types=1);

namespace App;

final class Currency
{
    public function __construct(
        private string $code = 'BGN',
        private string $symbol = 'лв.',
        private int $decimals = 2,
    ) {
        if ($this->code === '' || $this->symbol === '') {
            throw new \InvalidArgumentException('Валутата трябва да има код и символ.');
        }

        if ($this->decimals < 0 || $this->decimals > 4) {
            throw new \InvalidArgumentException('Невалиден брой десетични знаци за валутата.');
        }
    }

    public function toMinorUnits(mixed $amount): int
    {
        if (!is_numeric($amount)) {
            throw new \InvalidArgumentException('Стойността трябва да е число.');
        }

        $minorUnits = (int) round((float) $amount * $this->minorUnitFactor());

        if ($minorUnits <= 0) {
            throw new \InvalidArgumentException('Стойността трябва да е по - голяма от 0.');
        }

        return $minorUnits;
    }

    public function format(int $minorUnits): string
    {
        return sprintf(
            '%s %s',
            number_format(
                $minorUnits / $this->minorUnitFactor(),
                $this->decimals,
                '.',
                '',
            ),
            $this->symbol,
        );
    }

    public function metadata(): array
    {
        return [
            'code' => $this->code,
            'symbol' => $this->symbol,
            'decimals' => $this->decimals,
        ];
    }

    public function minorUnitFactor(): int
    {
        return 10 ** $this->decimals;
    }
}
