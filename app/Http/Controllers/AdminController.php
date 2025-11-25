<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminStoreWishRequest;
use App\Models\Wish;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    public function index()
    {
        $guestWishes = Wish::where('is_public', false)->latest()->get();
        $myWishes = Wish::where('is_public', true)->latest()->get();

        return view('admin.dashboard', compact('guestWishes', 'myWishes'));
    }

    public function store(AdminStoreWishRequest $request)
    {
        $validatedData = $request->validated();

        $validatedData['example_links'] = explode('\n',
            $validatedData['example_links']);
        $validatedData['is_public'] = true;
        $validatedData['receiver'] = auth()->user()->name;

        Wish::create($validatedData);

        return back()->with('success', 'Wunsch hinzugefügt!');
    }

    public function edit(Wish $wish)
    {
        $wish->example_links = implode('\n',$wish->example_links);
        return view('admin.edit', compact('wish'));
    }

    public function update(AdminStoreWishRequest $request, Wish $wish)
    {
        $validatedData = $request->validated();

        $validatedData['example_links'] = explode('\n',
            $validatedData['example_links']);

        $wish->update($validatedData);

        return redirect()
            ->route('admin.index')
            ->with('success', 'Wunsch erfolgreich geändert!');
    }

    public function togglePublic(Wish $wish)
    {
        $wish->update(['is_public' => !$wish->is_public]);
        return back()->with('success', 'Status geändert!');
    }

    public function destroy(Wish $wish)
    {
        $wish->delete();
        return back()->with('success', 'Wunsch dauerhaft gelöscht!');
    }

}
