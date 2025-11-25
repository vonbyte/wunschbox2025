<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWishRequest;
use App\Models\Wish;

class WishboxController extends Controller
{

    public function index()
    {
        return view('wishbox');
    }

    public function store(StoreWishRequest $request)
    {
        $validatedData = $request->validated();

        $validatedData['example_links'] = explode("\r\n",
            $validatedData['example_links']);
        $validatedData['is_public'] = false;

        Wish::create($validatedData);

        return redirect()
            ->route('wishbox')
            ->with('success', 'Wunsch erfolgreich übermittelt');
    }

}
