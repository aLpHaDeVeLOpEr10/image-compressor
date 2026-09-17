<?php

namespace App\Services\ImageCompression;

use GdImage;

class GdImageCompressor
{
    private const MIN_QUALITY = 10;

    private const MAX_RESIZE_ROUNDS = 8;

    private const MIN_DIMENSION = 16;

    private const SUPPORTED_TYPES = [
        IMAGETYPE_JPEG => 'image/jpeg',
        IMAGETYPE_PNG => 'image/png',
        IMAGETYPE_WEBP => 'image/webp',
    ];

    public function __construct(private readonly int $maxPixels) {}

    public function compress(string $path, CompressionOptions $options): CompressionResult
    {
        $image = $this->load($path);

        if ($options->outputMime === 'image/png' && $this->hasAlphaChannel($path)) {
            $options = new CompressionOptions($options->outputMime, 100, $options->targetBytes);
        }

        return $options->targetBytes === null
            ? $this->encode($image, $options->outputMime, $options->quality)
            : $this->compressToTarget($image, $options);
    }

    /**
     * GD's palette conversion discards alpha, so images that can carry transparency are kept as lossless PNG.
     */
    public function hasAlphaChannel(string $path): bool
    {
        $header = (string) file_get_contents($path, false, null, 0, 64);

        if (str_starts_with($header, "\x89PNG")) {
            return in_array(ord($header[25] ?? "\0"), [4, 6], true)
                || str_contains((string) file_get_contents($path, false, null, 0, 1024 * 1024), 'tRNS');
        }

        if (strlen($header) >= 25 && substr($header, 0, 4) === 'RIFF' && substr($header, 8, 4) === 'WEBP') {
            return match (substr($header, 12, 4)) {
                'VP8X' => (ord($header[20]) & 0x10) !== 0,
                'VP8L' => (ord($header[24]) & 0x10) !== 0,
                default => false,
            };
        }

        return false;
    }

    private function load(string $path): GdImage
    {
        $info = @getimagesize($path);
        $mime = $info === false ? null : (self::SUPPORTED_TYPES[$info[2]] ?? null);

        if ($mime === null || $info[0] < 1 || $info[1] < 1) {
            throw ImageProcessingException::invalidImage();
        }

        if ($this->maxPixels < $info[0] * $info[1]) {
            throw ImageProcessingException::tooManyPixels();
        }

        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
        };

        if (! $image instanceof GdImage) {
            throw ImageProcessingException::invalidImage();
        }

        if (! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $mime === 'image/jpeg' ? $this->applyExifOrientation($image, $path) : $image;
    }

    private function compressToTarget(GdImage $source, CompressionOptions $options): CompressionResult
    {
        $work = $source;
        $best = null;

        for ($round = 0; $round <= self::MAX_RESIZE_ROUNDS; $round++) {
            $result = $this->bestWithinTarget($work, $options);

            if ($best === null || $result->size() < $best->size() || $result->targetMet) {
                $best = $result;
            }

            if ($result->targetMet) {
                break;
            }

            $factor = max(0.5, min(0.9, sqrt($options->targetBytes / $result->size()) * 0.95));
            $width = (int) floor(imagesx($work) * $factor);
            $height = (int) floor(imagesy($work) * $factor);

            if ($width < self::MIN_DIMENSION || $height < self::MIN_DIMENSION) {
                break;
            }

            $work = $this->resize($source, $width, $height);
        }

        return new CompressionResult(
            data: $best->data,
            mime: $best->mime,
            width: $best->width,
            height: $best->height,
            quality: $best->quality,
            resized: $best->width !== imagesx($source) || $best->height !== imagesy($source),
            targetMet: $best->targetMet,
        );
    }

