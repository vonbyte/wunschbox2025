<?php

use App\Models\User;
use App\Models\Wish;
use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('edit form preselects the receiver of the wish', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $wish = Wish::factory()->create(['receiver' => 'Tanja']);

    $response = $this->actingAs($admin)->get(route('admin.wishes.edit', $wish));

    $response->assertOk();
    $response->assertSee('<option value="Tanja" selected>', false);
    $response->assertDontSee('<option value="Sonia" selected>', false);
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
