<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Thumbnails were generated on upload, but their path was never saved
     * because image_thumbnail was missing from the model's $fillable.
     */
    public function up(): void
    {
        DB::table('wishes')
            ->whereNotNull('image')
            ->whereNull('image_thumbnail')
            // By id, not offset: updated rows drop out of the whereNull filter
            ->eachById(function ($wish) {
                // Same naming as ImageService at the time of this migration
                $pathInfo = pathinfo($wish->image);
                $thumbnailPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'_thumb.'.$pathInfo['extension'];

                if (Storage::disk('public')->exists($thumbnailPath)) {
                    DB::table('wishes')
                        ->where('id', $wish->id)
                        ->update(['image_thumbnail' => $thumbnailPath]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to undo: the backfilled paths point to existing thumbnails.
    }
};
