<?php

use App\Models\User;
use App\Models\Wish;

// One guest wish per status, plus one on the wish note that no filter shows
beforeEach(function () {
    foreach (array_keys(Wish::getStatuses()) as $i => $status) {
        Wish::factory()->create(['status' => $status, 'title' => "Gast {$status}", 'receiver' => 'Sonia', 'sortnr' => $i]);
    }
    Wish::factory()->onWishNote()->create(['status' => Wish::STATUS_PACKED, 'title' => 'Auf dem Wunschzettel']);
});

function guestWishesFor(?string $filter): array
{
    return test()->actingAs(User::factory()->admin()->create())
        ->get(route('admin.index', array_filter(['filter' => $filter])))
        ->assertOk()
        ->viewData('guestWishes')
        ->pluck('title')
        ->all();
}

test('each filter shows only guest wishes with its statuses', function (?string $filter, array $titles) {
    expect(guestWishesFor($filter))->toEqualCanonicalizing($titles);
})->with([
    'all' => [null, ['Gast idea', 'Gast open', 'Gast ordered', 'Gast delivered', 'Gast packed', 'Gast unpacked']],
    'open' => ['open', ['Gast idea', 'Gast open', 'Gast ordered']],
    'delivered' => ['delivered', ['Gast delivered']],
    'packed' => ['packed', ['Gast packed']],
    'unpacked' => ['unpacked', ['Gast unpacked']],
]);

test('filters keep their order', function (string $filter, string $status, array $wishes, array $titles) {
    Wish::query()->delete();
    foreach ($wishes as [$title, $sortnr, $daysAgo]) {
        Wish::factory()->create(['status' => $status, 'title' => $title, 'receiver' => 'Sonia', 'sortnr' => $sortnr, 'created_at' => now()->subDays($daysAgo)]);
    }

    expect(guestWishesFor($filter))->toBe($titles);
})->with([
    // Newest first, the sort number does not count
    'open' => ['open', Wish::STATUS_IDEA, [['Alt', 1, 2], ['Neu', 2, 0], ['Mitte', 0, 1]], ['Neu', 'Mitte', 'Alt']],
    'delivered' => ['delivered', Wish::STATUS_DELIVERED, [['Alt', 1, 2], ['Neu', 2, 0], ['Mitte', 0, 1]], ['Neu', 'Mitte', 'Alt']],
    // Packed in the order of their sort number, unpacked the other way round
    'packed' => ['packed', Wish::STATUS_PACKED, [['Zwei', 2, 0], ['Eins', 1, 0], ['Drei', 3, 0]], ['Eins', 'Zwei', 'Drei']],
    'unpacked' => ['unpacked', Wish::STATUS_UNPACKED, [['Zwei', 2, 0], ['Eins', 1, 0], ['Drei', 3, 0]], ['Drei', 'Zwei', 'Eins']],
]);
