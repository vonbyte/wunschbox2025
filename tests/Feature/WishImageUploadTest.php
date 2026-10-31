<?php

use App\Models\User;
use App\Models\Wish;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

/**
 * A PNG whose header (all the validation reads) says $width × $height, with $pixelData as image data
 */
function fakePng(int $width, int $height, string $pixelData = 'kaputt'): UploadedFile
{
    $chunk = fn (string $type, string $data) => pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));

    return UploadedFile::fake()->createWithContent('geschenk.png', "\x89PNG\r\n\x1a\n"
        .$chunk('IHDR', pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0))
        .$chunk('IDAT', $pixelData)
        .$chunk('IEND', ''));
}

function sendWishWithImage(UploadedFile $image)
{
    return test()->post(route('wishbox.store'), ['title' => 'Wunsch mit Bild', 'receiver' => 'Sonia', 'image' => $image]);
}

test('the original is scaled down to 1920 × 1080 and the thumbnail is 400 × 300', function () {
    sendWishWithImage(UploadedFile::fake()->image('geschenk.jpg', 3000, 2000))->assertSessionHasNoErrors();

    $wish = Wish::firstWhere('title', 'Wunsch mit Bild');

    expect(getimagesize(Storage::disk('public')->path($wish->image)))->toMatchArray([0 => 1620, 1 => 1080])
        ->and(getimagesize(Storage::disk('public')->path($wish->image_thumbnail)))->toMatchArray([0 => 400, 1 => 300]);
});

test('images with too many pixels are rejected before they are decoded', function () {
    sendWishWithImage(fakePng(20000, 20000))
        ->assertSessionHasErrors(['image' => 'Das Bild hat zu viele Pixel, höchstens 50 Megapixel. Bitte nimm ein kleineres Foto.']);

    expect(Wish::count())->toBe(0)
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('an image without a readable header is rejected', function () {
    sendWishWithImage(UploadedFile::fake()->createWithContent('geschenk.png', "\x89PNG\r\n\x1a\nnichts weiter"))
        ->assertSessionHasErrors(['image' => 'Das Bild konnte nicht gelesen werden. Bitte nimm ein anderes Foto.']);

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('an image GD can not decode gives a German message and leaves no file behind', function () {
    $this->from(route('wishbox'))
        ->post(route('wishbox.store'), ['title' => 'Wunsch mit Bild', 'receiver' => 'Sonia', 'image' => fakePng(100, 100)])
        ->assertRedirect(route('wishbox'))
        ->assertSessionHasInput('title', 'Wunsch mit Bild')
        ->assertSessionHasErrors(['image' => 'Das Bild konnte nicht gelesen werden. Bitte nimm ein anderes Foto.']);

    expect(Wish::count())->toBe(0)
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('an image GD can not decode shows up at the add form of the dashboard', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.wishes.store'), ['title' => 'Neu', 'image' => fakePng(100, 100)])
        ->assertSessionHasErrorsIn('addWish', 'image');
});

test('a new image replaces the old one and deletes its files', function () {
    $admin = User::factory()->admin()->create();
    $wish = Wish::factory()->onWishNote()->create();

    $this->actingAs($admin)->patch(route('admin.wishes.update', $wish), [
        'title' => $wish->title,
        'image' => UploadedFile::fake()->image('alt.jpg'),
    ])->assertSessionHasNoErrors();
    $old = $wish->fresh()->only('image', 'image_thumbnail');

    $this->actingAs($admin)->patch(route('admin.wishes.update', $wish), [
        'title' => $wish->title,
        'image' => UploadedFile::fake()->image('neu.jpg'),
    ])->assertSessionHasNoErrors();
    $wish->refresh();

    expect($wish->image)->not->toBe($old['image']);
    Storage::disk('public')->assertExists([$wish->image, $wish->image_thumbnail]);
    Storage::disk('public')->assertMissing([$old['image'], $old['image_thumbnail']]);
});

test('a failed replacement keeps the old image', function () {
    $admin = User::factory()->admin()->create();
    $wish = Wish::factory()->onWishNote()->create();

    $this->actingAs($admin)->patch(route('admin.wishes.update', $wish), [
        'title' => $wish->title,
        'image' => UploadedFile::fake()->image('alt.jpg'),
    ]);
    $old = $wish->fresh()->only('image', 'image_thumbnail');

    $this->actingAs($admin)->patch(route('admin.wishes.update', $wish), [
        'title' => 'Neuer Titel',
        'image' => fakePng(100, 100),
    ])->assertSessionHasErrors('image');

    expect($wish->fresh()->only('title', 'image', 'image_thumbnail'))->toBe(['title' => $wish->title, ...$old])
        ->and(Storage::disk('public')->allFiles())->toEqualCanonicalizing(array_values($old));
});

test('editing without a new image keeps the image', function () {
    $admin = User::factory()->admin()->create();
    $wish = Wish::factory()->onWishNote()->create();

    $this->actingAs($admin)->patch(route('admin.wishes.update', $wish), [
        'title' => $wish->title,
        'image' => UploadedFile::fake()->image('alt.jpg'),
    ]);
    $old = $wish->fresh()->only('image', 'image_thumbnail');

    $this->actingAs($admin)->patch(route('admin.wishes.update', $wish), ['title' => 'Neuer Titel'])
        ->assertSessionHasNoErrors();

    expect($wish->fresh()->only('image', 'image_thumbnail'))->toBe($old);
    Storage::disk('public')->assertExists(array_values($old));
});

test('animated GIFs are stored as still images', function () {
    // Two frames of 1 × 1 px with a frame delay
    $gif = base64_decode('R0lGODlhAQABAIAAAP///wAAACH/C05FVFNDQVBFMi4wAwEAAAAh+QQACgAAACwAAAAAAQABAAACAkQBACH5BAAKAAAALAAAAAABAAEAgAAAAP///wICRAEAOw==');

    sendWishWithImage(UploadedFile::fake()->createWithContent('geschenk.gif', $gif))->assertSessionHasNoErrors();

    $stored = Storage::disk('public')->get(Wish::firstWhere('title', 'Wunsch mit Bild')->image);

    expect(substr_count($stored, "\x21\xF9\x04"))->toBeLessThanOrEqual(1);
});