    private function bestWithinTarget(GdImage $image, CompressionOptions $options): CompressionResult
    {
        $target = $options->targetBytes;
        $levels = $options->outputMime === 'image/png'
            ? $this->pngLevels($options->quality)
            : null;

        $fits = fn (CompressionResult $r) => $r->size() <= $target;

        $first = $this->encode($image, $options->outputMime, $options->quality);

        if ($fits($first)) {
            return $this->withTargetMet($first, true);
        }

        if ($levels !== null) {
            $last = $first;

            foreach ($levels as $level) {
                $last = $this->encode($image, 'image/png', $level);

                if ($fits($last)) {
                    return $this->withTargetMet($last, true);
                }
            }

            return $this->withTargetMet($last, false);
        }

        $lowest = $this->encode($image, $options->outputMime, self::MIN_QUALITY);

        if (! $fits($lowest)) {
            return $this->withTargetMet($lowest, false);
        }

        $best = $lowest;
        $low = self::MIN_QUALITY + 1;
        $high = $options->quality - 1;

        while ($low <= $high) {
            $mid = intdiv($low + $high, 2);
            $attempt = $this->encode($image, $options->outputMime, $mid);

            if ($fits($attempt)) {
                $best = $attempt;
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        }

        return $this->withTargetMet($best, true);
    }

    /**
     * Lower PNG "quality" levels step down the palette size used after the initial attempt.
     *
     * @return array<int, int>
     */
    private function pngLevels(int $quality): array
    {
        if ($quality >= 100) {
            return [];
        }

        $start = min($quality, 90);

        return array_values(array_filter([70, 50, 30, self::MIN_QUALITY], fn (int $q) => $q < $start));
    }

    private function encode(GdImage $image, string $mime, int $quality): CompressionResult
    {
        ob_start();

        try {
            $ok = match ($mime) {
                'image/jpeg' => $this->encodeJpeg($image, $quality),
                'image/webp' => imagewebp($image, null, $quality),
                'image/png' => $this->encodePng($image, $quality),
            };
            $data = (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }

        if (! $ok || $data === '') {
            throw new ImageProcessingException('Something went wrong while compressing your image. Please try again.');
        }

        return new CompressionResult($data, $mime, imagesx($image), imagesy($image), $quality);
    }

    private function encodeJpeg(GdImage $image, int $quality): bool
    {
        $flattened = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefill($flattened, 0, 0, imagecolorallocate($flattened, 255, 255, 255));
        imagealphablending($flattened, true);
        imagecopy($flattened, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        imageinterlace($flattened, true);

        return imagejpeg($flattened, null, $quality);
    }

    private function encodePng(GdImage $image, int $quality): bool
    {
        if ($quality >= 100) {
            return imagepng($image, null, 9);
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $palette = imagecreatetruecolor($width, $height);
        imagealphablending($palette, false);
        imagesavealpha($palette, true);
        imagecopy($palette, $image, 0, 0, 0, 0, $width, $height);
        imagetruecolortopalette($palette, false, $this->paletteColors($quality));
        imagesavealpha($palette, true);

        return imagepng($palette, null, 9);
    }

    /**
     * Maps quality 10–99 to a palette of 16–256 colours on an exponential scale.
     */
    private function paletteColors(int $quality): int
    {
        $ratio = max(0, min(1, ($quality - self::MIN_QUALITY) / 80));

        return (int) min(256, max(16, round(2 ** (4 + 4 * $ratio))));
    }

    private function withTargetMet(CompressionResult $result, bool $met): CompressionResult
    {
        return new CompressionResult(
            $result->data, $result->mime, $result->width, $result->height, $result->quality, $result->resized, $met,
        );
    }

    private function resize(GdImage $source, int $width, int $height): GdImage
    {
        $resized = imagecreatetruecolor($width, $height);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 0, 0, 0, 127));
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));

        return $resized;
    }

    private function applyExifOrientation(GdImage $image, string $path): GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = (int) (@exif_read_data($path)['Orientation'] ?? 1);

        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($image, $orientation === 4 ? IMG_FLIP_VERTICAL : IMG_FLIP_HORIZONTAL);
        }

        $angle = match ($orientation) {
            3 => 180,
            5, 8 => 90,
            6, 7 => -90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        if (! $rotated instanceof GdImage) {
            return $image;
        }

        return $rotated;
    }
}
