<?php

declare(strict_types=1);

namespace App;

final class Display
{
    private array $messages;

    public function __construct(array $messages = [])
    {
        $this->messages = array_values(array_slice($messages, -3));
    }

    public function show(string $message): void
    {
        $this->messages[] = $message;
        $this->messages = array_slice($this->messages, -3);
    }

    public function messages(): array
    {
        return $this->messages;
    }
}
