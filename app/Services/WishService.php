<?php

namespace App\Services;

use App\Models\Wish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WishService
{
    /**
     * Process validated wish data (handle image upload and link parsing)
     */
    public function processWishData(array $validatedData, Request $request, ?Wish $existingWish = null): array
    {
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if updating
            if ($existingWish && $existingWish->image) {
                Storage::disk('public')->delete($existingWish->image);
            }

            $validatedData['image'] = $request->file('image')->store('wishes', 'public');
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
            Storage::disk('public')->delete($wish->image);
        }

        $wish->delete();
    }
}
