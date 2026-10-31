<?php

namespace App\Services;

use App\Http\Requests\StoreWishRequest;
use App\Models\Wish;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Exceptions\DecoderException;
use Throwable;

class WishService
{
    public function __construct(private readonly ImageService $imageService) {}

    /**
     * Process validated wish data (handle image upload, the request already split the links)
     *
     * @return array<string, mixed>
     */
    public function processWishData(StoreWishRequest $request): array
    {
        $validatedData = $request->validated();
        // Handle image upload
        if ($request->hasFile('image')) {
            try {
                $imagePaths = $this->imageService->storeWithThumbnail($request->file('image'));
            } catch (DecoderException) {
                // Looked like an image to the validation, but GD can not read it
                throw ValidationException::withMessages(['image' => __('validation.wishes.image.unreadable')])
                    ->errorBag($request->errorBagName());
            }

            $validatedData['image'] = $imagePaths['original'];
            $validatedData['image_thumbnail'] = $imagePaths['thumbnail'];
        }

        // No links are stored as null, not as an empty list
        $validatedData['example_links'] = ($validatedData['example_links'] ?? null) ?: null;

        return $validatedData;
    }

    /**
     * Update a wish. A replaced image is deleted only once the new one is stored and on the wish,
     * so a failed upload never leaves the wish pointing to a deleted image.
     *
     * @param  array<string, mixed>  $wishData  from processWishData()
     */
    public function updateWish(Wish $wish, array $wishData): void
    {
        $oldImage = $wish->image;

        try {
            $wish->update($wishData);
        } catch (Throwable $e) {
            $this->imageService->deleteWithThumbnail($wishData['image'] ?? null);

            throw $e;
        }

        if (isset($wishData['image']) && $oldImage !== $wishData['image']) {
            $this->imageService->deleteWithThumbnail($oldImage);
        }
    }

    /**
     * Delete wish and its associated image
     */
    public function deleteWish(Wish $wish): void
    {
        if ($wish->image) {
            $this->imageService->deleteWithThumbnail($wish->image);
        }

        $wish->delete();
    }
}
