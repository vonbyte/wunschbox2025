<?php

use App\Models\User;
use App\Models\Wish;
use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

test('edit form preselects the receiver of the wish', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $wish = Wish::factory()->create(['receiver' => 'Tanja']);

    $response = $this->actingAs($admin)->get(route('admin.wishes.edit', $wish));

    $response->assertOk();
    $response->assertSee('<option value="Tanja" selected>', false);
    $response->assertDontSee('<option value="Sonia" selected>', false);
});

test('edit form keeps a receiver outside the default list selected', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $wish = Wish::factory()->create(['receiver' => 'Tanja B.']);

    $this->actingAs($admin)
        ->get(route('admin.wishes.edit', $wish))
        ->assertOk()
        ->assertSee('<option value="Tanja B." selected>', false)
        ->assertDontSee('<option value="Sonia" selected>', false);
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
    $wish = Wish::factory()->create(['is_public' => true, 'sortnr' => '000']);

    $this->actingAs($admin)
        ->patch(route('admin.wishes.update-sort', $wish), ['sortnr' => '005'])
        ->assertSessionHasNoErrors();

    expect($wish->fresh()->sortnr)->toBe('005');
});

test('sort number is zero-padded so it orders numerically', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $ten = Wish::factory()->create(['is_public' => true, 'title' => 'Zehn']);
    $five = Wish::factory()->create(['is_public' => true, 'title' => 'Fünf']);

    $this->actingAs($admin)->patch(route('admin.wishes.update-sort', $ten), ['sortnr' => '10']);
    $this->actingAs($admin)->patch(route('admin.wishes.update-sort', $five), ['sortnr' => '5']);

    expect($ten->fresh()->sortnr)->toBe('010')
        ->and($five->fresh()->sortnr)->toBe('005');

    $this->get(route('wishlist'))->assertSeeInOrder(['Fünf', 'Zehn']);
});

test('sort number must be a number with at most three digits', function (string $sortnr) {
    $admin = User::factory()->create(['is_admin' => true]);
    $wish = Wish::factory()->create(['is_public' => true, 'sortnr' => '000']);

    $this->actingAs($admin)
        ->patch(route('admin.wishes.update-sort', $wish), ['sortnr' => $sortnr])
        ->assertSessionHasErrors('sortnr');

    expect($wish->fresh()->sortnr)->toBe('000');
})->with(['abc', '-1', '1000', '1.5']);

// Different statuses and creation dates make sure the order comes from sortnr,
// not from the (is_public, status, sortnr) index or the creation date.
function createPublicWishesInMixedOrder(): void
{
    Wish::factory()->create([
        'is_public' => true, 'sortnr' => '001', 'status' => 'packed',
        'title' => 'Erster Wunsch', 'created_at' => now()->subDays(3),
    ]);
    Wish::factory()->create([
        'is_public' => true, 'sortnr' => '020', 'status' => 'idea',
        'title' => 'Dritter Wunsch', 'created_at' => now()->subDays(2),
    ]);
    Wish::factory()->create([
        'is_public' => true, 'sortnr' => '010', 'status' => 'open',
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
    Wish::factory()->create(['status' => 'idea', 'is_public' => true, 'title' => 'Meine Idee']);

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
