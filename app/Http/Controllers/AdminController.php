<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminStoreWishRequest;
use App\Models\Wish;
use App\Services\WishService;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    public function __construct(private readonly WishService $wishService) {}

    public function index(Request $request)
    {
        $myWishes = Wish::sorted()->onWishNote()->get();
        $filter = in_array($request->query('filter'), ['packed', 'unpacked', 'open', 'delivered'], true)
            ? $request->query('filter')
            : null;

        if ($filter) {
            $guestWishes = Wish::{ucfirst($filter)}()
                ->notOnWishNote()
                ->get();
        } else {
            $guestWishes = Wish::sorted()
                ->notOnWishNote()
                ->get();
        }

        $counts = $this->guestWishCounts();

        return view('admin.dashboard', compact('guestWishes', 'myWishes', 'filter', 'counts'));
    }

    /**
     * Number of guest wishes per dashboard filter, "open" groups the same statuses as Wish::scopeOpen()
     */
    private function guestWishCounts(): array
    {
        $perStatus = Wish::notOnWishNote()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $count = fn (array $statuses) => (int) $perStatus->only($statuses)->sum();

        return [
            'all' => (int) $perStatus->sum(),
            'open' => $count([Wish::STATUS_IDEA, Wish::STATUS_OPEN, Wish::STATUS_ORDERED]),
            'delivered' => $count([Wish::STATUS_DELIVERED]),
            'packed' => $count([Wish::STATUS_PACKED]),
            'unpacked' => $count([Wish::STATUS_UNPACKED]),
        ];
    }

    public function store(AdminStoreWishRequest $request)
    {

        $wishData = $this->wishService->processWishData($request);

        // Wishes added here always go on the owner's wish note
        $wishData['receiver'] = Wish::owner();
        $wishData['is_public'] = true;
        $wishData['status'] = Wish::STATUS_IDEA;

        Wish::create($wishData);

        return back()->with('success', 'Wunsch hinzugefügt!');
    }

    public function edit(Wish $wish)
    {

        return view('admin.edit', compact('wish'));
    }

    public function update(AdminStoreWishRequest $request, Wish $wish)
    {

        $wishData = $this->wishService->processWishData($request, $wish);

        $wish->update($wishData);

        return redirect()
            ->route('admin.index')
            ->with('success', 'Wunsch erfolgreich geändert!');
    }

    public function updateStatus(Request $request, Wish $wish)
    {
        $validated = $request->validate([
            'status' => 'required|in:idea,open,ordered,delivered,packed,unpacked',
        ]);

        $wish->update(['status' => $validated['status']]);

        return back()->with('success', 'Status aktualisiert!');
    }

    public function updateSortNr(Request $request, Wish $wish)
    {
        $validated = $request->validate([
            'sortnr' => 'required|integer|min:0',
        ], [
            'sortnr' => 'Die Sortierung muss eine ganze Zahl ab 0 sein.',
        ]);

        $wish->update(['sortnr' => $validated['sortnr']]);

        return back()->with('success', 'Sortierung aktualisiert!');
    }

    public function togglePublic(Wish $wish)
    {
        if (!$wish->is_public && !$wish->belongsToOwner()) {
            return back()->withErrors([
                'is_public' => 'Auf den Wunschzettel kommen nur Wünsche von '.Wish::owner().'.',
            ]);
        }

        $wish->update(['is_public' => !$wish->is_public]);
        return back()->with('success', 'Status geändert!');
    }

    public function destroy(Wish $wish)
    {
        $this->wishService->deleteWish($wish);
        return back()->with('success', 'Wunsch dauerhaft gelöscht!');
    }

    public function updatePackageNr(Request $request, Wish $wish) {
        $validated = $request->validate([
            'package_nr' => 'required|integer|min:0',
        ], [
            'package_nr' => 'Die Päckchen-Nummer muss eine ganze Zahl ab 0 sein.',
        ]);

        $wish->update(['package_nr' => $validated['package_nr']]);

        return back()->with('success', 'Päckchen-Nummer aktualisiert!');
    }

}
