<?php

declare(strict_types=1);

namespace App;

final class ServiceAccess
{
    private const SESSION_KEY = 'service_authorized';

    public function __construct(private string $passwordHash)
    {
        if ($this->passwordHash === '') {
            throw new \InvalidArgumentException('Липсва хеш на паролата за сервизни настройки.');
        }
    }

    public function login(string $password): bool
    {
        if (!password_verify($password, $this->passwordHash)) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY] = true;

        return true;
    }

    public function isAuthorized(): bool
    {
        return ($_SESSION[self::SESSION_KEY] ?? false) === true;
    }

    public function requireAuthorization(): void
    {
        if (!$this->isAuthorized()) {
            throw new \RuntimeException('Нужен е вход в сервизните настройки.');
        }
    }

    public function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }
}
