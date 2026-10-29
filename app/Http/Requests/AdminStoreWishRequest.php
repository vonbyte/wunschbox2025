<?php

namespace App\Http\Requests;

use App\Models\Wish;
use Illuminate\Validation\Rule;

// Access is checked by the admin middleware on all admin routes
class AdminStoreWishRequest extends StoreWishRequest
{
    /**
     * Admins may keep the current receiver of an existing wish, even if it is not in the list.
     * New wishes and wishes on the wish note get no receiver from the request.
     */
    public function rules(): array
    {
        $wish = $this->route('wish');
        $receivers = array_filter([...Wish::RECEIVERS, $wish?->receiver]);

        return [
            ...parent::rules(),
            // New admin wishes belong to the wish note owner (see AdminController::store()),
            // and wishes on the wish note keep her as receiver
            'receiver' => $wish && !$wish->isOnWishNote() ? ['required', Rule::in($receivers)] : ['exclude'],
        ];
    }
}
