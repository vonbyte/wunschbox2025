<?php

namespace App\Http\Requests;

use App\Models\Wish;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreWishRequest extends FormRequest
{
    /**
     * Largest image guests may upload; PHP's upload_max_filesize and post_max_size must allow it
     */
    public const int IMAGE_MAX_KILOBYTES = 10000;

    /**
     * GD needs about 4 bytes per pixel, so a small file with huge dimensions would exhaust the memory.
     * 50 megapixels take about 200 MB and still allow full size photos of 48 megapixel phone cameras.
     */
    public const int IMAGE_MAX_MEGAPIXELS = 50;

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
            'image' => ['bail', 'nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:'.self::IMAGE_MAX_KILOBYTES, $this->checkPixels(...)],
            'example_links' => ['nullable', 'array', 'max:10'],
            'example_links.*' => ['bail', 'string', 'max:2048', 'url:'.implode(',', Wish::LINK_SCHEMES)],
        ];
    }

    /**
     * Reads only the header of the image, before GD decodes the whole picture
     */
    protected function checkPixels(string $attribute, mixed $value, Closure $fail): void
    {
        $size = $value instanceof UploadedFile ? @getimagesize($value->getRealPath()) : false;

        if ($size === false) {
            $fail(__('validation.wishes.image.unreadable'));
        } elseif ($size[0] * $size[1] > self::IMAGE_MAX_MEGAPIXELS * 1_000_000) {
            $fail(__('validation.wishes.image.pixels', ['megapixels' => self::IMAGE_MAX_MEGAPIXELS]));
        }
    }

    /**
     * The error bag of this form, for errors found after the validation
     */
    public function errorBagName(): string
    {
        return $this->errorBag;
    }

    /**
     * The German messages of the wish form, from "wishes" in lang/de/validation.php
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = trans('validation.wishes', ['megabytes' => self::IMAGE_MAX_KILOBYTES / 1000]);

        // Without a translation, trans() returns the key and Laravel's own messages apply
        return is_array($messages) ? Arr::dot($messages) : [];
    }
}
