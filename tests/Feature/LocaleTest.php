<?php

test('the app stays German even if the environment says en', function () {
    $_SERVER['APP_LOCALE'] = $_ENV['APP_LOCALE'] = 'en';

    try {
        $config = require config_path('app.php');
    } finally {
        unset($_SERVER['APP_LOCALE'], $_ENV['APP_LOCALE']);
    }

    expect($config['locale'])->toBe('de')
        ->and(app()->getLocale())->toBe('de');
});
