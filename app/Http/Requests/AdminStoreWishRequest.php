<?php

namespace App\Http\Requests;

use App\Models\Wish;
use Illuminate\Validation\Rule;

class AdminStoreWishRequest extends StoreWishRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

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
