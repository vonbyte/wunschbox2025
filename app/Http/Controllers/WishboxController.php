<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWishRequest;
use App\Models\Wish;
use App\Services\WishService;

class WishboxController extends Controller
{

    public function __construct(private readonly WishService $wishService) {}

    public function index()
    {
        return view('wishbox');
    }

    public function store(StoreWishRequest $request)
    {
        $validatedData = $request->validated();
        $wishData = $this->wishService->processWishData($validatedData);

        $wishData['is_public'] = false;

        Wish::create($wishData);

        return redirect()
            ->route('wishbox')
            ->with('success', 'Wunsch erfolgreich übermittelt');
    }

}
