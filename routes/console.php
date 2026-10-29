<?php

use App\Services\FairyLightsService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('wishbox:fairy-lights', function (FairyLightsService $fairyLights) {
    foreach (FairyLightsService::SIZES as $size) {
        File::ensureDirectoryExists(dirname(FairyLightsService::path($size)));
        File::put(FairyLightsService::path($size), $fairyLights->svg($size));
        $this->info('Wrote '.FairyLightsService::path($size));
    }
})->purpose('Rebuild the fairy lights header SVGs in resources/images');
