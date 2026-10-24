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
        $myWishes = Wish::sorted()->where('is_public', true)->get();
        $hasFilter = $request->has('filter');

        if ($hasFilter && in_array($request->filter, ['packed', 'unpacked', 'open','delivered'])) {
            $scope = ucfirst($request->query('filter'));
            $guestWishes = Wish::{$scope}()
                ->where('is_public', false)
                ->get();
        } else {
            $guestWishes = Wish::sorted()
                ->where('is_public', false)
                ->get();
        }

        return view('admin.dashboard', compact('guestWishes', 'myWishes'));
    }

    public function store(AdminStoreWishRequest $request)
    {

        $wishData = $this->wishService->processWishData($request);

        $wishData['is_public'] = true;
        $wishData['receiver'] = auth()->user()->name;
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
            'sortnr' => ['required', 'regex:/^\d{1,3}$/'],
        ]);

        // sortnr is a string column, so pad it to keep the text order numeric
        $wish->update(['sortnr' => str_pad($validated['sortnr'], 3, '0', STR_PAD_LEFT)]);

        return back()->with('success', 'Sortierung aktualisiert!');
    }

    public function togglePublic(Wish $wish)
    {
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
            'package_nr' => 'required|integer',
        ]);

        $wish->update(['package_nr' => $validated['package_nr']]);

        return back()->with('success', 'Päckchen-Nummer aktualisiert!');
    }

}
