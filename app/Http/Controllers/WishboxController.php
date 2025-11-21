<?php

namespace App\Http\Controllers;

use App\Models\Wish;
use Illuminate\Http\Request;

class WishboxController extends Controller
{

    public function index()
    {
        return view('wishlist');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'receiver' => 'required',
            'description' => 'nullable',
        ]);

        Wish::create($validatedData);

        return redirect()->route('home')->with('success', 'Wunsch erfolgreich übermittelt');
    }

}
