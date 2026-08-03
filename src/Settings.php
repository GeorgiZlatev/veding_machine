<?php

declare(strict_types=1);

namespace App;

final class Settings
{
    private array $drinks;
    private array $coins;

    public function __construct(array $drinks, array $coins)
    {
        $this->drinks = $drinks;
        $this->coins = array_values(array_unique($coins));
        sort($this->coins);

        if ($this->coins === []) {
            throw new \InvalidArgumentException('Трябва да има поне една приета монета');
        }
    }

    public function drinks(): array
    {
        return $this->drinks;
    }

    public function coins(): array
    {
        return $this->coins;
    }

    public function hasCoin(int $coin): bool
    {
        return in_array($coin, $this->coins, true);
    }

    public function takeDrink(string $id): array
    {
        if (!isset($this->drinks[$id])) {
            throw new \InvalidArgumentException('Напитката не е намерена.');
        }

        --$this->drinks[$id]['quantity'];
        return $this->drinks[$id];
    }

    public function addDrink(string $name, int $price, int $quantity): void
    {
        $name = trim($name);
        if ($name === '' || $price <= 0 || $quantity < 0) {
            throw new \InvalidArgumentException('Невалидни данни за напитка.');
        }

        $id = sprintf('drink%s', bin2hex(random_bytes(4)));
        $this->drinks[$id] = [
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
        ];
    }

    public function removeDrink(string $id): void
    {
        if (!isset($this->drinks[$id])) {
            throw new \InvalidArgumentException('Напитката не е намерена.');
        }
        unset($this->drinks[$id]);
    }

    public function addCoin(int $coin): void
    {
        if ($this->hasCoin($coin)) {
            throw new \InvalidArgumentException('Тази монета вече се приема');
        }

        $this->coins[] = $coin;
        sort($this->coins);
    }

    public function removeCoin(int $coin): void
    {
        if (!$this->hasCoin($coin)) {
            throw new \InvalidArgumentException('Тази монета не се приема.');
        }

        if (count($this->coins) === 1) {
            throw new \InvalidArgumentException('Не може да се премахне последната монета.');
        }

        $this->coins = array_values(array_filter($this->coins, static fn(int $item): bool => $item !== $coin));
    }
}
