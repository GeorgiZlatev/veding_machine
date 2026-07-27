<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

final class Wallet
{
    private array $stock;
    private int $balance;

    public function __construct(array $stock = [], int $balance = 0)
    {
        $this->stock = $stock;
        $this->balance = $balance;
    }

    public function balance(): int
    {
        return $this->balance;
    }

    public function insert(int $coin): void
    {
        $this->balance += $coin;
        $this->stock[$coin] = ($this->stock[$coin] ?? 0) + 1;
    }

    public function charge(int $price): void
    {
        if ($this->balance < $price) {
            throw new \RuntimeException('Недостатъчна сума.');
        }

        $this->balance -= $price;
    }

    public function giveChange(array $allowedCoins): array
    {
        if ($this->balance === 0) {
            throw new \RuntimeException('Няма ресто за връщане.');
        }

        rsort($allowedCoins);
        $remaining = $this->balance;
        $change = [];

        foreach ($allowedCoins as $coin) {
            $vailable = $this->stock[$coin] ?? 0;
            $count = min(intdiv($remaining, $coin), $vailable);

            if ($count > 0) {
                $change[$coin] = $count;
                $remaining -= $coin * $count;
            }
        }

        if ($remaining !== 0) {
            throw new RuntimeException('Автоматът няма подходящо ресто.');
        }

        foreach ($change as $coin => $count) {
            $this->stock[$coin] -= $count;
        }

        $this->balance = 0;

        return $change;
    }

    public function stock(): array
    {
        return $this->stock;
    }
}
