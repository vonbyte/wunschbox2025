<?php

use App\Models\Wish;
use Illuminate\Support\Facades\File;

// With APP_LOCALE=en the pages show the English source texts, which proves they are translation strings

test('every translation has a German text', function () {
    $translations = json_decode(File::get(lang_path('de.json')), true, flags: JSON_THROW_ON_ERROR);

    expect(array_filter($translations, fn ($text) => trim($text) === ''))->toBeEmpty();
});

test('the public pages follow the locale', function () {
    config(['wishbox.owner' => 'Tanja']);
    $this->travelTo(Carbon\Carbon::parse('2026-12-20 12:00'));
    $wish = Wish::factory()->onWishNote()->create([
        'title' => 'Schlitten',
        'example_links' => ['https://example.com/a', 'https://example.com/b'],
        'created_at' => '2026-11-03 10:00',
    ]);

    $this->get(route('wishbox'))
        ->assertSee(['Santa hat die Rentiere schon vorgewarnt.', 'Ich wünsche mir', 'Nur noch 4 Tage bis Weihnachten', 'Tanjas Wunschzettel ansehen']);
    $this->get(route('wishlist'))
        ->assertSee(['Mein Wunschzettel', '2 Links', 'Deine Tanja']);
    $this->get(route('wish.show', $wish))
        ->assertSee(['Zurück zum Wunschzettel', 'Beispiele', 'Aufgeschrieben am 03.11.2026']);

    app()->setLocale('en');

    $this->get(route('wishbox'))
        ->assertSee(['<html lang="en">', 'Santa has already warned the reindeer.', 'I wish for', 'Only 4 days until Christmas', 'See Tanja&#039;s wish note'], false)
        ->assertDontSee(['Ich wünsche mir', 'Tippen zum Anheften', 'Ab in die Box!']);
    $this->get(route('wishlist'))
        ->assertSee(['My wish note', '2 links', 'Yours, Tanja'])
        ->assertDontSee(['Lieber Santa', 'Selbst einen Wunsch']);
    $this->get(route('wish.show', $wish))
        ->assertSee(['Back to the wish note', 'Examples', 'Written down on 11/03/2026'])
        ->assertDontSee(['Beispiele', 'Aufgeschrieben']);
});

test('the success message after sending a wish follows the locale', function () {
    app()->setLocale('en');

    $this->post(route('wishbox.store'), ['title' => 'Schlitten', 'receiver' => 'Sonia'])
        ->assertSessionHas('success', 'Wish sent');
});
