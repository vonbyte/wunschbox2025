<?php

use App\Models\User;
use App\Models\Wish;

test('admins can set every status', function (string $status) {
    $wish = Wish::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.index'))
        ->patch(route('admin.wishes.update-status', $wish), ['status' => $status])
        ->assertRedirect(route('admin.index'))
        ->assertSessionHasNoErrors();

    expect($wish->fresh()->status)->toBe($status);
})->with([Wish::STATUS_IDEA, Wish::STATUS_OPEN, Wish::STATUS_ORDERED, Wish::STATUS_DELIVERED, Wish::STATUS_PACKED, Wish::STATUS_UNPACKED]);

test('the status list offers every status', function () {
    expect(array_keys(Wish::getStatuses()))->toBe([Wish::STATUS_IDEA, Wish::STATUS_OPEN, Wish::STATUS_ORDERED, Wish::STATUS_DELIVERED, Wish::STATUS_PACKED, Wish::STATUS_UNPACKED]);
});

test('an unknown status is not saved', function (string $status) {
    $wish = Wish::factory()->create(['status' => Wish::STATUS_ORDERED]);

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('admin.wishes.update-status', $wish), ['status' => $status])
        ->assertSessionHasErrors('status');

    expect($wish->fresh()->status)->toBe(Wish::STATUS_ORDERED);
})->with(['lost', 'Packed', '']);

test('admins can set the package number', function () {
    $wish = Wish::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.index'))
        ->followingRedirects()
        ->patch(route('admin.wishes.update-nr', $wish), ['package_nr' => '12'])
        ->assertSee('Päckchen-Nummer aktualisiert!');

    expect((int) $wish->fresh()->package_nr)->toBe(12);
});
