<?php

use App\Models\User;
use App\Models\Wish;

test('admin wishes always go on the owner\'s wish note', function () {
    config(['wishbox.owner' => 'Tanja']);
    $admin = User::factory()->create(['is_admin' => true, 'name' => 'Sonia']);

    $this->actingAs($admin)
        ->post(route('admin.wishes.store'), ['title' => 'Für Sonia?', 'receiver' => 'Sonia'])
        ->assertSessionHasNoErrors();

    expect(Wish::firstWhere('title', 'Für Sonia?'))
        ->receiver->toBe('Tanja')
        ->is_public->toBeTrue();
});

test('the public wish note never shows wishes of other receivers', function () {
    config(['wishbox.owner' => 'Tanja']);
    Wish::factory()->onWishNote()->create(['title' => 'Tanjas Wunsch']);
    Wish::factory()->create(['is_public' => true, 'receiver' => 'Sonia', 'title' => 'Sonias Wunsch']);

    $this->get(route('wishlist'))
        ->assertOk()
        ->assertSee('Tanjas Wunsch')
        ->assertDontSee('Sonias Wunsch');
});

test('guests cannot open public wishes of other receivers', function () {
    config(['wishbox.owner' => 'Tanja']);
    $wish = Wish::factory()->create(['is_public' => true, 'receiver' => 'Sonia']);

    $this->get(route('wish.show', $wish))->assertNotFound();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('wish.show', $wish))
        ->assertOk();
});

test('only wishes of the owner can be put on the wish note', function () {
    config(['wishbox.owner' => 'Tanja']);
    $admin = User::factory()->admin()->create();
    $sonia = Wish::factory()->create(['is_public' => false, 'receiver' => 'Sonia']);
    $tanja = Wish::factory()->create(['is_public' => false, 'receiver' => 'Tanja']);

    $this->actingAs($admin)
        ->patch(route('admin.wishes.toggle', $sonia))
        ->assertSessionHasErrors('is_public');
    $this->actingAs($admin)
        ->patch(route('admin.wishes.toggle', $tanja))
        ->assertSessionHasNoErrors();

    expect($sonia->fresh()->is_public)->toBeFalse()
        ->and($tanja->fresh()->is_public)->toBeTrue();
});

test('public wishes of other receivers can still be made private', function () {
    config(['wishbox.owner' => 'Tanja']);
    $wish = Wish::factory()->create(['is_public' => true, 'receiver' => 'Sonia']);

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('admin.wishes.toggle', $wish))
        ->assertSessionHasNoErrors();

    expect($wish->fresh()->is_public)->toBeFalse();
});
