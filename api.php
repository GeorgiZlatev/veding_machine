<?php

declare(strict_types=1);

session_start();

require 'src/Currency.php';
require 'src/Display.php';
require 'src/Settings.php';
require 'src/Wallet.php';
require 'src/VendingMachine.php';
require 'src/ServiceAccess.php';

use App\ServiceAccess;
use App\VendingMachine;

header('Content-Type: application/json; charset=utf-8');

try {
    $payload = json_decode(
        (string) file_get_contents('php://input'),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    $passwordHash = getenv('VENDING_SERVICE_PASSWORD_HASH')
        ?: '$2y$12$lfMXULEwyqLcjoYLfZVQY.hFALRGAujqdIme6eHM.zpJK9RbDhCcm'; // service123
    $serviceAccess = new ServiceAccess($passwordHash);

    $state = $_SESSION['vending_state'] ?? null;
    $machine = is_array($state)
        ? VendingMachine::fromState($state)
        : VendingMachine::withDefaults();

    $action = $payload['action'] ?? 'state';
    $data = $payload['data'] ?? [];

    $resetSession = false;

    match ($action) {
        'state' => null,
        'service.status' => null,
        'service.login' => login($serviceAccess, $data['password'] ?? null),
        'service.logout' => $serviceAccess->logout(),
        'service.drink.add' => addDrink($serviceAccess, $machine, $data),
        'service.coin.add' => addCoin($serviceAccess, $machine, $data),
        'reset' => $resetSession = true,
        'coin.insert' => $machine->putCoin($data['value'] ?? null),
        'coin.change' => $machine->getCoins(),
        'drink.buy' => $machine->buyDrink((string) ($data['id'] ?? '')),
        default => throw new \InvalidArgumentException('Непознато действие.'),
    };

    if ($resetSession) {
        session_unset();
        session_destroy();
        $machine->reset();
    } else {
        $_SESSION['vending_state'] = $machine->export();
    }

    echo json_encode([
        'ok' => true,
        'state' => $machine->state(),
        'service' => [
            'authorized' => !$resetSession && $serviceAccess->isAuthorized(),
        ],
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'message' => $error->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

function login(ServiceAccess $serviceAccess, mixed $password): void
{
    if (!is_string($password) || !$serviceAccess->login($password)) {
        throw new \InvalidArgumentException('Невалидна парола.');
    }
}

function addDrink(ServiceAccess $serviceAccess, VendingMachine $machine, array $data): void
{
    $serviceAccess->requireAuthorization();
    $machine->addDrink($data['name'] ?? null, $data['price'] ?? null, $data['quantity'] ?? null);
}

function addCoin(ServiceAccess $serviceAccess, VendingMachine $machine, array $data): void
{
    $serviceAccess->requireAuthorization();
    $machine->addAcceptedCoin($data['value'] ?? null);
}
