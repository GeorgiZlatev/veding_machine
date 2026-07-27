<?php

declare(strict_types=1);

session_start();

require 'src/Currency.php';
require 'src/Display.php';
require 'src/Settings.php';
require 'src/Wallet.php';
require 'src/VendingMachine.php';

use App\VendingMachine;

header('Content-Type: application/json; charset=utf-8');

try {
    $payload = json_decode(
        (string) file_get_contents('php://input'),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    $state = $_SESSION['vending_state'] ?? null;
    $machine = is_array($state)
        ? VendingMachine::fromState($state)
        : VendingMachine::withDefaults();

    $action = $payload['action'] ?? 'state';
    $data = $payload['data'] ?? [];

    match ($action) {
        'state' => null,
        'reset' => $machine->reset(),
        'coin.insert' => $machine->putCoin($data['value'] ?? null),
        'coin.change' => $machine->getCoins(),
        'drink.buy' => $machine->buyDrink((string) ($data['id'] ?? '')),
        default => throw new \InvalidArgumentException('Непознато действие.'),
    };

    $_SESSION['vending_state'] = $machine->export();

    echo json_encode([
        'ok' => true,
        'state' => $machine->state(),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'message' => $error->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
