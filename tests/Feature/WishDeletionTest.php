<?php

use App\Models\User;
use App\Models\Wish;

test('deleting a wish on its detail page leads to the workshop', function () {
    $wish = Wish::factory()->onWishNote()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('wish.show', $wish))
        ->delete(route('admin.wishes.destroy', $wish))
        ->assertRedirect(route('admin.index'));

    $this->get(route('admin.index'))
        ->assertOk()
        ->assertSee('Wunsch dauerhaft gelöscht!');

    expect(Wish::find($wish->id))->toBeNull();
});

test('deleting a wish on the dashboard stays on the dashboard with its filter', function () {
    $wish = Wish::factory()->create(['receiver' => 'Sonia']);

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.index', ['filter' => 'open']))
        ->delete(route('admin.wishes.destroy', $wish))
        ->assertRedirect(route('admin.index', ['filter' => 'open']));
});
