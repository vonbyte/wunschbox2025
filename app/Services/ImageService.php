<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    private ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Store image and create thumbnail
     *
     * @return array ['original' => 'path/to/original.jpg', 'thumbnail' => 'path/to/thumb.jpg']
     */
    public function storeWithThumbnail($uploadedFile): array
    {
        // Store original image (max 1920px width)
        $originalPath = $uploadedFile->store('wishes', 'public');
        $fullPath = Storage::disk('public')->path($originalPath);

        // Resize original if too large
        $image = $this->imageManager->read($fullPath);

        // Max dimensions for original: 1920x1080
        if ($image->width() > 1920 || $image->height() > 1080) {
            $image->scale(width: 1920, height: 1080);
            $image->save($fullPath, quality: 85);
        } else {
            // Re-save with compression
            $image->save($fullPath, quality: 85);
        }

        // Create thumbnail (400x300)
        $thumbnail = $this->imageManager->read($fullPath);
        $thumbnail->cover(400, 300); // Crop to exact size

        // Generate thumbnail path
        $pathInfo = pathinfo($originalPath);
        $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];

        Storage::disk('public')->put(
            $thumbnailPath,
            (string) $thumbnail->encode()
        );

        return [
            'original' => $originalPath,
            'thumbnail' => $thumbnailPath,
        ];
    }

    /**
     * Delete image and its thumbnail
     */
    public function deleteWithThumbnail(?string $imagePath): void
    {
        if (!$imagePath) {
            return;
        }

        // Delete original
        Storage::disk('public')->delete($imagePath);

        // Delete thumbnail
        $pathInfo = pathinfo($imagePath);
        $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        Storage::disk('public')->delete($thumbnailPath);
    }

    /**
     * Get thumbnail path from original path
     */
    public function getThumbnailPath(string $originalPath): string
    {
        $pathInfo = pathinfo($originalPath);
        return $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
    }
}
