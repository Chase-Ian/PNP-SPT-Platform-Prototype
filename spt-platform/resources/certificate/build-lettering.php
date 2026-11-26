<?php

/**
 * Draws the certificate's fixed wording as vector outlines (SVG):
 *
 *   title.svg         "Certificate of Participation"   Matura MT Script Capitals
 *   presented-to.svg  "is presented to"                 Bookman Old Style Bold
 *   tagline.svg       DICTM: "Leading digital transformation"   Arial Bold + Brush Script MT
 *
 * These are the fonts of the official DICTM certificate. They come with
 * Microsoft Office, and their license doesn't allow putting the font files in
 * this public repository or on the server. Artwork made with them may be
 * shared, though, so only the drawn letters are committed; the fonts never
 * leave the computer that runs this script.
 *
 * Run it from spt-platform/ on a computer that has the fonts installed:
 *
 *     php resources/certificate/build-lettering.php [fonts folder]
 *
 * The fonts folder defaults to C:\Windows\Fonts. Rerun it after changing any
 * wording below. Sizes are in points and the SVGs keep their print size, so
 * the certificate template needs no change.
 */

require __DIR__.'/../../vendor/autoload.php';

use FontLib\Font;
use FontLib\Glyph\OutlineComposite;

$fontsDir = rtrim($argv[1] ?? 'C:/Windows/Fonts', '/\\');

// Runs of [font file, text, size in pt, colour], set one after another on a
// common baseline. Sizes were measured from a scan of the printed certificate.
$pieces = [
    'title.svg' => [
        ['MATURASC.TTF', 'Certificate of Participation', 50.8, '#38383d'],
    ],
    'presented-to.svg' => [
        ['BOOKOSB.TTF', 'is presented to', 16.7, '#1f1f1f'],
    ],
    'tagline.svg' => [
        ['arialbd.ttf', 'DICTM:  ', 10.6, '#24397e'],
        ['BRUSHSCI.TTF', "\u{201C}Leading digital transformation\u{201D}", 14.8, '#24397e'],
    ],
];

/** Parsed font tables, cached per file. */
function font(string $path): array
{
    static $cache = [];

    if (! isset($cache[$path])) {
        if (! is_file($path)) {
            fwrite(STDERR, "Font not found: {$path}\n");
            exit(1);
        }

        $font = Font::load($path);
        $font->parse();

        $cache[$path] = [
            'font' => $font, // glyphs are read lazily from this file handle
            'upm' => $font->getData('head', 'unitsPerEm'),
            'cmap' => $font->getUnicodeCharMap(),
            'hmtx' => $font->getData('hmtx'),
            'loca' => $font->getData('loca'),
            'glyf' => $font->getData('glyf'),
        ];
    }

    return $cache[$path];
}

/**
 * Whether a glyph has an outline (a space has none). php-font-lib can't tell:
 * it reads an empty glyph's header from the next glyph, so ask the loca table.
 */
function drawn(array $font, int $gid): bool
{
    return isset($font['glyf'][$gid], $font['loca'][$gid + 1])
        && $font['loca'][$gid + 1] > $font['loca'][$gid];
}

/**
 * A glyph's contours as lists of [x, y, onCurve] in font units. Composite
 * glyphs (accented letters) are flattened through their component matrices.
 */
function contours(array $font, int $gid): array
{
    if (! drawn($font, $gid)) {
        return [];
    }
    $glyph = $font['glyf'][$gid];
    $glyph->parseData();

    if ($glyph instanceof OutlineComposite) {
        $out = [];
        foreach ($glyph->components as $component) {
            [$a, $b, $c, $d, $e, $f] = $component->getMatrix();
            foreach (contours($font, $component->glyphIndex) as $contour) {
                $out[] = array_map(fn ($p) => [$a * $p[0] + $c * $p[1] + $e, $b * $p[0] + $d * $p[1] + $f, $p[2]], $contour);
            }
        }

        return $out;
    }

    $out = [];
    $current = [];
    foreach ($glyph->points ?? [] as $point) {
        $current[] = [$point['x'], $point['y'], (bool) $point['onCurve']];
        if ($point['endOfContour']) {
            $out[] = $current;
            $current = [];
        }
    }

    return $out;
}

