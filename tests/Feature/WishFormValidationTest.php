<?php

use App\Http\Requests\StoreWishRequest;
use App\Models\User;
use App\Models\Wish;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

test('a description may have up to 5000 characters', function () {
    $this->post(route('wishbox.store'), [
        'title' => 'Lange Geschichte',
        'receiver' => 'Sonia',
        'description' => str_repeat('a', 5000),
    ])->assertSessionHasNoErrors();

    $this->from(route('wishbox'))
        ->followingRedirects()
        ->post(route('wishbox.store'), [
            'title' => 'Zu lange Geschichte',
            'receiver' => 'Sonia',
            'description' => str_repeat('a', 5001),
        ])
        ->assertSee('Die Beschreibung darf höchstens 5000 Zeichen lang sein.');

    expect(Wish::where('title', 'Lange Geschichte')->exists())->toBeTrue()
        ->and(Wish::where('title', 'Zu lange Geschichte')->exists())->toBeFalse();
});

test('admins can not save longer descriptions either', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.wishes.store'), ['title' => 'Neu', 'description' => str_repeat('a', 5001)])
        ->assertSessionHasErrorsIn('addWish', 'description');
});

test('the wish form shows German messages', function (array $input, string $field, string $message) {
    $this->post(route('wishbox.store'), [...['title' => 'Schlitten', 'receiver' => 'Sonia'], ...$input])
        ->assertSessionHasErrors([$field => $message]);
})->with([
    'no title' => [['title' => ''], 'title', 'Bitte einen Wunsch eintragen.'],
    'long title' => [['title' => str_repeat('a', 256)], 'title', 'Der Wunsch darf höchstens 255 Zeichen lang sein.'],
    'no receiver' => [['receiver' => ''], 'receiver', 'Bitte auswählen, von wem der Wunsch ist.'],
    'unknown receiver' => [['receiver' => 'Rudolph'], 'receiver', 'Bitte auswählen, von wem der Wunsch ist.'],
    'no image' => [fn () => ['image' => UploadedFile::fake()->create('wunsch.pdf', 10, 'application/pdf')], 'image', 'Das Bild muss ein JPG, PNG oder GIF sein.'],
    'webp image' => [fn () => ['image' => UploadedFile::fake()->image('wunsch.webp')], 'image', 'Das Bild muss ein JPG, PNG oder GIF sein.'],
    'large image' => [fn () => ['image' => UploadedFile::fake()->image('wunsch.jpg')->size(10001)], 'image', 'Das Bild darf höchstens 10 MB gross sein.'],
    'failed upload' => [fn () => ['image' => new UploadedFile(UploadedFile::fake()->image('wunsch.jpg')->getPathname(), 'wunsch.jpg', 'image/jpeg', UPLOAD_ERR_INI_SIZE, true)], 'image', 'Das Bild konnte nicht hochgeladen werden. Vielleicht ist es zu gross?'],
]);

test('the messages of the wish form do not apply to other forms', function () {
    $errors = Validator::make(['title' => '', 'image' => 'kein Bild'], ['title' => 'required', 'image' => 'image'])->errors();

    expect($errors->first('title'))->not->toBe('Bitte einen Wunsch eintragen.')
        ->and($errors->first('image'))->not->toBe('Das Bild muss ein JPG, PNG oder GIF sein.');
});

test('the image size message follows the limit of the rule', function () {
    expect((new StoreWishRequest)->messages())
        ->toHaveKey('image.max', 'Das Bild darf höchstens '.(StoreWishRequest::IMAGE_MAX_KILOBYTES / 1000).' MB gross sein.')
        ->toHaveKey('example_links.*.url', 'Links müssen mit http:// oder https:// beginnen: :input');
});

test('no message on the wish form is English or has a placeholder left', function () {
    $this->post(route('wishbox.store'), [
        'title' => ['kein Text'],
        'receiver' => ['kein Text'],
        'description' => ['kein Text'],
        'image' => 'kein Bild',
        'example_links' => [['kein Text'], 'kein Link'],
    ]);

    // The session stores the error bags as arrays (JSON serialization)
    $messages = collect(session('errors')['default']['messages'])->flatten();

    expect($messages)->not->toBeEmpty()
        ->and($messages->filter(fn ($message) => str_starts_with($message, 'The ') || str_contains($message, ':input')))->toBeEmpty();
});

test('the file picker offers only the accepted image types', function () {
    $this->get(route('wishbox'))
        ->assertOk()
        ->assertSee('accept="image/jpeg,image/png,image/gif"', false);
});

test('the form shows up again after fields were sent as lists', function (array $input) {
    $this->from(route('wishbox'))
        ->followingRedirects()
        ->post(route('wishbox.store'), [...['title' => 'Schlitten', 'receiver' => 'Sonia'], ...$input])
        ->assertOk()
        ->assertSee('Ab in die Box!');
})->with([
    'title' => [['title' => ['kein', 'Text']]],
    'description' => [['description' => ['kein', 'Text']]],
    'nested links' => [['example_links' => ['https://example.com/a', ['verschachtelt']]]],
]);

test('the admin edit form shows up again after fields were sent as lists', function () {
    $wish = Wish::factory()->onWishNote()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.wishes.edit', $wish))
        ->followingRedirects()
        ->patch(route('admin.wishes.update', $wish), [
            'title' => ['kein', 'Text'],
            'description' => ['kein', 'Text'],
            'example_links' => ['https://example.com/a', ['verschachtelt']],
        ])
        ->assertOk()
        ->assertSee('https://example.com/a</textarea>', false);
});
