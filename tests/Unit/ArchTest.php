<?php

// No preset()->laravel(): it allows only resource actions in controllers (the dashboard has quick edits)
// and form requests only in controllers (WishService takes the wish form request)
arch()->preset()->php();

arch()->preset()->security();

arch('no debug calls are left behind')
    ->expect(['dd', 'ddd', 'dump', 'ray'])
    ->not->toBeUsed();
