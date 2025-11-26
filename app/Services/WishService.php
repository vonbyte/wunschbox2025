<?php

namespace App\Services;

use App\Models\Wish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WishService
{
    public function __construct(private readonly ImageService $imageService) {}

    /**
     * Process validated wish data (handle image upload and link parsing)
     */
    public function processWishData(Request $request, ?Wish $existingWish = null): array
    {
        $validatedData = $request->validated();
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if updating
            if ($existingWish && $existingWish->image) {
                $this->imageService->deleteWithThumbnail($existingWish->image);
            }

            // Store image with thumbnail
            $imagePaths = $this->imageService->storeWithThumbnail($request->file('image'));
            $validatedData['image'] = $imagePaths['original'];
            $validatedData['image_thumbnail'] = $imagePaths['thumbnail'];
        }

        // Parse example links
        if (!empty($validatedData['example_links'])) {
            $links = preg_split('/[\r\n,]+/', $validatedData['example_links']);
            $validatedData['example_links'] = array_values(
                array_filter(
                    array_map('trim', $links),
                    fn($link) => !empty($link)
                )
            );
        } else {
            $validatedData['example_links'] = null;
        }

        return $validatedData;
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
