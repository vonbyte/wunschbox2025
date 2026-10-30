<?php

use App\Models\User;
use App\Models\Wish;

test('a request bigger than post_max_size gets a German page', function () {
    $this->withServerVariables(['CONTENT_LENGTH' => 50 * 1024 * 1024])
        ->post(route('wishbox.store'), ['title' => 'Schlitten', 'receiver' => 'Sonia'])
        ->assertStatus(413)
        ->assertSee('Zu schwer für den Schlitten')
        ->assertSee('Bitte nimm ein kleineres Bild mit höchstens 10 MB')
        ->assertSee('href="'.route('wishbox').'"', false);

    expect(Wish::count())->toBe(0);
});

test('the page leads back to where the upload came from', function () {
    $wish = Wish::factory()->onWishNote()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->withHeader('referer', route('admin.wishes.edit', $wish))
        ->withServerVariables(['CONTENT_LENGTH' => 50 * 1024 * 1024])
        ->patch(route('admin.wishes.update', $wish), ['title' => 'Neu'])
        ->assertStatus(413)
        ->assertSee('href="'.route('admin.wishes.edit', $wish).'"', false);
});
