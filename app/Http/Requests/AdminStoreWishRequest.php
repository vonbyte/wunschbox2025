<?php

namespace App\Http\Requests;

use App\Models\Wish;
use Illuminate\Validation\Rule;

// Access is checked by the admin middleware on all admin routes
class AdminStoreWishRequest extends StoreWishRequest
{
    /**
     * Admins may keep the current receiver of an existing wish, even if it is not in the list.
     */
    public function rules(): array
    {
        $receivers = array_filter([...Wish::RECEIVERS, $this->route('wish')?->receiver]);

        return [
            ...parent::rules(),
            'receiver' => ['required', Rule::in($receivers)],
        ];
    }
}
