<?php

namespace App\Http\Requests;

use App\Models\Wish;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWishRequest extends FormRequest
{
    /**
     * Largest image guests may upload; PHP's upload_max_filesize and post_max_size must allow it
     */
    public const int IMAGE_MAX_KILOBYTES = 10000;

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
                    // A comma only separates links when the next one starts with a scheme,
                    // so commas inside a link (?farben=rot,blau) stay; stray ones at the ends go
                    array_map(fn ($link) => trim($link, " \t,"), preg_split('/[\r\n]+|,\s*(?=[a-z][a-z0-9+.-]*:)/i', $links)),
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:'.self::IMAGE_MAX_KILOBYTES,
            'example_links' => ['nullable', 'array', 'max:10'],
            // Only web links: anything else, like javascript: or data:, would end up in an href
            'example_links.*' => ['bail', 'string', 'max:2048', 'url:http,https'],
        ];
    }
}
