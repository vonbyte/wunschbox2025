<?php

use App\Models\User;
use App\Models\Wish;
use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

function checkedReceiver(string $receiver): string
{
    return 'name="receiver" value="'.$receiver.'" class="sr-only" required checked';
}

test('edit form preselects the receiver of the wish', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $wish = Wish::factory()->create(['receiver' => 'Tanja']);

    $response = $this->actingAs($admin)->get(route('admin.wishes.edit', $wish));

    $response->assertOk();
    $response->assertSee(checkedReceiver('Tanja'), false);
    $response->assertDontSee(checkedReceiver('Sonia'), false);
});

test('edit form keeps a receiver outside the default list selected', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $wish = Wish::factory()->create(['receiver' => 'Tanja B.']);

    $this->actingAs($admin)
        ->get(route('admin.wishes.edit', $wish))
        ->assertOk()
        ->assertSee(checkedReceiver('Tanja B.'), false)
        ->assertDontSee(checkedReceiver('Sonia'), false);
});

test('updating a wish keeps its receiver', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $wish = Wish::factory()->create(['receiver' => 'Tanja', 'title' => 'Alt']);

    $this->actingAs($admin)
        ->patch(route('admin.wishes.update', $wish), [
            'title' => 'Neu',
            'receiver' => 'Tanja',
        ])
        ->assertRedirect(route('admin.index'));

    expect($wish->fresh())
        ->title->toBe('Neu')
        ->receiver->toBe('Tanja');
});

test('sort number of a public wish can be updated', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $wish = Wish::factory()->create(['is_public' => true]);

    $this->actingAs($admin)
        ->patch(route('admin.wishes.update-sort', $wish), ['sortnr' => '5'])
        ->assertSessionHasNoErrors();

    expect($wish->fresh()->sortnr)->toBe(5);
});

test('sort numbers order numerically', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $ten = Wish::factory()->onWishNote()->create(['title' => 'Zehn']);
    $five = Wish::factory()->onWishNote()->create(['title' => 'Fünf']);

    $this->actingAs($admin)->patch(route('admin.wishes.update-sort', $ten), ['sortnr' => '10']);
    $this->actingAs($admin)->patch(route('admin.wishes.update-sort', $five), ['sortnr' => '5']);

    $this->get(route('wishlist'))->assertSeeInOrder(['Fünf', 'Zehn']);
});

test('sort number must be a whole number from 0', function (string $sortnr) {
    $admin = User::factory()->create(['is_admin' => true]);
    $wish = Wish::factory()->onWishNote()->create(['sortnr' => 3]);

    $this->actingAs($admin)
        ->patch(route('admin.wishes.update-sort', $wish), ['sortnr' => $sortnr])
        ->assertSessionHasErrors('sortnr');

    expect($wish->fresh()->sortnr)->toBe(3);
})->with(['abc', '-1', '1.5', '']);

test('invalid quick edits show an error on the dashboard', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $wish = Wish::factory()->create();

    $this->actingAs($admin)
        ->from(route('admin.index'))
        ->followingRedirects()
        ->patch(route('admin.wishes.update-sort', $wish), ['sortnr' => 'abc'])
        ->assertSee('Nicht gespeichert: Die Sortierung muss eine ganze Zahl ab 0 sein.');

    $this->actingAs($admin)
        ->from(route('admin.index'))
        ->followingRedirects()
        ->patch(route('admin.wishes.update-nr', $wish), ['package_nr' => '-2'])
        ->assertSee('Nicht gespeichert: Die Päckchen-Nummer muss eine ganze Zahl ab 0 sein.');
});

test('migration turns text sort numbers into integers', function () {
    $migration = require database_path('migrations/2026_10_24_130000_change_sortnr_to_integer_on_wishes.php');
    $migration->down();

    $ids = collect(['5', '10', '007', 'abc'])->map(fn ($sortnr) => DB::table('wishes')->insertGetId([
        'title' => "Wunsch {$sortnr}", 'receiver' => 'Sonia', 'sortnr' => $sortnr,
    ]));

    $migration->up();

    expect(DB::table('wishes')->whereIn('id', $ids)->orderBy('id')->pluck('sortnr')->all())
        ->toBe([5, 10, 7, 0]);
});

// Different statuses and creation dates make sure the order comes from sortnr,
// not from the (is_public, status, sortnr) index or the creation date.
function createPublicWishesInMixedOrder(): void
{
    Wish::factory()->onWishNote()->create([
        'sortnr' => 1, 'status' => 'packed',
        'title' => 'Erster Wunsch', 'created_at' => now()->subDays(3),
    ]);
    Wish::factory()->onWishNote()->create([
        'sortnr' => 20, 'status' => 'idea',
        'title' => 'Dritter Wunsch', 'created_at' => now()->subDays(2),
    ]);
    Wish::factory()->onWishNote()->create([
        'sortnr' => 10, 'status' => 'open',
        'title' => 'Zweiter Wunsch', 'created_at' => now()->subDay(),
    ]);
}

