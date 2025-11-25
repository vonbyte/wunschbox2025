<?php

namespace App\Http\Requests;

class AdminStoreWishRequest extends StoreWishRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->is_admin();
    }
}
