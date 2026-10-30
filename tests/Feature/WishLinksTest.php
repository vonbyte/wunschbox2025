<?php

use App\Models\User;
use App\Models\Wish;

test('links are split by line and comma and stored as a list', function () {
    $this->post(route('wishbox.store'), [
        'title' => 'Kopfhörer',
        'receiver' => 'Sonia',
        'example_links' => " https://example.com/a \r\nhttps://example.com/b,https://example.com/c\n\n,",
    ])->assertSessionHasNoErrors();

    expect(Wish::firstWhere('title', 'Kopfhörer')->example_links)
        ->toBe(['https://example.com/a', 'https://example.com/b', 'https://example.com/c']);
});

test('a wish without links stores no links', function (?string $links) {
    $this->post(route('wishbox.store'), [
        'title' => 'Ohne Links',
        'receiver' => 'Sonia',
        'example_links' => $links,
    ])->assertSessionHasNoErrors();

    expect(Wish::firstWhere('title', 'Ohne Links')->example_links)->toBeNull();
})->with([null, '', ' , ']);

test('commas inside a link are kept', function () {
    $this->post(route('wishbox.store'), [
        'title' => 'Socken',
        'receiver' => 'Sonia',
        'example_links' => "https://www.example.ch/socken?farben=rot,blau, https://example.com/b,\nhttps://example.com/c/1,2,3",
    ])->assertSessionHasNoErrors();

    expect(Wish::firstWhere('title', 'Socken')->example_links)->toBe([
        'https://www.example.ch/socken?farben=rot,blau',
        'https://example.com/b',
        'https://example.com/c/1,2,3',
    ]);
});

test('a comma before another scheme still splits, so the second link is checked on its own', function () {
    $this->post(route('wishbox.store'), [
        'title' => 'Böser Link',
        'receiver' => 'Sonia',
        'example_links' => 'https://example.com/ok,javascript:alert(1)',
    ])->assertSessionHasErrors('example_links.1');
});

test('only http and https links are accepted', function (string $link) {
    $this->post(route('wishbox.store'), [
        'title' => 'Böser Link',
        'receiver' => 'Sonia',
        'example_links' => "https://example.com/ok\n{$link}",
    ])->assertSessionHasErrors('example_links.1');

    expect(Wish::where('title', 'Böser Link')->exists())->toBeFalse();
})->with([
    'javascript:alert(document.cookie)',
    'JavaScript:alert(1)',
    'data:text/html,<script>alert(1)</script>',
    'ftp://example.com/datei',
    'www.example.com',
]);

test('a rejected link shows a German message and keeps the typed text', function () {
    $this->from(route('wishbox'))
        ->followingRedirects()
        ->post(route('wishbox.store'), [
            'title' => 'Böser Link',
            'receiver' => 'Sonia',
            'example_links' => "https://example.com/ok\njavascript:alert(1)",
        ])
        ->assertOk()
        ->assertSee('Links müssen mit http:// oder https:// beginnen: javascript:alert(1)')
        ->assertSee("https://example.com/ok\njavascript:alert(1)</textarea>", false);
});

test('links sent as a list are checked one by one instead of failing', function () {
    $this->post(route('wishbox.store'), [
        'title' => 'Liste',
        'receiver' => 'Sonia',
        'example_links' => ['https://example.com/a', ['verschachtelt']],
    ])->assertSessionHasErrors('example_links.1');

    expect(Wish::where('title', 'Liste')->exists())->toBeFalse();
});

test('a wish takes at most ten links', function () {
    $links = collect(range(1, 11))->map(fn ($i) => "https://example.com/{$i}")->implode("\n");

    $this->post(route('wishbox.store'), [
        'title' => 'Zu viele',
        'receiver' => 'Sonia',
        'example_links' => $links,
    ])->assertSessionHasErrors('example_links');
});

test('admins can only save web links too', function () {
    $admin = User::factory()->admin()->create();
    $wish = Wish::factory()->onWishNote()->create(['example_links' => ['https://example.com/alt']]);

    $this->actingAs($admin)
        ->patch(route('admin.wishes.update', $wish), ['title' => 'Neu', 'example_links' => 'javascript:alert(1)'])
        ->assertSessionHasErrors('example_links.0');

    $this->actingAs($admin)
        ->post(route('admin.wishes.store'), ['title' => 'Neu', 'example_links' => 'javascript:alert(1)'])
        ->assertSessionHasErrorsIn('addWish', 'example_links.0');

    expect($wish->fresh()->example_links)->toBe(['https://example.com/alt'])
        ->and(Wish::where('title', 'Neu')->exists())->toBeFalse();
});

test('the edit form shows the stored links one per line', function () {
    $wish = Wish::factory()->create(['example_links' => ['https://example.com/a', 'https://example.com/b']]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.wishes.edit', $wish))
        ->assertOk()
        ->assertSee("https://example.com/a\nhttps://example.com/b</textarea>", false);
});

test('stored links that are no web links are shown without a link', function () {
    $wish = Wish::factory()->onWishNote()->create([
        'example_links' => ['https://example.com/decke', 'javascript:alert(1)', ' javascript:alert(2)'],
    ]);

    $this->get(route('wish.show', $wish))
        ->assertOk()
        ->assertSee('href="https://example.com/decke"', false)
        ->assertSee('javascript:alert(1)')
        ->assertDontSee('href="javascript:', false)
        ->assertDontSee('href=" javascript:', false);
});