test('public wishlist is ordered by sort number', function () {
    createPublicWishesInMixedOrder();

    $this->get(route('wishlist'))
        ->assertOk()
        ->assertSeeInOrder(['Erster Wunsch', 'Zweiter Wunsch', 'Dritter Wunsch']);
});

test('admin list of my wishes is ordered by sort number', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    createPublicWishesInMixedOrder();

    $this->actingAs($admin)
        ->get(route('admin.index'))
        ->assertOk()
        ->assertSeeInOrder(['Erster Wunsch', 'Zweiter Wunsch', 'Dritter Wunsch']);
});

test('uploaded wish image stores its thumbnail path', function () {
    Storage::fake('public');

    $this->post(route('wishbox.store'), [
        'title' => 'Wunsch mit Bild',
        'receiver' => 'Sonia',
        'image' => UploadedFile::fake()->image('geschenk.jpg', 1200, 900),
    ])->assertSessionHasNoErrors();

    $wish = Wish::firstWhere('title', 'Wunsch mit Bild');

    expect($wish->image_thumbnail)->toBe(app(ImageService::class)->getThumbnailPath($wish->image));
    Storage::disk('public')->assertExists($wish->image_thumbnail);
});

test('backfill migration sets missing thumbnail paths', function () {
    Storage::fake('public');
    Storage::disk('public')->put('wishes/mit.jpg', 'x');
    Storage::disk('public')->put('wishes/mit_thumb.jpg', 'x');
    Storage::disk('public')->put('wishes/ohne.jpg', 'x');

    $withThumbnail = Wish::factory()->create(['image' => 'wishes/mit.jpg']);
    $withoutThumbnail = Wish::factory()->create(['image' => 'wishes/ohne.jpg']);

    (require database_path('migrations/2026_10_24_120000_backfill_image_thumbnail_on_wishes.php'))->up();

    expect($withThumbnail->fresh()->image_thumbnail)->toBe('wishes/mit_thumb.jpg')
        ->and($withoutThumbnail->fresh()->image_thumbnail)->toBeNull();
});

test('backfill migration covers more wishes than one chunk', function () {
    Storage::fake('public');
    $count = 1001;

    $rows = [];
    for ($i = 1; $i <= $count; $i++) {
        Storage::disk('public')->put("wishes/{$i}_thumb.jpg", 'x');
        $rows[] = ['title' => "Wunsch {$i}", 'receiver' => 'Sonia', 'image' => "wishes/{$i}.jpg", 'created_at' => now(), 'updated_at' => now()];
    }
    DB::table('wishes')->insert($rows);

    (require database_path('migrations/2026_10_24_120000_backfill_image_thumbnail_on_wishes.php'))->up();

    expect(Wish::whereNull('image_thumbnail')->count())->toBe(0);
});

test('open filter only shows open guest wishes', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    Wish::factory()->create(['status' => 'idea', 'title' => 'Gast Idee']);
    Wish::factory()->create(['status' => 'ordered', 'title' => 'Gast Bestellt']);
    Wish::factory()->create(['status' => 'packed', 'title' => 'Gast Verpackt']);
    Wish::factory()->onWishNote()->create(['status' => 'idea', 'title' => 'Meine Idee']);

    $guestWishes = $this->actingAs($admin)
        ->get(route('admin.index', ['filter' => 'open']))
        ->assertOk()
        ->viewData('guestWishes');

    expect($guestWishes->pluck('title')->sort()->values()->all())
        ->toBe(['Gast Bestellt', 'Gast Idee']);
});

test('svg uploads are rejected without storing a file', function () {
    Storage::fake('public');

    $svg = UploadedFile::fake()->createWithContent(
        'geschenk.svg',
        '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"><rect width="10" height="10"/></svg>'
    );

    $this->post(route('wishbox.store'), [
        'title' => 'Wunsch mit SVG',
        'receiver' => 'Sonia',
        'image' => $svg,
    ])->assertSessionHasErrors('image');

    expect(Wish::where('title', 'Wunsch mit SVG')->exists())->toBeFalse();
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('guests can only pick receivers from the list', function () {
    $this->post(route('wishbox.store'), [
        'title' => 'Fremder Wunsch',
        'receiver' => 'Max Muster',
    ])->assertSessionHasErrors('receiver');

    expect(Wish::where('title', 'Fremder Wunsch')->exists())->toBeFalse();
});

test('admins can keep a receiver outside the list but not set a new one', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $wish = Wish::factory()->create(['receiver' => 'Tanja B.']);

    $this->actingAs($admin)
        ->get(route('admin.wishes.edit', $wish))
        ->assertSee(checkedReceiver('Tanja B.'), false);

    $this->actingAs($admin)
        ->patch(route('admin.wishes.update', $wish), ['title' => 'Neu', 'receiver' => 'Tanja B.'])
        ->assertSessionHasNoErrors();

    $this->actingAs($admin)
        ->patch(route('admin.wishes.update', $wish), ['title' => 'Neu', 'receiver' => 'Max Muster'])
        ->assertSessionHasErrors('receiver');

    expect($wish->fresh()->receiver)->toBe('Tanja B.');
});
