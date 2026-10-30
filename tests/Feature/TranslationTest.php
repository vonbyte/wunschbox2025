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

test('the admin area follows the locale', function () {
    $admin = App\Models\User::factory()->admin()->create(['name' => 'Tanja']);
    $wish = Wish::factory()->create(['title' => 'Schlitten', 'receiver' => 'Sonia', 'status' => Wish::STATUS_PACKED]);

    $this->actingAs($admin)->get(route('admin.index'))
        ->assertSee(['Hallo Tanja', 'Gäste-Wünsche', 'Eingepackt', 'Werkstatt', 'Schlitten löschen', 'Wirklich löschen?'], false);

    app()->setLocale('en');

    $this->actingAs($admin)->get(route('admin.index'))
        ->assertSee(['Hello Tanja', 'Guest wishes', 'Packed', 'Workshop', 'Delete Schlitten', 'Really delete?', 'No wishes on your wish note yet'])
        ->assertDontSee(['Gäste-Wünsche', 'Eingepackt', 'Abmelden', 'Wirklich']);
    $this->actingAs($admin)->get(route('admin.wishes.edit', $wish))
        ->assertSee(['Edit wish', 'Save', 'Cancel'])
        ->assertDontSee(['Speichern', 'Abbrechen']);
    $this->actingAs($admin)->get(route('profile.edit'))
        ->assertSee(['Name and email', 'Change password', 'Current password'])
        ->assertDontSee(['Passwort ändern']);
    $this->actingAs($admin)->from(route('admin.index'))
        ->patch(route('admin.wishes.update-status', $wish), ['status' => Wish::STATUS_DELIVERED])
        ->assertSessionHas('success', 'Status updated!');
});

test('login and password reset are German', function () {
    Illuminate\Support\Facades\Notification::fake();
    $admin = App\Models\User::factory()->admin()->create();

    $this->get(route('login'))->assertSee(['Anmelden', 'Angemeldet bleiben', 'Passwort vergessen?']);
    $this->get(route('password.request'))->assertSee(['Passwort vergessen? Kein Problem.', 'Link zum Zurücksetzen schicken']);

    $this->post(route('login'), ['email' => $admin->email, 'password' => 'falsch'])
        ->assertSessionHasErrors(['email' => 'Diese Zugangsdaten stimmen nicht.']);
    $this->post(route('password.email'), ['email' => $admin->email])
        ->assertSessionHas('status', 'Der Link zum Zurücksetzen ist per E-Mail unterwegs.');

    Illuminate\Support\Facades\Notification::assertSentTo($admin, Illuminate\Auth\Notifications\ResetPassword::class, function ($notification) use ($admin) {
        $mail = $notification->toMail($admin);

        return $mail->subject === 'Passwort zurücksetzen' && $mail->actionText === 'Passwort zurücksetzen';
    });

    app()->setLocale('en');

    $this->get(route('login'))->assertSee(['Log in', 'Remember me'])->assertDontSee('Angemeldet bleiben');
});

test('validation and error pages outside the wish form are German', function () {
    $admin = App\Models\User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put(route('password.update'), ['current_password' => 'falsch', 'password' => 'kurz', 'password_confirmation' => 'anders'])
        ->assertSessionHasErrorsIn('updatePassword', [
            'current_password' => 'Das Passwort ist falsch.',
            'password' => 'Passwort und Bestätigung stimmen nicht überein.',
        ]);
    $this->actingAs($admin)
        ->patch(route('profile.update'), ['name' => '', 'email' => 'keine-adresse'])
        ->assertSessionHasErrors(['name' => 'Bitte Name ausfüllen.', 'email' => 'E-Mail muss eine gültige E-Mail-Adresse sein.']);

    $this->get('/gibt-es-nicht')->assertNotFound()->assertSee('Nicht gefunden');
});
