<?php

use App\Models\User;
use App\Models\Wish;

test('a description may have up to 5000 characters', function () {
    $this->post(route('wishbox.store'), [
        'title' => 'Lange Geschichte',
        'receiver' => 'Sonia',
        'description' => str_repeat('a', 5000),
    ])->assertSessionHasNoErrors();

    $this->from(route('wishbox'))
        ->followingRedirects()
        ->post(route('wishbox.store'), [
            'title' => 'Zu lange Geschichte',
            'receiver' => 'Sonia',
            'description' => str_repeat('a', 5001),
        ])
        ->assertSee('Die Beschreibung darf höchstens 5000 Zeichen lang sein.');

    expect(Wish::where('title', 'Lange Geschichte')->exists())->toBeTrue()
        ->and(Wish::where('title', 'Zu lange Geschichte')->exists())->toBeFalse();
});

test('admins can not save longer descriptions either', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.wishes.store'), ['title' => 'Neu', 'description' => str_repeat('a', 5001)])
        ->assertSessionHasErrorsIn('addWish', 'description');
});
