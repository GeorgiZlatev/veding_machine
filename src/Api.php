<?php

declare(strict_types=1);

namespace App;

final class Api
{
    public function handle(array $payload): array
    {
        $passwordHash = getenv('VENDING_SERVICE_PASSWORD_HASH')
            ?: '$2y$12$lfMXULEwyqLcjoYLfZVQY.hFALRGAujqdIme6eHM.zpJK9RbDhCcm';
        $serviceAccess = new ServiceAccess($passwordHash);
        $machine = $this->machineFromSession();
        $action = $payload['action'] ?? 'state';
        $data = $payload['data'] ?? [];

        if (!is_string($action) || !is_array($data)) {
            throw new \InvalidArgumentException('Невалидна заявка.');
        }

        $resetSession = false;

        switch ($action) {
            case 'state':
            case 'service.status':
                break;
            case 'service.login':
                $this->login($serviceAccess, $data['password'] ?? null);
                break;
            case 'service.logout':
                $serviceAccess->logout();
                break;
            case 'service.drink.add':
                $this->addDrink($serviceAccess, $machine, $data);
                break;
            case 'service.coin.add':
                $this->addCoin($serviceAccess, $machine, $data);
                break;
            case 'reset':
                $resetSession = true;
                break;
            case 'coin.insert':
                $machine->putCoin($data['value'] ?? null);
                break;
            case 'coin.change':
                $machine->getCoins();
                break;
            case 'drink.buy':
                $machine->buyDrink((string) ($data['id'] ?? ''));
                break;
            default:
                throw new \InvalidArgumentException('Непознато действие.');
        }

        if ($resetSession) {
            session_unset();
            session_destroy();
            $machine->reset();
        } else {
            $_SESSION['vending_state'] = $machine->export();
        }

        return [
            'ok' => true,
            'state' => $machine->state(),
            'service' => [
                'authorized' => !$resetSession && $serviceAccess->isAuthorized(),
            ],
        ];
    }

    private function machineFromSession(): VendingMachine
    {
        $state = $_SESSION['vending_state'] ?? null;

        return is_array($state)
            ? VendingMachine::fromState($state)
            : VendingMachine::withDefaults();
    }

    private function login(ServiceAccess $serviceAccess, mixed $password): void
    {
        if (!is_string($password) || !$serviceAccess->login($password)) {
            throw new \InvalidArgumentException('Невалидна парола.');
        }
    }

    private function addDrink(ServiceAccess $serviceAccess, VendingMachine $machine, array $data): void
    {
        $serviceAccess->requireAuthorization();
        $machine->addDrink($data['name'] ?? null, $data['price'] ?? null, $data['quantity'] ?? null);
    }

    private function addCoin(ServiceAccess $serviceAccess, VendingMachine $machine, array $data): void
    {
        $serviceAccess->requireAuthorization();
        $machine->addAcceptedCoin($data['value'] ?? null);
    }
}
