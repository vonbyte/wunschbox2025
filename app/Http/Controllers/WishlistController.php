<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Wish;

class WishlistController
{

    public function index()
    {
        $wishes = Wish::where('is_public', true)->get();
        return view('wishlist', compact('wishes'));
    }

}
