<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Wish;

class WishlistController
{

    public function index()
    {
        $wishes = Wish::sorted()->onWishNote()->get();
        return view('wishlist', compact('wishes'));
    }

    public function show(Wish $wish) {

        if (!$wish->isOnWishNote() && !auth()->user()?->isAdmin()) {
            abort(404, 'Dieser Wunsch ist nicht öffentlich.');
        }

        return view('wishlist.show', compact('wish'));
    }

}
