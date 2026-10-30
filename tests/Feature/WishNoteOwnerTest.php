<?php

use App\Models\User;
use App\Models\Wish;

beforeEach(function () {
    config(['wishbox.owner' => 'Tanja']);
});

test('admin wishes always go on the owner\'s wish note', function () {
    $admin = User::factory()->admin()->create(['name' => 'Sonia']);

    $this->actingAs($admin)
        ->post(route('admin.wishes.store'), ['title' => 'Für Sonia?', 'receiver' => 'Sonia'])
        ->assertSessionHasNoErrors();

    expect(Wish::firstWhere('title', 'Für Sonia?'))
        ->receiver->toBe('Tanja')
        ->is_public->toBeTrue();
});

test('the public wish note never shows wishes of other receivers', function () {
    Wish::factory()->onWishNote()->create(['title' => 'Tanjas Wunsch']);
    Wish::factory()->create(['is_public' => true, 'receiver' => 'Sonia', 'title' => 'Sonias Wunsch']);

    $this->get(route('wishlist'))
        ->assertOk()
        ->assertSee('Tanjas Wunsch')
        ->assertDontSee('Sonias Wunsch');
});

test('guests cannot open public wishes of other receivers', function () {
    $wish = Wish::factory()->create(['is_public' => true, 'receiver' => 'Sonia']);

    $this->get(route('wish.show', $wish))->assertNotFound();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('wish.show', $wish))
        ->assertOk();
});

test('only wishes of the owner can be put on the wish note', function () {
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
    $wish = Wish::factory()->create(['is_public' => true, 'receiver' => 'Sonia']);

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('admin.wishes.toggle', $wish))
        ->assertSessionHasNoErrors();

    expect($wish->fresh()->is_public)->toBeFalse();
});

test('public admin wishes with an admin name as receiver move to the owner', function () {
    $adminWish = Wish::factory()->create(['is_public' => true, 'receiver' => 'Tanja B.']);
    $privateWish = Wish::factory()->create(['is_public' => false, 'receiver' => 'Max Muster']);
    $soniasWish = Wish::factory()->create(['is_public' => true, 'receiver' => 'Sonia']);

    (require database_path('migrations/2026_10_29_120000_move_admin_wishes_to_wish_note_owner.php'))->up();

    expect($adminWish->fresh()->receiver)->toBe('Tanja')
        ->and($adminWish->fresh()->isOnWishNote())->toBeTrue()
        ->and($privateWish->fresh()->receiver)->toBe('Max Muster')
        ->and($soniasWish->fresh()->receiver)->toBe('Sonia');
});

test('wishes on the wish note keep the owner as receiver when edited', function () {
    $admin = User::factory()->admin()->create();
    $wish = Wish::factory()->onWishNote()->create(['title' => 'Kopfhörer']);

    $this->actingAs($admin)
        ->get(route('admin.wishes.edit', $wish))
        ->assertOk()
        ->assertDontSee('name="receiver"', false);

    $this->actingAs($admin)
        ->patch(route('admin.wishes.update', $wish), ['title' => 'Kopfhörer', 'receiver' => 'Sonia'])
        ->assertSessionHasNoErrors();

    expect($wish->fresh()->isOnWishNote())->toBeTrue();
});

test('guest wishes can still change their receiver', function () {
    $admin = User::factory()->admin()->create();
    $wish = Wish::factory()->create(['is_public' => false, 'receiver' => 'Tanja']);

    $this->actingAs($admin)
        ->get(route('admin.wishes.edit', $wish))
        ->assertSee('name="receiver"', false);

    $this->actingAs($admin)
        ->patch(route('admin.wishes.update', $wish), ['title' => 'Neu', 'receiver' => 'Sonia'])
        ->assertSessionHasNoErrors();

    expect($wish->fresh()->receiver)->toBe('Sonia');
});
