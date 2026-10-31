<?php

function appConfigWith(array $env): array
{
    foreach ($env as $key => $value) {
        if ($value === null) {
            unset($_SERVER[$key], $_ENV[$key]);
            putenv($key);
        } else {
            $_SERVER[$key] = $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }

    try {
        return require config_path('app.php');
    } finally {
        // phpunit.xml sets APP_LOCALE=de for every test
        $_SERVER['APP_LOCALE'] = $_ENV['APP_LOCALE'] = 'de';
        putenv('APP_LOCALE=de');
    }
}

test('the app is German without APP_LOCALE', function () {
    expect(appConfigWith(['APP_LOCALE' => null])['locale'])->toBe('de');
});

test('APP_LOCALE switches the locale', function () {
    expect(appConfigWith(['APP_LOCALE' => 'en'])['locale'])->toBe('en');
});

test('the tests run in German', function () {
    expect(app()->getLocale())->toBe('de');
});
