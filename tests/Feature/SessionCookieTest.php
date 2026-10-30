<?php

function sessionConfigWith(array $env): array
{
    foreach ($env as $key => $value) {
        $_SERVER[$key] = $_ENV[$key] = $value;
    }

    try {
        return require config_path('session.php');
    } finally {
        foreach (array_keys($env) as $key) {
            unset($_SERVER[$key], $_ENV[$key]);
        }
    }
}

test('the session cookie is https only when the app runs on https', function (string $url, bool $secure) {
    expect(sessionConfigWith(['APP_URL' => $url])['secure'])->toBe($secure);
})->with([
    'https' => ['https://wunschbox.example', true],
    'http' => ['http://localhost:8000', false],
]);

test('SESSION_SECURE_COOKIE still decides when it is set', function () {
    expect(sessionConfigWith(['APP_URL' => 'https://wunschbox.example', 'SESSION_SECURE_COOKIE' => 'false'])['secure'])->toBeFalse()
        ->and(sessionConfigWith(['APP_URL' => 'http://localhost:8000', 'SESSION_SECURE_COOKIE' => 'true'])['secure'])->toBeTrue();
});
