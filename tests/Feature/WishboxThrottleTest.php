<?php

use App\Models\Wish;

function sendWish(string $ip = '203.0.113.1')
{
    return test()->withServerVariables(['REMOTE_ADDR' => $ip])
        ->post(route('wishbox.store'), ['title' => 'Schlitten', 'receiver' => 'Sonia']);
}

test('a guest can send five wishes a minute, the sixth one gets a German 429 page', function () {
    foreach (range(1, 5) as $i) {
        sendWish()->assertRedirect(route('wishbox'));
    }

    sendWish()
        ->assertTooManyRequests()
        ->assertSee('Kurz durchatmen')
        ->assertSee('Bitte warte ein paar Minuten');

    expect(Wish::count())->toBe(5);
});

test('the limit counts per guest', function () {
    foreach (range(1, 5) as $i) {
        sendWish('203.0.113.1');
    }

    sendWish('203.0.113.2')->assertRedirect(route('wishbox'));
});

test('a guest can send thirty wishes an hour', function () {
    foreach (range(1, 6) as $round) {
        foreach (range(1, 5) as $i) {
            sendWish()->assertRedirect(route('wishbox'));
        }
        $this->travel(61)->seconds();
    }

    sendWish()->assertTooManyRequests();

    expect(Wish::count())->toBe(30);
});
