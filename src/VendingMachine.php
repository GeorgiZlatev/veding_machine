<?php

declare(strict_types=1);

namespace App;

final class VendingMachine
{
    public function __construct(
        private Currency $currency,
        private Settings $settings,
        private Wallet $wallet,
        private Display $display,
    ) {}

    public static function withDefaults(): self
    {
        $currency = new Currency;

        $drinks = [
            'milk' => ['name' => 'Milk', 'price' => 50, 'quantity' => 5],
            'espresso' => ['name' => 'Espresso', 'price' => 40, 'quantity' => 5],
            'long-espresso' => ['name' => 'Long Espresso', 'price' => 70, 'quantity' => 5],
        ];
        $coins = [5, 10, 20, 50, 100];
        $stock = [5 => 10, 10 => 10, 20 => 10, 50 => 10, 100 => 10];

        return new self(
            $currency,
            new Settings($drinks, $coins),
            new Wallet($stock),
            new Display(['Автомата е готов']),
        );
    }

    public function putCoin(mixed $value): void
    {
        $coin = $this->currency->toCents($value);

        if (!$this->settings->hasCoin($coin)) {
            throw new \InvalidArgumentException('Автомата не приема тази монета.');
        }

        $this->wallet->insert($coin);
        $this->display->show('Добавени са ' . $this->currency->format($coin));
    }

    public function buyDrink(string $id): void
    {
        $drink = $this->settings->drinks()[$id] ?? null;

        if ($drink === null) {
            throw new \InvalidArgumentException('Напитката не е намерена.');
        }
        if ($drink['quantity'] < 1) {
            throw new \InvalidArgumentException('Недостатъчна наличвост.');
        }
        if ($this->wallet->balance() < $drink['price']) {
            throw new \InvalidArgumentException('Недостатъчна сума.');
        }

        $this->wallet->charge($drink['price']);
        $this->settings->takeDrink($id);
        $this->display->show("Успешно закупихте '{$drink['name']}'. ");
    }

    public function getCoins(): void
    {
        $change = $this->wallet->giveChange($this->settings->coins());
        $parts = [];

        foreach ($change as $coin => $count) {
            $parts[] = $count . ' x ' . $this->currency->format((int) $coin);
        }

        $this->display->show('Получихте ресто: ' . implode(', ', $parts) . '.');
    }

    public function addDrink(mixed $name, mixed $price, mixed $quantity): void
    {
        if (!is_string($name) || !is_numeric($quantity) || (int) $quantity != $quantity) {
            throw new \InvalidArgumentException('Невалидни данни за напитка.');
        }

        $this->settings->addDrink(
            $name,
            $this->currency->toCents($price),
            (int) $quantity,
        );
        $this->display->show('Добавена е напитка: ' . trim($name) . '.');
    }

    public function addAcceptedCoin(mixed $value): void
    {
        $coin = $this->currency->toCents($value);
        $this->settings->addCoin($coin);
        $this->display->show('Добавен е номинал: ' . $this->currency->format($coin));
    }

    public function reset(): void
    {
        $newMachine = self::withDefaults();
        $this->settings = $newMachine->settings;
        $this->wallet = $newMachine->wallet;
        $this->display = $newMachine->display;
        $this->display->show('Автомата е рестартиран.');
    }

    public function export(): array
    {
        return [
            'drinks' => $this->settings->drinks(),
            'coins' => $this->settings->coins(),
            'stock' => $this->wallet->stock(),
            'balance' => $this->wallet->balance(),
            'messages' => $this->display->messages(),
        ];
    }

    public static function fromState(array $state): self
    {
        return new self(
            new Currency(),
            new Settings($state['drinks'], $state['coins'] ?? $state['coints'] ?? []),
            new Wallet($state['stock'], $state['balance']),
            new Display($state['messages']),
        );
    }

    public function state(): array
    {
        $drinks = [];
        foreach ($this->settings->drinks() as $id => $drink) {
            $drinks[$id] = $drink;
            $drinks[$id]['priceLabel'] = $this->currency->format($drink['price']);
        }

        return [
            'balance' => $this->currency->format($this->wallet->balance()),
            'drinks' => $drinks,
            'coins' => $this->settings->coins(),
            'messages' => $this->display->messages(),
        ];
    }
}
