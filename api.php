<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/vendor/autoload.php';

use App\Api;

header('Content-Type: application/json; charset=utf-8');

try {
    $payload = json_decode(
        (string) file_get_contents('php://input'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );

    if (!is_array($payload)) {
        throw new InvalidArgumentException('Невалидна заявка.');
    }

    echo json_encode(
        (new Api())->handle($payload),
        JSON_UNESCAPED_UNICODE,
    );
} catch (Throwable $error) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'message' => $error->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
