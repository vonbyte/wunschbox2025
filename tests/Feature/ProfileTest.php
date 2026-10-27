<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->admin()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->admin()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
});

test('account cannot be deleted', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->delete('/profile', ['password' => 'password'])
        ->assertMethodNotAllowed();

    $this->assertNotNull($user->fresh());
});
