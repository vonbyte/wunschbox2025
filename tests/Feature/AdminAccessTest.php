<?php

use App\Models\User;
use App\Models\Wish;

test('guests are redirected to the login', function () {
    $this->get(route('admin.index'))->assertRedirect(route('login'));
});

test('users without admin role cannot open the admin area', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)->get(route('admin.index'))->assertForbidden();
});

test('users without admin role cannot change or delete wishes', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $wish = Wish::factory()->create();

    $this->actingAs($user)->delete(route('admin.wishes.destroy', $wish))->assertForbidden();
    $this->actingAs($user)->patch(route('admin.wishes.update-status', $wish), ['status' => 'packed'])->assertForbidden();
    $this->actingAs($user)->patch(route('admin.wishes.toggle', $wish))->assertForbidden();
    $this->actingAs($user)
        ->patch(route('admin.wishes.update', $wish), ['title' => 'Gekapert', 'receiver' => 'Sonia'])
        ->assertForbidden();
    $this->actingAs($user)
        ->post(route('admin.wishes.store'), ['title' => 'Gekapert', 'receiver' => 'Sonia'])
        ->assertForbidden();

    expect($wish->fresh())
        ->not->toBeNull()
        ->status->toBe('idea')
        ->is_public->toBeFalse();
});

test('users without admin role cannot use the profile', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)->get(route('profile.edit'))->assertForbidden();
    $this->actingAs($user)
        ->patch(route('profile.update'), ['name' => 'X', 'email' => 'x@example.com'])
        ->assertForbidden();

    expect($user->fresh()->email)->not->toBe('x@example.com');
});

test('users without admin role can still log out', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)->post(route('logout'))->assertRedirect('/');
    $this->assertGuest();
});

test('admins can open the admin area', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.index'))->assertOk();
});

test('registration is not available', function () {
    $this->get('/register')->assertNotFound();
    $this->post('/register', [
        'name' => 'Fremd',
        'email' => 'fremd@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();

    expect(User::where('email', 'fremd@example.com')->exists())->toBeFalse();
});
