<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Validates, re-encodes and stores lesson images.
 *
 * Input:  JPG or PNG (decoded with GD; SVG, WebP and everything else is rejected).
 * Output: JPEG (photos) or PNG (graphics / transparency), max 1600px wide.
 *
 * Re-encoding means the stored file is always a fresh image we produced:
 * EXIF/metadata is dropped and disguised or polyglot files cannot survive.
 * WebP is not accepted because the production GD build (Dockerfile) is
 * compiled without WebP support.
 */
class LessonImageProcessor
{
    public const DIRECTORY = 'lesson-images';
    public const PATH_REGEX = '#^lesson-images/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.(jpg|png)$#';

    private const MAX_WIDTH = 1600;
    private const MAX_SOURCE_PIXELS = 16_000_000; // decompression-bomb guard (~64 MB in GD, fits default memory_limit)
    private const JPEG_QUALITY = 82;

    /**
     * @return string storage path, e.g. "lesson-images/<uuid>.jpg"
     * @throws InvalidArgumentException
     */
    public function store(UploadedFile $file): string
    {
        $raw = file_get_contents($file->getRealPath());
        $info = $raw === false ? false : @getimagesizefromstring($raw);

        // Real content check — never trust the extension or client MIME type.
        $allowed = [IMAGETYPE_JPEG, IMAGETYPE_PNG];
        if (! $info || ! in_array($info[2], $allowed, true)) {
            throw new InvalidArgumentException('Only JPG or PNG images are allowed.');
        }

        [$width, $height] = $info;
        if ($width < 1 || $height < 1 || ($width * $height) > self::MAX_SOURCE_PIXELS) {
            throw new InvalidArgumentException('That image is too large in pixel dimensions. Please resize it and try again.');
        }

        $source = @imagecreatefromstring($raw);
        unset($raw);
        if (! $source) {
            throw new InvalidArgumentException('That image could not be read. It may be corrupt.');
        }

        $hasAlpha = $info[2] !== IMAGETYPE_JPEG && $this->usesTransparency($source);

        // Downscale only; never upscale.
        if ($width > self::MAX_WIDTH) {
            $newHeight = (int) round($height * (self::MAX_WIDTH / $width));
            $scaled = imagescale($source, self::MAX_WIDTH, $newHeight, IMG_BICUBIC);
            imagedestroy($source);
            if (! $scaled) {
                throw new InvalidArgumentException('That image could not be processed.');
            }
            $source = $scaled;
        }

        // PNG sources and anything transparent stay lossless (sharp text in
        // charts/screenshots); photos become JPEG.
        $asPng = $info[2] === IMAGETYPE_PNG || $hasAlpha;

        ob_start();
        if ($asPng) {
            imagealphablending($source, false);
            imagesavealpha($source, true);
            imagepng($source, null, 9);
        } else {
            imagejpeg($source, null, self::JPEG_QUALITY);
        }
        $encoded = ob_get_clean();
        imagedestroy($source);

        if (! $encoded) {
            throw new InvalidArgumentException('That image could not be processed.');
        }

        $path = self::DIRECTORY . '/' . Str::uuid() . ($asPng ? '.png' : '.jpg');
        Storage::put($path, $encoded);

        return $path;
    }

    /** Collect valid image paths referenced by a lesson's blocks. */
    public static function pathsIn(?array $blocks): array
    {
        $paths = [];
        foreach ($blocks ?? [] as $block) {
            if (($block['type'] ?? null) === 'image'
                && isset($block['path'])
                && is_string($block['path'])
                && preg_match(self::PATH_REGEX, $block['path'])) {
                $paths[] = $block['path'];
            }
        }

        return array_values(array_unique($paths));
    }

    private function usesTransparency(\GdImage $img): bool
    {
        $w = imagesx($img);
        $h = imagesy($img);
        // Sample a grid instead of scanning every pixel.
        $stepX = max(1, intdiv($w, 40));
        $stepY = max(1, intdiv($h, 40));
        for ($y = 0; $y < $h; $y += $stepY) {
            for ($x = 0; $x < $w; $x += $stepX) {
                if (((imagecolorat($img, $x, $y) >> 24) & 0x7F) > 0) {
                    return true;
                }
            }
        }

        return false;
    }
}
