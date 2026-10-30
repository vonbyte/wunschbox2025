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
            abort(404, __('This wish is not public.'));
        }

        return view('wishlist.show', compact('wish'));
    }

}
