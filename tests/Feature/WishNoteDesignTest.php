<?php

use App\Models\User;
use App\Models\Wish;
use Illuminate\Support\Facades\Storage;

test('wishbox offers every receiver as a radio button', function () {
    $response = $this->get(route('wishbox'))
        ->assertOk()
        ->assertSee('Santa hat die Rentiere schon vorgewarnt.');

    foreach (Wish::RECEIVERS as $receiver) {
        $response->assertSee('type="radio" name="receiver" value="'.$receiver.'"', false);
    }

    $response->assertSee('name="receiver" value="'.Wish::RECEIVERS[0].'" class="sr-only" required checked', false);
});

test('wish note lists public wishes numbered by sort number and signed by the owner', function () {
    config(['wishbox.owner' => 'Tanja']);
    Wish::factory()->onWishNote()->create(['sortnr' => 20, 'title' => 'Konzertkarten']);
    Wish::factory()->onWishNote()->create(['sortnr' => 10, 'title' => 'Kopfhörer']);
    Wish::factory()->create(['is_public' => false, 'title' => 'Geheimer Gastwunsch']);

    $this->get(route('wishlist'))
        ->assertOk()
        ->assertSee('<title>Tanjas Wunschzettel</title>', false)
        ->assertSeeInOrder(['Lieber Santa', '1.', 'Kopfhörer', '2.', 'Konzertkarten', 'Deine Tanja'])
        ->assertDontSee('Geheimer Gastwunsch');
});

test('empty wish note invites to come back later', function () {
    Wish::factory()->create(['is_public' => false]);

    $this->get(route('wishlist'))
        ->assertOk()
        ->assertSee('Der Wunschzettel ist noch leer.')
        ->assertDontSee('Lieber Santa');
});

test('wish note shows the thumbnail and the number of links', function () {
    Wish::factory()->onWishNote()->create([
        'title' => 'Bildband',
        'image' => 'wishes/bildband.jpg',
        'image_thumbnail' => 'wishes/bildband_thumb.jpg',
        'example_links' => ['https://example.com/a', 'https://example.com/b'],
    ]);

    $this->get(route('wishlist'))
        ->assertOk()
        ->assertSee(Storage::url('wishes/bildband_thumb.jpg'), false)
        ->assertSee('2 Links');
});

test('wish detail page works with and without an image', function () {
    $withImage = Wish::factory()->onWishNote()->create([
        'image' => 'wishes/decke.jpg',
        'image_thumbnail' => 'wishes/decke_thumb.jpg',
        'example_links' => ['https://www.example.com/decke'],
    ]);
    $withoutImage = Wish::factory()->onWishNote()->create(['image' => null]);

    $this->get(route('wish.show', $withImage))
        ->assertOk()
        ->assertSee(Storage::url('wishes/decke.jpg'), false)
        ->assertSee('example.com')
        ->assertDontSee('Nur Admin');

    $this->get(route('wish.show', $withoutImage))
        ->assertOk()
        ->assertSee($withoutImage->title);
});

test('admin dashboard counts every wish that is not on the wish note', function () {
    config(['wishbox.owner' => 'Tanja']);
    $admin = User::factory()->admin()->create();
    foreach (['idea', 'open', 'ordered', 'delivered', 'packed', 'packed', 'unpacked'] as $status) {
        Wish::factory()->create(['status' => $status, 'is_public' => false]);
    }
    Wish::factory()->create(['status' => 'open', 'is_public' => true, 'receiver' => 'Sonia']);
    Wish::factory()->onWishNote()->create(['status' => 'open']);

    $response = $this->actingAs($admin)->get(route('admin.index'))->assertOk();

    expect($response->viewData('counts'))->toBe([
        'all' => 8,
        'open' => 4,
        'delivered' => 1,
        'packed' => 2,
        'unpacked' => 1,
    ]);
});

test('admin dashboard ignores unknown filters', function () {
    $admin = User::factory()->admin()->create();
    Wish::factory()->create(['status' => 'packed', 'is_public' => false, 'title' => 'Gast Verpackt']);

    $response = $this->actingAs($admin)
        ->get(route('admin.index', ['filter' => 'unbekannt']))
        ->assertOk()
        ->assertSee('Gast Verpackt');

    expect($response->viewData('filter'))->toBeNull();
});

test('wish detail shows the receiver only to admins', function () {
    config(['wishbox.owner' => 'Tanja']);
    $wish = Wish::factory()->onWishNote()->create();

    $this->get(route('wish.show', $wish))
        ->assertOk()
        ->assertDontSee('von Tanja');

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('wish.show', $wish))
        ->assertOk()
        ->assertSee('von Tanja');
});

test('the add form on the dashboard has no receiver choice', function () {
    $wish = Wish::factory()->create(['is_public' => false]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.index'))
        ->assertOk()
        ->assertDontSee('name="receiver"', false);
});

test('wishbox counts down the days to christmas', function (string $today, string $text) {
    $this->travelTo(Carbon\Carbon::parse($today));

    $this->get(route('wishbox'))
        ->assertOk()
        ->assertSee($text);
})->with([
    'weeks before' => ['2026-10-27 18:00', 'Nur noch 58 Tage bis Weihnachten'],
    'one day before' => ['2026-12-23 08:00', 'Nur noch 1 Tag bis Weihnachten'],
    'christmas eve' => ['2026-12-24 20:00', 'Heute ist Heiligabend'],
    'after christmas' => ['2026-12-26 10:00', 'Nur noch 363 Tage bis Weihnachten'],
]);

test('admin header shows the same countdown', function () {
    $this->travelTo(Carbon\Carbon::parse('2026-12-20 12:00'));

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.index'))
        ->assertSee('noch 4 Tage bis Weihnachten');
});

test('countdown follows the local time, not UTC', function () {
    config(['wishbox.timezone' => 'Europe/Zurich']);
    // 23:30 UTC is already half past midnight on Christmas Eve in Zurich
    $this->travelTo(Carbon\Carbon::parse('2026-12-23 23:30', 'UTC'));

    $this->get(route('wishbox'))
        ->assertOk()
        ->assertSee('Heute ist Heiligabend');
});

test('errors of the add form open the form instead of the banner', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->from(route('admin.index'))
        ->post(route('admin.wishes.store'), ['title' => '   '])
        ->assertSessionHasErrorsIn('addWish', 'title');

    $this->actingAs($admin)
        ->from(route('admin.index'))
        ->followingRedirects()
        ->post(route('admin.wishes.store'), ['title' => '   '])
        ->assertOk()
        ->assertSee('<p class="field-error">', false)
        ->assertSee('open: true, adding: true', false)
        ->assertDontSee('Nicht gespeichert');
});

test('edit form errors stay in the default bag', function () {
    $admin = User::factory()->admin()->create();
    $wish = Wish::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.wishes.update', $wish), ['title' => '', 'receiver' => $wish->receiver])
        ->assertSessionHasErrors('title');
});
