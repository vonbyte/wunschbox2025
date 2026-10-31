<?php

use App\Models\Wish;

test('guests can not set what only admins decide', function () {
    $this->post(route('wishbox.store'), [
        'title' => 'Schlitten',
        'receiver' => 'Tanja',
        'is_public' => '1',
        'status' => 'packed',
        'sortnr' => '9',
        'package_nr' => '3',
        'image_thumbnail' => 'wishes/fremd_thumb.jpg',
    ])->assertSessionHasNoErrors();

    expect(Wish::sole())
        ->is_public->toBeFalse()
        ->status->toBe(Wish::STATUS_IDEA)
        ->sortnr->toBe(0)
        ->package_nr->toEqual(0)
        ->image_thumbnail->toBeNull();
});

test('guests can not open a private wish of the owner', function () {
    config(['wishbox.owner' => 'Tanja']);
    $wish = Wish::factory()->create(['receiver' => 'Tanja', 'is_public' => false]);

    $this->get(route('wish.show', $wish))->assertNotFound();
    $this->get(route('wishlist'))->assertOk()->assertDontSee($wish->title);
});
