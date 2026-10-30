<?php

namespace App\Http\Requests;

use App\Models\Wish;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWishRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The form sends the links as text, one per line or comma separated. Split them here,
     * so every single link can be validated.
     */
    protected function prepareForValidation(): void
    {
        $links = $this->input('example_links');

        if (is_string($links)) {
            $this->merge([
                'example_links' => array_values(array_filter(
                    array_map('trim', preg_split('/[\r\n,]+/', $links)),
                    fn ($link) => $link !== ''
                )),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'receiver' => ['required', Rule::in(Wish::RECEIVERS)],
            'description' => 'nullable|string|max:5000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10000',
            'example_links' => ['nullable', 'array', 'max:10'],
            // Only web links: anything else, like javascript: or data:, would end up in an href
            'example_links.*' => ['bail', 'string', 'max:2048', 'url:http,https'],
        ];
    }
}
