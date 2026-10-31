<?php

use App\Models\User;
use App\Models\Wish;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('deleting a wish on its detail page leads to the workshop', function () {
    $wish = Wish::factory()->onWishNote()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('wish.show', $wish))
        ->delete(route('admin.wishes.destroy', $wish))
        ->assertRedirect(route('admin.index'));

    $this->get(route('admin.index'))
        ->assertOk()
        ->assertSee('Wunsch dauerhaft gelöscht!');

    expect(Wish::find($wish->id))->toBeNull();
});

test('deleting a wish on the dashboard stays on the dashboard with its filter', function () {
    $wish = Wish::factory()->create(['receiver' => 'Sonia']);

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.index', ['filter' => 'open']))
        ->delete(route('admin.wishes.destroy', $wish))
        ->assertRedirect(route('admin.index', ['filter' => 'open']));
});

test('deleting a wish deletes its image and thumbnail', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $wish = Wish::factory()->onWishNote()->create();

    $this->actingAs($admin)->patch(route('admin.wishes.update', $wish), [
        'title' => $wish->title,
        'image' => UploadedFile::fake()->image('geschenk.jpg'),
    ]);
    $files = array_values($wish->fresh()->only('image', 'image_thumbnail'));
    Storage::disk('public')->assertExists($files);

    $this->actingAs($admin)->delete(route('admin.wishes.destroy', $wish));

    Storage::disk('public')->assertMissing($files);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});
