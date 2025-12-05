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
        $myWishes = Wish::where('is_public', true)->latest()->get();
        $hasSort = $request->has('sortnr');
        if ($hasSort) {
            $sortOrder = $request->sortnr;
            $guestWishes = Wish::orderBy('sortnr', $sortOrder)
                ->where('is_public', false)
                ->get();
        } else {
            $guestWishes = Wish::where('is_public', false)
                ->latest()
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
            'status' => 'required|in:idea,open,ordered,delivered',
        ]);

        $wish->update(['status' => $validated['status']]);

        return back()->with('success', 'Status aktualisiert!');
    }

    public function updateSortNr(Request $request, Wish $wish)
    {
        $validated = $request->validate([
            'sortnr' => 'required|string',
        ]);

        $wish->update(['sortnr' => $validated['sortnr']]);

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

}
