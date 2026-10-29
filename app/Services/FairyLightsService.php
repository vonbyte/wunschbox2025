<?php

namespace App\Services;

/**
 * Builds the fairy lights header deco as standalone SVG files.
 *
 * The output is static, so `php artisan wishbox:fairy-lights` writes it to resources/images once
 * and the pages load it as a cached background image instead of inlining it on every request.
 */
class FairyLightsService
{
    public const SIZES = ['md', 'sm'];

    private const WIDTH = 2560;

    private const COPPER = '#c58a4a';

    public static function path(string $size): string
    {
        return resource_path($size === 'sm' ? 'images/fairy-lights-sm.svg' : 'images/fairy-lights.svg');
    }

    /**
     * Two copper wires with micro lights. The wide box is shown at its natural height and cropped
     * to the screen width, so it only scales up (and crops at the bottom) on screens wider than 2560px.
     */
    public function svg(string $size): string
    {
        $width = self::WIDTH;
        $height = $size === 'sm' ? 34 : 54;
        $scale = $size === 'sm' ? 0.7 : 1;

        $front = fn (float $x) => (14 + sin($x / 38) * 7 + sin($x / 11) * 1.5) * $scale;
        $back = fn (float $x) => (26 + sin($x / 45 + 2) * 8) * $scale;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$width.'" height="'.$height.'" viewBox="0 0 '.$width.' '.$height.'">'."\n";
        $svg .= '<style>'
            .'.glow{animation:twinkle 3.2s ease-in-out infinite}'
            .'@keyframes twinkle{0%,100%{opacity:.95}50%{opacity:.4}}'
            .'@media (prefers-reduced-motion:reduce){.glow{animation:none}}'
            .'</style>'."\n";
        $svg .= '<path d="'.$this->wire($front).'" fill="none" stroke="'.self::COPPER.'" stroke-width="1" opacity=".9"/>'."\n";
        $svg .= '<path d="'.$this->wire($back).'" fill="none" stroke="'.self::COPPER.'" stroke-width="1" opacity=".6"/>'."\n";

        foreach ($this->lights($front, $back) as $light) {
            $svg .= '<g class="glow" style="animation-delay:'.$light['delay'].'s">'
                .'<circle cx="'.$light['x'].'" cy="'.$light['y'].'" r="'.$light['halo'].'" fill="#ffd98a" opacity=".18"/>'
                .'<circle cx="'.$light['x'].'" cy="'.$light['y'].'" r="'.$light['r'].'" fill="'.$light['color'].'"/>'
                .'</g>'."\n";
        }

        if ($size !== 'sm') {
            foreach ($this->stars() as [$cx, $cy, $r]) {
                $svg .= '<path d="'.$this->starPath($cx, $cy, $r).'" fill="#e9c46a"/>'."\n";
            }
        }

        return $svg.'</svg>'."\n";
    }

    private function wire(callable $y): string
    {
        $d = 'M0 '.round($y(0), 1);
        for ($x = 8; $x <= self::WIDTH; $x += 8) {
            $d .= ' L'.$x.' '.round($y($x), 1);
        }

        return $d;
    }

    private function lights(callable $front, callable $back): array
    {
        $lights = [];
        for ($x = 8; $x < self::WIDTH; $x += 17) {
            $lights[] = ['x' => $x, 'y' => round($front($x), 1), 'r' => 2.2, 'halo' => 6, 'color' => '#fff3d1', 'delay' => ($x * 13 % 30) / 10];
        }
        for ($x = 20; $x < self::WIDTH; $x += 29) {
            $lights[] = ['x' => $x, 'y' => round($back($x), 1), 'r' => 1.8, 'halo' => 5, 'color' => '#ffe9b0', 'delay' => ($x * 7 % 30) / 10];
        }

        return $lights;
    }

    private function stars(): array
    {
        $stars = [];
        foreach ([[92, 6], [300, 4.5], [468, 7], [590, 4], [760, 5.5], [930, 4], [1110, 6.5], [1290, 4.5], [1480, 6]] as $i => [$x, $r]) {
            // Repeat the pattern across the wide box and keep every star inside it
            foreach ([0, 1600] as $offset) {
                if ($x + $offset < self::WIDTH) {
                    $stars[] = [$x + $offset, $i % 2 ? 44 : 40, $r];
                }
            }
        }

        return $stars;
    }

    private function starPath(float $cx, float $cy, float $r): string
    {
        $points = [];
        for ($k = 0; $k < 10; $k++) {
            $angle = M_PI / 5 * $k - M_PI / 2;
            $radius = $k % 2 ? $r * 0.42 : $r;
            $points[] = round($cx + cos($angle) * $radius, 1).' '.round($cy + sin($angle) * $radius, 1);
        }

        return 'M'.implode(' L', $points).' Z';
    }
}
