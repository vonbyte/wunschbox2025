<?php

use App\Models\Wish;
use Illuminate\Http\UploadedFile;

function sendWish(string $ip = '203.0.113.1', array $input = [])
{
    return test()->withServerVariables(['REMOTE_ADDR' => $ip])
        ->post(route('wishbox.store'), ['title' => 'Schlitten', 'receiver' => 'Sonia', ...$input]);
}

test('guests on one network can send ten wishes a minute, the eleventh one comes back to the form', function () {
    foreach (range(1, 10) as $i) {
        sendWish()->assertRedirect(route('wishbox'));
    }

    sendWish(input: ['title' => 'Elfter Wunsch', 'receiver' => 'Tanja', 'description' => 'Rot, bitte'])
        ->assertRedirect(route('wishbox'))
        ->assertSessionHasInput(['title' => 'Elfter Wunsch', 'receiver' => 'Tanja', 'description' => 'Rot, bitte'])
        ->assertSessionHasErrors(['throttle' => 'Es sind gerade sehr viele Wünsche auf einmal angekommen. Bitte warte eine Minute und schick deinen Wunsch dann noch einmal ab.']);

    expect(Wish::count())->toBe(10);
});

test('the form shows the typed wish and the message again', function () {
    foreach (range(1, 10) as $i) {
        sendWish();
    }

    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.1'])
        ->followingRedirects()
        ->post(route('wishbox.store'), ['title' => 'Elfter Wunsch', 'receiver' => 'Sonia', 'description' => 'Rot, bitte'])
        ->assertOk()
        ->assertSee('Bitte warte eine Minute')
        ->assertSee('value="Elfter Wunsch"', false)
        ->assertSee('Rot, bitte</textarea>', false);
});

test('guests who sent an image are told to attach it again', function () {
    foreach (range(1, 10) as $i) {
        sendWish();
    }

    sendWish(input: ['image' => UploadedFile::fake()->image('schlitten.jpg')])
        ->assertSessionHasErrors(['throttle' => 'Es sind gerade sehr viele Wünsche auf einmal angekommen. Bitte warte eine Minute und schick deinen Wunsch dann noch einmal ab. Dein Text ist noch da, nur das Bild musst du noch einmal anheften.']);
});

test('the limit counts per network', function () {
    foreach (range(1, 10) as $i) {
        sendWish('203.0.113.1');
    }

    sendWish('203.0.113.2')->assertRedirect(route('wishbox'))->assertSessionHasNoErrors();
});

test('guests on one network can send sixty wishes an hour and learn how long to wait after that', function () {
    foreach (range(1, 6) as $round) {
        foreach (range(1, 10) as $i) {
            sendWish()->assertRedirect(route('wishbox'))->assertSessionHasNoErrors();
        }
        $this->travel(61)->seconds();
    }

    // The first wish of the hour was sent 6 × 61 seconds ago, so the hour frees up in 54 minutes
    sendWish()->assertSessionHasErrors(['throttle' => 'Es sind gerade sehr viele Wünsche auf einmal angekommen. Bitte warte 54 Minuten und schick deinen Wunsch dann noch einmal ab.']);

    expect(Wish::count())->toBe(60);
});
