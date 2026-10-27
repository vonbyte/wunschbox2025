@props(['size' => 'md'])

@php
    // Two copper wires with micro lights, computed in PHP so the SVG stays static markup.
    // The wide viewBox is shown at its natural height and cropped to the screen width,
    // so it only scales up (and crops at the bottom) on screens wider than 2560px.
    $width = 2560;
    $height = $size === 'sm' ? 34 : 54;
    $scale = $size === 'sm' ? 0.7 : 1;

    $front = fn (float $x) => (14 + sin($x / 38) * 7 + sin($x / 11) * 1.5) * $scale;
    $back = fn (float $x) => (26 + sin($x / 45 + 2) * 8) * $scale;

    $wire = function (callable $y) use ($width) {
        $d = 'M0 '.round($y(0), 1);
        for ($x = 8; $x <= $width; $x += 8) {
            $d .= ' L'.$x.' '.round($y($x), 1);
        }

        return $d;
    };

    $lights = [];
    for ($x = 8; $x < $width; $x += 17) {
        $lights[] = ['x' => $x, 'y' => round($front($x), 1), 'r' => 2.2, 'halo' => 6, 'color' => '#fff3d1', 'delay' => ($x * 13 % 30) / 10];
    }
    for ($x = 20; $x < $width; $x += 29) {
        $lights[] = ['x' => $x, 'y' => round($back($x), 1), 'r' => 1.8, 'halo' => 5, 'color' => '#ffe9b0', 'delay' => ($x * 7 % 30) / 10];
    }

    $stars = [];
    if ($size !== 'sm') {
        foreach ([[92, 6], [300, 4.5], [468, 7], [590, 4], [760, 5.5], [930, 4], [1110, 6.5], [1290, 4.5], [1480, 6]] as $i => [$x, $r]) {
            // Repeat the pattern across the wide viewBox and keep every star inside the box
            foreach ([0, 1600] as $offset) {
                if ($x + $offset < $width) {
                    $stars[] = [$x + $offset, $i % 2 ? 44 : 40, $r];
                }
            }
        }
    }
    $starPath = function (float $cx, float $cy, float $r) {
        $points = [];
        for ($k = 0; $k < 10; $k++) {
            $angle = M_PI / 5 * $k - M_PI / 2;
            $radius = $k % 2 ? $r * 0.42 : $r;
            $points[] = round($cx + cos($angle) * $radius, 1).' '.round($cy + sin($angle) * $radius, 1);
        }

        return 'M'.implode(' L', $points).' Z';
    };
@endphp

<svg viewBox="0 0 {{ $width }} {{ $height }}" preserveAspectRatio="xMidYMin slice" aria-hidden="true" focusable="false"
     {{ $attributes->merge(['class' => 'block w-full', 'style' => "height: {$height}px"]) }}>
    <path d="{{ $wire($front) }}" fill="none" stroke="var(--color-copper)" stroke-width="1" opacity=".9"/>
    <path d="{{ $wire($back) }}" fill="none" stroke="var(--color-copper)" stroke-width="1" opacity=".6"/>
    @foreach($lights as $light)
        <g class="fairy-glow" style="animation-delay: {{ $light['delay'] }}s">
            <circle cx="{{ $light['x'] }}" cy="{{ $light['y'] }}" r="{{ $light['halo'] }}" fill="#ffd98a" opacity=".18"/>
            <circle cx="{{ $light['x'] }}" cy="{{ $light['y'] }}" r="{{ $light['r'] }}" fill="{{ $light['color'] }}"/>
        </g>
    @endforeach
    @foreach($stars as [$cx, $cy, $r])
        <path d="{{ $starPath($cx, $cy, $r) }}" fill="#e9c46a"/>
    @endforeach
</svg>