/** One closed TrueType (quadratic) contour as SVG path data. */
function contourPath(array $points, callable $at): string
{
    $count = count($points);
    if ($count < 2) {
        return '';
    }

    // Start on an on-curve point. A contour made only of control points
    // starts at the implied point halfway between its last and first ones.
    $first = null;
    foreach ($points as $i => $point) {
        if ($point[2]) {
            $first = $i;
            break;
        }
    }
    if ($first === null) {
        $last = $points[$count - 1];
        array_unshift($points, [($last[0] + $points[0][0]) / 2, ($last[1] + $points[0][1]) / 2, true]);
        $count++;
    } else {
        $points = array_merge(array_slice($points, $first), array_slice($points, 0, $first));
    }

    $path = 'M'.$at($points[0]);
    for ($i = 1; $i <= $count;) {
        $point = $points[$i % $count];
        if ($point[2]) {
            $path .= 'L'.$at($point);
            $i++;

            continue;
        }

        $next = $points[($i + 1) % $count];
        if ($next[2]) {
            $end = $next;
            $i += 2;
        } else {
            $end = [($point[0] + $next[0]) / 2, ($point[1] + $next[1]) / 2, true];
            $i++;
        }
        $path .= 'Q'.$at($point).' '.$at($end);
    }

    return $path.'Z';
}

$number = fn (float $n): string => rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');

foreach ($pieces as $file => $runs) {
    // Lay the glyphs out (no kerning, as in Word) and find the ink box, in pt
    // with y pointing down from the baseline.
    $placed = [];
    $pen = 0.0;
    $box = [INF, INF, -INF, -INF];
    foreach ($runs as [$fontFile, $text, $size, $colour]) {
        $font = font("{$fontsDir}/{$fontFile}");
        $scale = $size / $font['upm'];
        foreach (mb_str_split($text) as $char) {
            $gid = $font['cmap'][mb_ord($char)] ?? 0;
            if (drawn($font, $gid)) {
                $glyph = $font['glyf'][$gid];
                $glyph->parseData();
                $box = [
                    min($box[0], $pen + $glyph->xMin * $scale), min($box[1], -$glyph->yMax * $scale),
                    max($box[2], $pen + $glyph->xMax * $scale), max($box[3], -$glyph->yMin * $scale),
                ];
                $placed[] = [$font, $gid, $pen, $scale, $colour];
            }
            $pen += $font['hmtx'][$gid][0] * $scale;
        }
    }

    // Draw each colour as one path, shifted so the ink box starts at (pad, pad).
    // dompdf sizes an SVG from its width and height as pixels at 96 dpi
    // (1pt = 4/3 px) but draws viewBox units as pixels without scaling them,
    // so everything is drawn in pixels and the viewBox matches the size.
    $px = 4 / 3;
    $pad = 1.0;
    $paths = [];
    foreach ($placed as [$font, $gid, $x, $scale, $colour]) {
        $at = fn ($p) => $number(($x + $p[0] * $scale - $box[0] + $pad) * $px).' '.$number((-$p[1] * $scale - $box[1] + $pad) * $px);
        foreach (contours($font, $gid) as $contour) {
            $paths[$colour] = ($paths[$colour] ?? '').contourPath($contour, $at);
        }
    }

    $width = ($box[2] - $box[0] + 2 * $pad) * $px;
    $height = ($box[3] - $box[1] + 2 * $pad) * $px;
    $svg = sprintf(
        '<svg xmlns="http://www.w3.org/2000/svg" width="%1$s" height="%2$s" viewBox="0 0 %1$s %2$s">',
        $number($width), $number($height),
    )."\n";
    foreach ($paths as $colour => $d) {
        $svg .= "<path fill=\"{$colour}\" d=\"{$d}\"/>\n";
    }
    file_put_contents(__DIR__."/{$file}", $svg.'</svg>'."\n");

    printf("%-17s %6.1f x %5.1f mm\n", $file, $width / $px * 25.4 / 72, $height / $px * 25.4 / 72);
}
