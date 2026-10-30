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
     * Number of guest wishes per dashboard filter
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
            'open' => $count(Wish::OPEN_STATUSES),
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

        return back()->with('success', __('Wish added!'));
    }

    public function edit(Wish $wish)
    {

        return view('admin.edit', compact('wish'));
    }

    public function update(AdminStoreWishRequest $request, Wish $wish)
    {

        $this->wishService->updateWish($wish, $this->wishService->processWishData($request));

        return redirect()
            ->route('admin.index')
            ->with('success', __('Wish saved!'));
    }

    public function updateStatus(Request $request, Wish $wish)
    {
        $validated = $request->validate([
            'status' => 'required|in:idea,open,ordered,delivered,packed,unpacked',
        ]);

        $wish->update(['status' => $validated['status']]);

        return back()->with('success', __('Status updated!'));
    }

    public function updateSortNr(Request $request, Wish $wish)
    {
        $validated = $request->validate([
            'sortnr' => 'required|integer|min:0',
        ], [
            'sortnr' => __('validation.wishes.sortnr'),
        ]);

        $wish->update(['sortnr' => $validated['sortnr']]);

        return back()->with('success', __('Sort number updated!'));
    }

    public function togglePublic(Wish $wish)
    {
        if (!$wish->canTogglePublic()) {
            return back()->withErrors([
                'is_public' => __('Only wishes of :owner go on the wish note.', ['owner' => Wish::owner()]),
            ]);
        }

        $wish->update(['is_public' => !$wish->is_public]);
        return back()->with('success', __('Visibility changed!'));
    }

    public function destroy(Wish $wish)
    {
        $this->wishService->deleteWish($wish);

        // Back to the dashboard with its filter; from the detail page back() would lead to the deleted wish
        $previous = url()->previous();
        $target = strtok($previous, '?') === route('admin.index') ? $previous : route('admin.index');

        return redirect()->to($target)->with('success', __('Wish deleted for good!'));
    }

    public function updatePackageNr(Request $request, Wish $wish) {
        $validated = $request->validate([
            'package_nr' => 'required|integer|min:0',
        ], [
            'package_nr' => __('validation.wishes.package_nr'),
        ]);

        $wish->update(['package_nr' => $validated['package_nr']]);

        return back()->with('success', __('Package number updated!'));
    }

}
