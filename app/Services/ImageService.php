<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use RuntimeException;

class ImageService
{
    private ImageManager $imageManager;

    public function __construct()
    {
        // Only the first frame of animated GIFs: every frame costs as much memory as a whole image
        $this->imageManager = new ImageManager(new Driver(), decodeAnimation: false);
    }

    /**
     * Store image and create thumbnail. Both are decoded and encoded before anything is written,
     * so an upload GD can not read leaves no file behind.
     *
     * @return array ['original' => 'path/to/original.jpg', 'thumbnail' => 'path/to/thumb.jpg']
     *
     * @throws \Intervention\Image\Exceptions\DecoderException when GD can not read the upload
     */
    public function storeWithThumbnail(UploadedFile $uploadedFile): array
    {
        $image = $this->imageManager->decodePath($uploadedFile->getRealPath());

        // Max dimensions for original: 1920x1080
        $image->scaleDown(width: 1920, height: 1080);

        $originalPath = 'wishes/'.$uploadedFile->hashName();
        $thumbnailPath = $this->getThumbnailPath($originalPath);

        $original = $image->encodeUsingPath($originalPath, quality: 85);
        // Create thumbnail (400x300)
        $thumbnail = (clone $image)->cover(400, 300)->encodeUsingPath($thumbnailPath);

        $disk = Storage::disk('public');

        if (!$disk->put($originalPath, (string) $original) || !$disk->put($thumbnailPath, (string) $thumbnail)) {
            $disk->delete([$originalPath, $thumbnailPath]);

            throw new RuntimeException("Could not store the image {$originalPath}.");
        }

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
        Storage::disk('public')->delete($this->getThumbnailPath($imagePath));
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
