<?php

namespace App\Console\Commands;

use App\Services\ImageCompression\CompressionOptions;
use App\Services\ImageCompression\CompressionResult;
use App\Services\ImageCompression\GdImageCompressor;
use App\Support\Figures;
use GdImage;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Generates the original example figures used on tool pages and in guides.
 *
 * Test scenes are drawn deterministically, compressed with the application's own GdImageCompressor (the same
 * quality-then-resize approach as the in-browser tool) and every size, dimension and quality in a caption is measured.
 */
#[Signature('images:generate-examples {--font-dir= : Directory containing arial.ttf and arialbd.ttf (or DejaVuSans.ttf and DejaVuSans-Bold.ttf)}')]
#[Description('Generate the measured example figures, the 512px logo and resources/content/figures.json')]
class GenerateExampleImages extends Command
{
    private const WIDTH = 1200;

    private const HEIGHT = 675;

    private string $font;

    private string $boldFont;

    /** @var array<string, array<string, mixed>> */
    private array $figures = [];

    /** @var array<int, string> */
    private array $tempFiles = [];

    public function handle(): int
    {
        ini_set('memory_limit', '-1');
        $this->resolveFonts();

        $directory = public_path('images/examples');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $compressor = new GdImageCompressor(60_000_000);

        try {
            $steps = [
                'quality-size-curve' => fn () => $this->qualitySizeCurve($compressor),
                'jpg-quality-ladder' => fn () => $this->jpgQualityLadder($compressor),
                'png-palette-ladder' => fn () => $this->pngPaletteLadder($compressor),
                'jpg-vs-webp-equal-size' => fn () => $this->jpgVsWebp($compressor),
                'signature-under-20kb' => fn () => $this->signatureTarget($compressor),
                'target-sizes' => fn () => $this->targetSizes($compressor),
                'png-to-jpg-transparency' => fn () => $this->pngToJpg($compressor),
                'png-to-webp-transparency' => fn () => $this->pngToWebp($compressor),
                'jpg-to-webp-sizes' => fn () => $this->jpgToWebpSizes($compressor),
                'webp-to-jpg-sizes' => fn () => $this->webpToJpgSizes($compressor),
                'resize-vs-quality' => fn () => $this->resizeVsQuality($compressor),
                'responsive-widths-sizes' => fn () => $this->responsiveWidths($compressor),
                'scan-document-100kb' => fn () => $this->documentTarget($compressor),
                'format-size-comparison' => fn () => $this->formatComparison($compressor),
                'jpeg-8x8-blocks' => fn () => $this->jpegBlocks($compressor),
                'jpg-webp-avif-equal-size' => fn () => $this->jpgWebpAvif($compressor),
            ];

            foreach ($steps as $name => $step) {
                $this->components->task($name, $step);
            }

            $this->components->task('logo-512.png', fn () => $this->logo());
        } finally {
            array_map(fn (string $file) => @unlink($file), $this->tempFiles);
        }

        ksort($this->figures);
        file_put_contents(Figures::path(), json_encode($this->figures, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n");
        Figures::flush();

        $this->components->info(count($this->figures).' figures written to public/images/examples and '.str_replace(base_path().DIRECTORY_SEPARATOR, '', Figures::path()).'.');

        return self::SUCCESS;
    }

    private function resolveFonts(): void
    {
        $candidates = array_filter([
            $this->option('font-dir'),
            'C:/Windows/Fonts',
            '/usr/share/fonts/truetype/dejavu',
            '/Library/Fonts',
            '/System/Library/Fonts/Supplemental',
        ]);

        foreach ($candidates as $directory) {
            foreach ([['arial.ttf', 'arialbd.ttf'], ['Arial.ttf', 'Arial Bold.ttf'], ['DejaVuSans.ttf', 'DejaVuSans-Bold.ttf']] as [$regular, $bold]) {
                if (is_file("{$directory}/{$regular}") && is_file("{$directory}/{$bold}")) {
                    $this->font = "{$directory}/{$regular}";
                    $this->boldFont = "{$directory}/{$bold}";

                    return;
                }
            }
        }

        throw new RuntimeException('No TrueType font found. Pass --font-dir pointing to arial.ttf/arialbd.ttf or DejaVuSans.ttf/DejaVuSans-Bold.ttf.');
    }

    // ---------------------------------------------------------------------------------------------------------
    // Figures
    // ---------------------------------------------------------------------------------------------------------

    private function qualitySizeCurve(GdImageCompressor $compressor): void
    {
        $source = $this->sourceFile($this->photoScene(1600, 1067), 'png');
        $levels = range(10, 100, 10);
        $jpg = $webp = [];

        foreach ($levels as $quality) {
            $jpg[$quality] = $compressor->compress($source, new CompressionOptions('image/jpeg', $quality))->size();
            $webp[$quality] = $compressor->compress($source, new CompressionOptions('image/webp', $quality))->size();
        }

        $canvas = $this->canvas('File size vs quality setting', 'The same 1600 × 1067 test photo encoded at every quality from 10 to 100');
        $this->lineChart($canvas, 80, 150, 1060, 440, $levels, ['JPG' => $jpg, 'WebP' => $webp]);

        $this->save('quality-size-curve', $canvas,
            'Line chart of file size against quality setting for JPG and WebP versions of the same 1600 × 1067 test photo',
            sprintf('Measured with the PicsCompressor server encoder (PHP GD). At quality 80 the JPG is %s and the WebP %s; at quality 100 the JPG grows to %s. File size rises steeply above about 90.',
                $this->bytes($jpg[80]), $this->bytes($webp[80]), $this->bytes($jpg[100])));
    }

    private function jpgQualityLadder(GdImageCompressor $compressor): void
    {
        $scene = $this->photoScene(1600, 1067);
        $source = $this->sourceFile($scene, 'png');
        $canvas = $this->canvas('JPG quality 90, 70, 40 and 15', 'The same detail crop from a 1600 × 1067 test photo, enlarged 2× to show compression artifacts');
        $sizes = [];

        foreach ([90, 70, 40, 15] as $i => $quality) {
            $result = $compressor->compress($source, new CompressionOptions('image/jpeg', $quality));
            $sizes[$quality] = $result->size();
            $decoded = $this->decode($result);
            $x = 40 + ($i % 2) * 570;
            $y = 130 + intdiv($i, 2) * 265;
            $this->placeCrop($canvas, $decoded, 1030, 445, 275, 125, $x, $y, 550, 250, true);
            $this->chip($canvas, $x + 10, $y + 10, "Quality {$quality} · ".$this->bytes($result->size()));
        }

        $this->save('jpg-quality-ladder', $canvas,
            'Four enlarged crops of the same photo saved as JPG at quality 90, 70, 40 and 15, with blocking and colour smearing visible at low quality',
            sprintf('Whole-image JPG sizes: quality 90 = %s, 70 = %s, 40 = %s, 15 = %s. Measured with the PicsCompressor server encoder; enlarged 2× so artifacts are visible.',
                $this->bytes($sizes[90]), $this->bytes($sizes[70]), $this->bytes($sizes[40]), $this->bytes($sizes[15])));
    }

    private function pngPaletteLadder(GdImageCompressor $compressor): void
    {
        $source = $this->sourceFile($this->screenshotScene(1200, 800), 'png');
        $canvas = $this->canvas('PNG: lossless vs 256, 64 and 16 colours', 'The same 1200 × 800 interface screenshot, detail crop at 100%');
        $variants = ['Lossless' => 100, '256 colours' => 90, '64 colours' => 50, '16 colours' => 10];
        $sizes = [];
        $i = 0;

        foreach ($variants as $label => $quality) {
            $result = $compressor->compress($source, new CompressionOptions('image/png', $quality));
            $sizes[$label] = $result->size();
            $x = 40 + ($i % 2) * 570;
            $y = 130 + intdiv($i, 2) * 265;
            $this->placeCrop($canvas, $this->decode($result), 560, 300, 550, 250, $x, $y, 550, 250, false);
            $this->chip($canvas, $x + 10, $y + 10, "{$label} · ".$this->bytes($result->size()));
            $i++;
        }

        $this->save('png-palette-ladder', $canvas,
            'Crops of the same PNG screenshot saved lossless and reduced to 256, 64 and 16 colours, showing banding in the gradient chart at 16 colours',
            sprintf('PNG sizes: lossless %s, 256 colours %s, 64 colours %s, 16 colours %s. Produced with the server fallback\'s palette conversion; the in-browser encoder uses a different quantizer, so its results differ slightly.',
                $this->bytes($sizes['Lossless']), $this->bytes($sizes['256 colours']), $this->bytes($sizes['64 colours']), $this->bytes($sizes['16 colours'])));
    }

    private function jpgVsWebp(GdImageCompressor $compressor): void
    {
        $source = $this->sourceFile($this->photoScene(1600, 1067), 'png');
        $jpg = $compressor->compress($source, new CompressionOptions('image/jpeg', 60));
        [$webpQuality, $webp] = $this->highestQualityWithin(fn (int $q) => $compressor->compress($source, new CompressionOptions('image/webp', $q))->data, $jpg->size());

        $canvas = $this->canvas('JPG vs WebP at the same file size', 'The same detail crop, both files at or under '.$this->bytes($jpg->size()));
        $this->placeCrop($canvas, $this->decode($jpg), 1040, 520, 275, 250, 40, 130, 550, 500, true);
        $this->chip($canvas, 50, 140, 'JPG quality 60 · '.$this->bytes($jpg->size()));
        $this->placeCrop($canvas, imagecreatefromstring($webp), 1040, 520, 275, 250, 610, 130, 550, 500, true);
        $this->chip($canvas, 620, 140, "WebP quality {$webpQuality} · ".$this->bytes(strlen($webp)));

        $this->save('jpg-vs-webp-equal-size', $canvas,
            'Side-by-side enlarged crops of a JPG and a WebP of the same photo at almost the same file size',
            sprintf('JPG at quality 60 is %s. The highest WebP quality that fits the same size is %d (%s). Crops enlarged 2×; measured with the PicsCompressor server encoders.',
                $this->bytes($jpg->size()), $webpQuality, $this->bytes(strlen($webp))));
    }

    private function signatureTarget(GdImageCompressor $compressor): void
    {
        $source = $this->sourceFile($this->signatureScene(1500, 600), 'jpg', 95);
        $original = filesize($source);
        $result = $compressor->compress($source, new CompressionOptions('image/jpeg', 80, 20 * 1024));

        $this->targetFigure('signature-under-20kb', $result, $original, '20 KB', 1500, 600,
            'A handwritten signature scan compressed to under 20 KB as a JPG, with the pen strokes still sharp',
            'signature scan');
    }

    private function targetSizes(GdImageCompressor $compressor): void
    {
        $scene = $this->photoScene(4000, 3000);
        $source = $this->sourceFile($scene, 'jpg', 95);
        unset($scene);
        $original = filesize($source);

        $targets = [
            'target-50kb' => [50, '50 KB', 80],
            'target-100kb' => [100, '100 KB', 80],
            'target-200kb' => [200, '200 KB', 80],
            'target-500kb' => [500, '500 KB', 85],
            'target-1mb' => [1024, '1 MB', 90],
            'target-2mb' => [2048, '2 MB', 90],
        ];

        foreach ($targets as $key => [$kb, $label, $quality]) {
            $result = $compressor->compress($source, new CompressionOptions('image/jpeg', $quality, $kb * 1024));
            $this->targetFigure($key, $result, $original, $label, 4000, 3000,
                "A 12-megapixel landscape test photo compressed with a {$label} target, shown with its measured size, dimensions and quality",
                'photo', $quality);
        }
    }

    private function documentTarget(GdImageCompressor $compressor): void
    {
        $source = $this->sourceFile($this->documentScene(1240, 1754), 'jpg', 95);
        $original = filesize($source);
        $result = $compressor->compress($source, new CompressionOptions('image/jpeg', 80, 100 * 1024));

        $this->targetFigure('scan-document-100kb', $result, $original, '100 KB', 1240, 1754,
            'A scanned A4 application form compressed to under 100 KB as a JPG, with a 100% crop showing the text is still readable',
            'A4 document scan');
    }

    private function pngToJpg(GdImageCompressor $compressor): void
    {
        $source = $this->sourceFile($this->logoScene(800, 800), 'png');
        $png = filesize($source);
        $jpg = $compressor->compress($source, new CompressionOptions('image/jpeg', 85));

        $canvas = $this->canvas('PNG to JPG: transparency becomes white', 'An 800 × 800 logo with a transparent background, before and after conversion');
        $this->checkerboard($canvas, 40, 130, 550, 500);
        $this->placeContain($canvas, imagecreatefrompng($source), 40, 130, 550, 500);
        $this->chip($canvas, 50, 140, 'PNG with transparency · '.$this->bytes($png));
        $this->checkerboard($canvas, 610, 130, 550, 500);
        $this->placeContain($canvas, $this->decode($jpg), 610, 130, 550, 500);
        $this->chip($canvas, 620, 140, 'JPG quality 85 · '.$this->bytes($jpg->size()));

        $this->save('png-to-jpg-transparency', $canvas,
            'A logo PNG with a transparent background next to the converted JPG, where the transparent area has become solid white',
            sprintf('The transparent PNG is %s. Converted to JPG at quality 85 it is %s, and the checkerboard (transparency) is replaced by white because JPG cannot store transparency. For flat graphics like this logo JPG can be larger than PNG; the big savings come from photos saved as PNG.',
                $this->bytes($png), $this->bytes($jpg->size())));
    }

    private function pngToWebp(GdImageCompressor $compressor): void
    {
        $source = $this->sourceFile($this->productScene(1000, 800), 'png');
        $png = filesize($source);
        $webp = $compressor->compress($source, new CompressionOptions('image/webp', 80));

        $canvas = $this->canvas('PNG to WebP: transparency kept', 'A 1000 × 800 product cut-out with a soft shadow, before and after conversion');
        foreach ([[40, imagecreatefrompng($source), 'PNG · '.$this->bytes($png)], [610, $this->decode($webp), 'WebP quality 80 · '.$this->bytes($webp->size())]] as [$x, $image, $label]) {
            $this->checkerboard($canvas, $x, 130, 550, 500);
            $this->placeContain($canvas, $image, $x, 130, 550, 500);
            $this->chip($canvas, $x + 10, 140, $label);
        }

        $this->save('png-to-webp-transparency', $canvas,
            'A product cut-out PNG with a soft transparent shadow next to the WebP version, which keeps the same transparency',
            sprintf('The PNG with transparency is %s; the WebP at quality 80 is %s and keeps the transparent background and soft shadow. Measured with the PicsCompressor server encoder.',
                $this->bytes($png), $this->bytes($webp->size())));
    }

    private function jpgToWebpSizes(GdImageCompressor $compressor): void
    {
        $source = $this->sourceFile($this->photoScene(1920, 1280), 'jpg', 85);
        $bars = ['JPG q85 (original)' => filesize($source)];

        foreach ([85, 80, 75] as $quality) {
            $bars["WebP q{$quality}"] = $compressor->compress($source, new CompressionOptions('image/webp', $quality))->size();
        }

        $canvas = $this->canvas('JPG to WebP: file sizes', 'A 1920 × 1280 photo saved as JPG at quality 85, then converted to WebP');
        $this->barChart($canvas, $bars);

        $this->save('jpg-to-webp-sizes', $canvas,
            'Bar chart comparing the file size of a JPG photo with WebP conversions at quality 85, 80 and 75',
            sprintf('Original JPG %s; WebP at quality 85 %s, 80 %s and 75 %s. WebP and JPG quality numbers are not directly comparable, so compare the images, not just the numbers.',
                $this->bytes($bars['JPG q85 (original)']), $this->bytes($bars['WebP q85']), $this->bytes($bars['WebP q80']), $this->bytes($bars['WebP q75'])));
    }

    private function webpToJpgSizes(GdImageCompressor $compressor): void
    {
        $source = $this->sourceFile($this->photoScene(1920, 1280), 'webp', 80);
        $bars = ['WebP q80 (original)' => filesize($source)];

        foreach ([92, 85, 75] as $quality) {
            $bars["JPG q{$quality}"] = $compressor->compress($source, new CompressionOptions('image/jpeg', $quality))->size();
        }

        $canvas = $this->canvas('WebP to JPG: file sizes', 'A 1920 × 1280 photo saved as WebP at quality 80, then converted to JPG');
        $this->barChart($canvas, $bars);

        $this->save('webp-to-jpg-sizes', $canvas,
            'Bar chart comparing the file size of a WebP photo with JPG conversions at quality 92, 85 and 75',
            sprintf('Original WebP %s; JPG at quality 92 %s, 85 %s and 75 %s. Converting WebP to JPG is a second lossy encode, so a higher JPG quality keeps more of the remaining detail.',
                $this->bytes($bars['WebP q80 (original)']), $this->bytes($bars['JPG q92']), $this->bytes($bars['JPG q85']), $this->bytes($bars['JPG q75'])));
    }

    private function resizeVsQuality(GdImageCompressor $compressor): void
    {
        $scene = $this->photoScene(2400, 1600);
        $budget = 120 * 1024;
        $full = $this->sourceFile($scene, 'png');
        $half = $this->sourceFile($this->resized($scene, 1200, 800), 'png');

        [$fullQuality, $fullData] = $this->highestQualityWithin(fn (int $q) => $compressor->compress($full, new CompressionOptions('image/jpeg', $q))->data, $budget);
        [$halfQuality, $halfData] = $this->highestQualityWithin(fn (int $q) => $compressor->compress($half, new CompressionOptions('image/jpeg', $q))->data, $budget);

        $canvas = $this->canvas('Same file size: full resolution or resized first?', 'Both JPGs at or under '.$this->bytes($budget).', shown at the same display size');
        $this->placeCrop($canvas, $this->resized(imagecreatefromstring($fullData), 1200, 800), 520, 260, 275, 250, 40, 130, 550, 500, true);
        $this->chip($canvas, 50, 140, "2400 × 1600, quality {$fullQuality} · ".$this->bytes(strlen($fullData)));
        $this->placeCrop($canvas, imagecreatefromstring($halfData), 520, 260, 275, 250, 610, 130, 550, 500, true);
        $this->chip($canvas, 620, 140, "1200 × 800, quality {$halfQuality} · ".$this->bytes(strlen($halfData)));

        $this->save('resize-vs-quality', $canvas,
            'Two crops of the same photo at the same file size: one kept at full resolution with low JPG quality, one resized first and saved at higher quality',
            sprintf('With a %s budget, the 2400 × 1600 version needs quality %d (%s) while the 1200 × 800 version can use quality %d (%s). An image that is only displayed 1200 pixels wide can therefore use a much higher quality for the same number of bytes.',
                $this->bytes($budget), $fullQuality, $this->bytes(strlen($fullData)), $halfQuality, $this->bytes(strlen($halfData))));
    }

    private function responsiveWidths(GdImageCompressor $compressor): void
    {
        $scene = $this->photoScene(1920, 1280);
        $bars = [];

        foreach ([480, 960, 1440, 1920] as $width) {
            $file = $this->sourceFile($this->resized($scene, $width, intdiv($width * 2, 3)), 'png');
            $bars["{$width} px wide"] = $compressor->compress($file, new CompressionOptions('image/webp', 80))->size();
        }

        $canvas = $this->canvas('Why srcset matters: size by image width', 'The same photo as WebP at quality 80, at four widths a website might serve');
        $this->barChart($canvas, $bars);

        $this->save('responsive-widths-sizes', $canvas,
            'Bar chart of WebP file sizes for the same photo at 480, 960, 1440 and 1920 pixels wide',
            sprintf('WebP at quality 80: 480 px %s, 960 px %s, 1440 px %s, 1920 px %s. Serving a 480-pixel image to a small phone instead of the 1920-pixel version saves most of the bytes.',
                $this->bytes($bars['480 px wide']), $this->bytes($bars['960 px wide']), $this->bytes($bars['1440 px wide']), $this->bytes($bars['1920 px wide'])));
    }

    private function formatComparison(GdImageCompressor $compressor): void
    {
        $photo = $this->sourceFile($this->photoScene(1200, 800), 'png');
        $screenshot = $this->sourceFile($this->screenshotScene(1200, 800), 'png');
        $bars = [];

        foreach (['Photo' => $photo, 'Screenshot' => $screenshot] as $label => $file) {
            $bars["{$label}: JPG q80"] = $compressor->compress($file, new CompressionOptions('image/jpeg', 80))->size();
            $bars["{$label}: PNG lossless"] = $compressor->compress($file, new CompressionOptions('image/png', 100))->size();
            $bars["{$label}: WebP q80"] = $compressor->compress($file, new CompressionOptions('image/webp', 80))->size();
        }

        $canvas = $this->canvas('JPG vs PNG vs WebP: file size by image type', 'A 1200 × 800 photo and a 1200 × 800 interface screenshot saved in each format');
        $this->barChart($canvas, $bars);

        $this->save('format-size-comparison', $canvas,
            'Bar chart comparing JPG, lossless PNG and WebP file sizes for a photo and for a screenshot of the same dimensions',
            sprintf('Photo: JPG %s, PNG %s, WebP %s. Screenshot: JPG %s, PNG %s, WebP %s. In this test WebP at quality 80 was the smallest and lossless PNG the largest for both images; the strength of PNG for graphics is exact pixels and crisp text, not file size.',
                $this->bytes($bars['Photo: JPG q80']), $this->bytes($bars['Photo: PNG lossless']), $this->bytes($bars['Photo: WebP q80']),
                $this->bytes($bars['Screenshot: JPG q80']), $this->bytes($bars['Screenshot: PNG lossless']), $this->bytes($bars['Screenshot: WebP q80'])));
    }

    private function jpegBlocks(GdImageCompressor $compressor): void
    {
        $source = $this->sourceFile($this->photoScene(1600, 1067), 'png');
        $original = imagecreatefrompng($source);
        $low = $this->decode($compressor->compress($source, new CompressionOptions('image/jpeg', 10)));

        $canvas = $this->canvas('JPEG works in 8 × 8 pixel blocks', 'A 56 × 48 pixel area enlarged 10×: original (left) and JPG quality 10 (right)');

        foreach ([[40, $original, 'Original pixels'], [610, $low, 'JPG quality 10, 8 × 8 grid']] as [$x, $image, $label]) {
            imagecopyresized($canvas, $image, $x, 130, 1040, 520, 550, 500, 55, 50);
            $this->chip($canvas, $x + 10, 140, $label);
        }

        $grid = imagecolorallocatealpha($canvas, 255, 255, 255, 60);

        for ($i = 0; $i <= 55; $i += 8) {
            imageline($canvas, 610 + (int) round($i * 10), 130, 610 + (int) round($i * 10), 630, $grid);
        }

        for ($i = 0; $i <= 50; $i += 8) {
            imageline($canvas, 610, 130 + $i * 10, 1160, 130 + $i * 10, $grid);
        }

        $this->save('jpeg-8x8-blocks', $canvas,
            'Enlarged pixels of a photo next to the same area saved as a very low quality JPG, with a grid showing the 8 × 8 blocks JPEG compresses separately',
            'At very low quality each 8 × 8 block keeps only its smoothest frequencies, so blocks turn into flat patches and edges between them become visible. Encoded with libjpeg via PHP GD.');
    }

    private function jpgWebpAvif(GdImageCompressor $compressor): void
    {
        $scene = $this->photoScene(1600, 1067);
        $source = $this->sourceFile($scene, 'png');
        $jpg = $compressor->compress($source, new CompressionOptions('image/jpeg', 50));
        $budget = $jpg->size();

        [$webpQuality, $webp] = $this->highestQualityWithin(fn (int $q) => $compressor->compress($source, new CompressionOptions('image/webp', $q))->data, $budget);
        [$avifQuality, $avif] = $this->highestQualityWithin(fn (int $q) => $this->encodeWith(fn () => imageavif($scene, null, $q, 6)), $budget);

        $canvas = $this->canvas('JPG, WebP and AVIF at the same file size', 'Detail crops from a 1600 × 1067 photo, every file at or under '.$this->bytes($budget));
        $columns = [
            ['JPG q50 · '.$this->bytes($budget), $this->decode($jpg)],
            ["WebP q{$webpQuality} · ".$this->bytes(strlen($webp)), imagecreatefromstring($webp)],
            ["AVIF q{$avifQuality} · ".$this->bytes(strlen($avif)), imagecreatefromstring($avif)],
        ];

        foreach ($columns as $i => [$label, $image]) {
            $x = 40 + $i * 380;
            $this->placeCrop($canvas, $image, 1040, 500, 180, 250, $x, 130, 360, 500, true);
            $this->chip($canvas, $x + 10, 140, $label);
        }

        $this->save('jpg-webp-avif-equal-size', $canvas,
            'Three enlarged crops of the same photo saved as JPG, WebP and AVIF at almost the same file size',
            sprintf('JPG at quality 50 is %s. At the same size, the highest WebP quality is %d (%s) and the highest AVIF quality is %d (%s). Encoded with PHP GD (libjpeg, libwebp, libavif); quality scales differ between formats.',
                $this->bytes($budget), $webpQuality, $this->bytes(strlen($webp)), $avifQuality, $this->bytes(strlen($avif))));
    }

    private function targetFigure(string $key, CompressionResult $result, int $originalBytes, string $targetLabel, int $sourceWidth, int $sourceHeight, string $alt, string $subject, int $maxQuality = 80): void
    {
        $decoded = $this->decode($result);
        $canvas = $this->canvas("Compressed to {$targetLabel}: measured result", ucfirst($subject)." test image, {$sourceWidth} × {$sourceHeight} pixels, compressed with the target size tool");

        $this->placeContain($canvas, $decoded, 40, 130, 640, 500, true);

        $stats = [
            'Original' => $this->bytes($originalBytes).' (JPG, quality 95)',
            'Target' => $targetLabel,
            'Result' => $this->bytes($result->size()).($result->targetMet ? ' — target reached' : ' — target not reached'),
            'Dimensions' => $result->resized ? "{$result->width} × {$result->height} (resized)" : "{$result->width} × {$result->height} (unchanged)",
            'JPG quality' => "{$result->quality} (maximum {$maxQuality})",
            'Saved' => round(100 - $result->size() / $originalBytes * 100, 1).'%',
        ];

        $y = 160;

        foreach ($stats as $label => $value) {
            $this->text($canvas, 720, $y, 15, '#5b6b80', $label);
            $this->text($canvas, 720, $y + 28, 21, '#0f172a', $value, true);
            $y += 74;
        }

        $this->save($key, $canvas, $alt, sprintf(
            'A %d × %d %s (%s) compressed with a %s target: %s at %d × %d pixels, JPG quality %d%s. Measured with the PicsCompressor server compressor, which uses the same quality-then-resize approach as the in-browser tool.',
            $sourceWidth, $sourceHeight, $subject, $this->bytes($originalBytes), $targetLabel, $this->bytes($result->size()), $result->width, $result->height, $result->quality,
            $result->resized ? ', after the dimensions were reduced' : ', without resizing',
        ));
    }

    // ---------------------------------------------------------------------------------------------------------
    // Test scenes (deterministic)
    // ---------------------------------------------------------------------------------------------------------

    private function photoScene(int $width, int $height): GdImage
    {
        mt_srand(20260916);
        $s = $width / 1600;
        $image = imagecreatetruecolor($width, $height);
        $horizon = (int) ($height * 0.58);

        for ($y = 0; $y < $horizon; $y++) {
            $t = $y / $horizon;
            imageline($image, 0, $y, $width, $y, $this->rgb($image, 62 + 120 * $t, 124 + 96 * $t, 204 + 40 * $t));
        }

        for ($r = (int) (140 * $s); $r > 0; $r -= max(1, (int) (4 * $s))) {
            $glow = 1 - $r / (140 * $s);
            imagefilledellipse($image, (int) (1180 * $s), (int) (170 * $s), $r * 2, $r * 2, $this->rgb($image, 255, 230 + 25 * $glow, 170 + 80 * $glow, 90 - (int) (80 * $glow)));
        }

        for ($i = 0; $i < 45; $i++) {
            imagefilledellipse($image, mt_rand(0, $width), mt_rand((int) (40 * $s), (int) ($horizon * 0.5)), (int) (mt_rand(120, 320) * $s), (int) (mt_rand(40, 90) * $s), imagecolorallocatealpha($image, 255, 255, 255, mt_rand(70, 105)));
        }

        foreach ([[0.36, [104, 124, 160], 0.22], [0.47, [70, 92, 96], 0.14]] as [$base, $colour, $roughness]) {
            $points = [0, $height];
            $y = $height * $base;

            for ($x = 0; $x <= $width; $x += (int) max(8, 24 * $s)) {
                $y = max($height * 0.18, min($horizon, $y + mt_rand(-40, 40) * $s * $roughness * 3));
                array_push($points, $x, (int) $y);
            }

            array_push($points, $width, $height);
            imagefilledpolygon($image, $points, $this->rgb($image, ...$colour));
        }

        for ($y = $horizon; $y < $height; $y++) {
            $t = ($y - $horizon) / ($height - $horizon);
            imageline($image, 0, $y, $width, $y, $this->rgb($image, 78 + 30 * $t, 128 - 20 * $t, 62 - 10 * $t));
        }

        $lakeTop = (int) ($height * 0.66);
        imagefilledellipse($image, (int) ($width * 0.28), (int) ($height * 0.78), (int) ($width * 0.5), (int) ($height * 0.22), $this->rgb($image, 88, 140, 190));

        for ($i = 0; $i < 400 * $s; $i++) {
            $x = mt_rand((int) ($width * 0.06), (int) ($width * 0.5));
            $y = mt_rand($lakeTop + (int) (20 * $s), (int) ($height * 0.88));
            imageline($image, $x, $y, $x + (int) (mt_rand(10, 40) * $s), $y, $this->rgb($image, 150, 190, 225, 60));
        }

        for ($i = 0, $count = (int) (26000 * $s * $s); $i < $count; $i++) {
            $x = mt_rand((int) ($width * 0.5), $width);
            $y = mt_rand($horizon - (int) (30 * $s), $height);
            $size = (int) (mt_rand(4, 16) * $s);
            imagefilledellipse($image, $x, $y, $size, $size, $this->rgb($image, mt_rand(20, 70), mt_rand(70, 140), mt_rand(20, 60)));
        }

        $houseX = (int) (650 * $s);
        $houseY = (int) (600 * $s);
        imagefilledrectangle($image, $houseX, $houseY, $houseX + (int) (300 * $s), $houseY + (int) (190 * $s), $this->rgb($image, 236, 226, 205));
        imagefilledpolygon($image, [$houseX - (int) (20 * $s), $houseY, $houseX + (int) (150 * $s), $houseY - (int) (110 * $s), $houseX + (int) (320 * $s), $houseY], $this->rgb($image, 170, 60, 45));

        for ($row = 0; $row < 2; $row++) {
            for ($col = 0; $col < 4; $col++) {
                $wx = $houseX + (int) ((24 + $col * 70) * $s);
                $wy = $houseY + (int) ((24 + $row * 80) * $s);
                imagefilledrectangle($image, $wx, $wy, $wx + (int) (44 * $s), $wy + (int) (52 * $s), $this->rgb($image, 40, 60, 90));
                imagefilledrectangle($image, $wx + (int) (21 * $s), $wy, $wx + (int) (23 * $s), $wy + (int) (52 * $s), $this->rgb($image, 250, 250, 250));
            }
        }

        for ($x = (int) (560 * $s); $x < (int) (1100 * $s); $x += max(2, (int) (6 * $s))) {
            imagefilledrectangle($image, $x, (int) (800 * $s), $x + max(1, (int) (2 * $s)), (int) (860 * $s), $this->rgb($image, 245, 245, 240));
        }

        imagefilledrectangle($image, (int) (1040 * $s), (int) (470 * $s), (int) (1500 * $s), (int) (560 * $s), $this->rgb($image, 255, 255, 255));
        imagettftext($image, 30 * $s, 0, (int) (1062 * $s), (int) (528 * $s), $this->rgb($image, 20, 30, 50), $this->boldFont, 'PicsCompressor test photo');

        for ($i = 0, $count = (int) ($width * $height / 45); $i < $count; $i++) {
            $x = mt_rand(0, $width - 1);
            $y = mt_rand(0, $height - 1);
            $c = imagecolorat($image, $x, $y);
            $d = mt_rand(-22, 22);
            imagesetpixel($image, $x, $y, $this->rgb($image, (($c >> 16) & 255) + $d, (($c >> 8) & 255) + $d, ($c & 255) + $d));
        }

        return $image;
    }

    private function screenshotScene(int $width, int $height): GdImage
    {
        mt_srand(7);
        $image = imagecreatetruecolor($width, $height);
        imagefilledrectangle($image, 0, 0, $width, $height, $this->hex($image, '#f7f9fc'));
        imagefilledrectangle($image, 0, 0, 220, $height, $this->hex($image, '#1e293b'));
        $this->text($image, 28, 48, 20, '#ffffff', 'Dashboard', true);

        foreach (['Overview', 'Images', 'Reports', 'Settings', 'Team'] as $i => $item) {
            if ($i === 1) {
                imagefilledrectangle($image, 12, 84 + $i * 48, 208, 120 + $i * 48, $this->hex($image, '#2459e0'));
            }
            $this->text($image, 32, 108 + $i * 48, 15, '#e2e8f0', $item);
        }

        imagefilledrectangle($image, 220, 0, $width, 64, $this->hex($image, '#ffffff'));
        imageline($image, 220, 64, $width, 64, $this->hex($image, '#e2e8f0'));
        $this->text($image, 250, 40, 18, '#0f172a', 'Monthly image savings', true);
        imagefilledellipse($image, $width - 40, 32, 36, 36, $this->hex($image, '#f59e0b'));

        $card = 0;

        foreach (['Images compressed' => '12,480', 'Space saved' => '8.4 GB', 'Average reduction' => '71%'] as $label => $value) {
            $x = 250 + $card * 310;
            imagefilledrectangle($image, $x, 90, $x + 290, 190, $this->hex($image, '#ffffff'));
            imagerectangle($image, $x, 90, $x + 290, 190, $this->hex($image, '#e2e8f0'));
            $this->text($image, $x + 20, 125, 14, '#5b6b80', $label);
            $this->text($image, $x + 20, 170, 28, '#0f172a', $value, true);
            $card++;
        }

        $chartTop = 220;
        $chartBottom = 560;
        imagefilledrectangle($image, 250, $chartTop, $width - 30, $chartBottom, $this->hex($image, '#ffffff'));

        for ($x = 270; $x < $width - 50; $x++) {
            $curve = (int) (430 + 70 * sin($x / 80) + 30 * sin($x / 23));

            for ($y = $curve; $y < $chartBottom - 10; $y++) {
                $t = ($y - $curve) / max(1, $chartBottom - 10 - $curve);
                imagesetpixel($image, $x, $y, $this->rgb($image, 36 + 210 * $t, 89 + 160 * $t, 224 + 30 * $t));
            }
        }

        $this->text($image, 270, $chartTop + 34, 15, '#0f172a', 'Bytes saved per day', true);
        imagecopyresampled($image, $this->photoScene(420, 280), 250, 590, 0, 0, 300, 190, 420, 280);

        foreach (range(0, 5) as $row) {
            $y = 610 + $row * 30;
            $this->text($image, 580, $y, 14, '#334155', ['photo-2026-09-01.jpg', 'logo-final.png', 'banner-wide.webp', 'team-portrait.jpg', 'product-cutout.png', 'screenshot-app.png'][$row]);
            $this->text($image, 900, $y, 14, '#047857', '-'.mt_rand(40, 85).'%', true);
        }

        imagefilledrectangle($image, $width - 210, $height - 70, $width - 30, $height - 26, $this->hex($image, '#2459e0'));
        $this->text($image, $width - 190, $height - 41, 16, '#ffffff', 'Compress more', true);

        return $image;
    }

    private function signatureScene(int $width, int $height): GdImage
    {
        mt_srand(11);
        $image = imagecreatetruecolor($width, $height);

        for ($x = 0; $x < $width; $x++) {
            $shade = 246 - (int) (10 * $x / $width);
            imageline($image, $x, 0, $x, $height, $this->rgb($image, $shade, $shade - 1, $shade - 5));
        }

        imagesetthickness($image, 2);
        imageline($image, 120, 470, $width - 120, 470, $this->rgb($image, 150, 150, 150));
        $this->text($image, 120, 510, 22, '#6b7280', 'Signature of applicant');
        imagesetthickness($image, 6);
        $ink = $this->rgb($image, 22, 32, 92);
        $previous = null;

        for ($t = 0; $t <= 1; $t += 0.0008) {
            $x = 180 + $t * 1100;
            $y = 330 + 90 * sin($t * 23) * (1 - $t * 0.4) + 40 * cos($t * 57);

            if ($previous !== null && fmod($t, 0.23) > 0.012) {
                imageline($image, (int) $previous[0], (int) $previous[1], (int) $x, (int) $y, $ink);
            }

            $previous = [$x, $y];
        }

        imagesetthickness($image, 5);
        imagearc($image, 1260, 300, 180, 120, 200, 520, $ink);
        imagesetthickness($image, 1);

        for ($i = 0, $count = intdiv($width * $height, 12); $i < $count; $i++) {
            $x = mt_rand(0, $width - 1);
            $y = mt_rand(0, $height - 1);
            $c = imagecolorat($image, $x, $y);
            $d = mt_rand(-8, 8);
            imagesetpixel($image, $x, $y, $this->rgb($image, (($c >> 16) & 255) + $d, (($c >> 8) & 255) + $d, ($c & 255) + $d));
        }

        return $image;
    }

    private function documentScene(int $width, int $height): GdImage
    {
        mt_srand(5);
        $image = imagecreatetruecolor($width, $height);

        for ($y = 0; $y < $height; $y++) {
            $shade = 250 - (int) (14 * $y / $height);
            imageline($image, 0, $y, $width, $y, $this->rgb($image, $shade, $shade, $shade - 4));
        }

        $this->text($image, 110, 170, 40, '#111827', 'Application for Admission', true);
        $this->text($image, 110, 220, 20, '#374151', 'Section A — Personal details (please write clearly in block capitals)');

        foreach (['Full name', 'Date of birth', 'Address', 'Email address', 'Telephone'] as $i => $label) {
            $y = 290 + $i * 90;
            $this->text($image, 110, $y, 19, '#111827', $label);
            imagerectangle($image, 420, $y - 34, $width - 110, $y + 20, $this->rgb($image, 60, 60, 60));
        }

        $sentences = [
            'I confirm that the information provided in this form is complete and accurate to the best of my knowledge.',
            'Supporting documents must be uploaded as JPG files no larger than 100 KB each, unless stated otherwise.',
            'Photographs should show the applicant against a plain light background, facing the camera directly.',
            'Incomplete applications, or applications with unreadable documents, may be returned without review.',
        ];

        $y = 790;

        foreach (range(0, 17) as $line) {
            $this->text($image, 110, $y, 18, '#1f2937', $sentences[$line % 4]);
            $y += 36;
        }

        imagerectangle($image, 110, 1480, $width - 110, 1640, $this->rgb($image, 60, 60, 60));
        foreach ([1, 2, 3] as $col) {
            imageline($image, 110 + $col * 255, 1480, 110 + $col * 255, 1640, $this->rgb($image, 60, 60, 60));
        }
        imageline($image, 110, 1530, $width - 110, 1530, $this->rgb($image, 60, 60, 60));

        foreach (['Document', 'Format', 'Max size', 'Status'] as $i => $head) {
            $this->text($image, 130 + $i * 255, 1515, 18, '#111827', $head, true);
        }

        foreach (['Photograph', 'JPG', '100 KB', 'Attached'] as $i => $cell) {
            $this->text($image, 130 + $i * 255, 1590, 18, '#1f2937', $cell);
        }

        for ($i = 0, $count = intdiv($width * $height, 20); $i < $count; $i++) {
            $x = mt_rand(0, $width - 1);
            $y = mt_rand(0, $height - 1);
            $c = imagecolorat($image, $x, $y);
            $d = mt_rand(-10, 10);
            imagesetpixel($image, $x, $y, $this->rgb($image, (($c >> 16) & 255) + $d, (($c >> 8) & 255) + $d, ($c & 255) + $d));
        }

        return $image;
    }

    private function logoScene(int $width, int $height): GdImage
    {
        $image = $this->transparent($width, $height);
        $cx = intdiv($width, 2);
        $cy = intdiv($height, 2);

        imagefilledellipse($image, $cx, $cy, 560, 560, $this->hex($image, '#2459e0'));
        imagefilledellipse($image, $cx, $cy, 420, 420, $this->hex($image, '#ffffff'));
        imagefilledellipse($image, $cx, $cy, 300, 300, $this->hex($image, '#f59e0b'));
        $this->text($image, $cx - 92, $cy + 42, 110, '#0f172a', 'CP', true);

        return $image;
    }

    private function productScene(int $width, int $height): GdImage
    {
        $image = $this->transparent($width, $height);
        $cx = intdiv($width, 2);

        for ($i = 60; $i > 0; $i--) {
            imagefilledellipse($image, $cx + 40, 700, 300 + $i * 6, 50 + $i, imagecolorallocatealpha($image, 0, 0, 0, 127 - (int) (4 * (60 - $i) / 60)));
        }

        for ($x = -140; $x <= 140; $x++) {
            $light = 0.55 + 0.45 * cos(($x + 40) / 140 * M_PI / 1.6);
            imageline($image, $cx + $x, 230, $cx + $x, 680, $this->rgb($image, 30 + 60 * $light, 120 + 100 * $light, 90 + 80 * $light));
        }

        imagefilledellipse($image, $cx, 680, 280, 40, $this->rgb($image, 40, 130, 100));
        imagefilledrectangle($image, $cx - 60, 120, $cx + 60, 230, $this->rgb($image, 230, 230, 235));
        imagefilledrectangle($image, $cx - 70, 90, $cx + 70, 130, $this->rgb($image, 30, 41, 59));
        imagefilledrectangle($image, $cx - 140, 380, $cx + 140, 520, $this->rgb($image, 255, 255, 255));
        $this->text($image, $cx - 110, 440, 34, '#0f172a', 'NATURAL', true);
        $this->text($image, $cx - 110, 490, 22, '#334155', 'Mineral water');

        return $image;
    }

    // ---------------------------------------------------------------------------------------------------------
    // Drawing helpers
    // ---------------------------------------------------------------------------------------------------------

    private function canvas(string $title, string $subtitle): GdImage
    {
        $canvas = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagefilledrectangle($canvas, 0, 0, self::WIDTH, self::HEIGHT, $this->hex($canvas, '#ffffff'));
        $this->text($canvas, 40, 62, 28, '#0f172a', $title, true);
        $this->text($canvas, 40, 98, 16, '#5b6b80', $subtitle);
        $this->text($canvas, self::WIDTH - 250, self::HEIGHT - 16, 12, '#94a3b8', config('site.brand').' · measured example');

        return $canvas;
    }

    private function lineChart(GdImage $canvas, int $left, int $top, int $width, int $height, array $levels, array $series): void
    {
        $max = max(array_map('max', $series)) * 1.08;
        $bottom = $top + $height;
        $axis = $this->hex($canvas, '#cbd5e1');
        imageline($canvas, $left, $bottom, $left + $width, $bottom, $axis);
        imageline($canvas, $left, $top, $left, $bottom, $axis);

        foreach ([0.25, 0.5, 0.75, 1] as $fraction) {
            $y = (int) ($bottom - $height * $fraction);
            imageline($canvas, $left, $y, $left + $width, $y, $this->hex($canvas, '#eef1f5'));
            $this->text($canvas, $left + 6, $y - 6, 12, '#5b6b80', $this->bytes((int) ($max * $fraction)));
        }

        $colours = ['#2459e0', '#f59e0b'];
        $step = $width / (count($levels) - 1);

        foreach (array_values($series) as $index => $values) {
            $colour = $this->hex($canvas, $colours[$index]);
            imagesetthickness($canvas, 4);
            $previous = null;

            foreach (array_values($levels) as $i => $level) {
                $point = [(int) ($left + $i * $step), (int) ($bottom - $values[$level] / $max * $height)];

                if ($previous) {
                    imageline($canvas, $previous[0], $previous[1], $point[0], $point[1], $colour);
                }

                imagefilledellipse($canvas, $point[0], $point[1], 11, 11, $colour);
                $previous = $point;
            }

            imagesetthickness($canvas, 1);
            imagefilledrectangle($canvas, $left + $width - 250 + $index * 130, $top - 18, $left + $width - 230 + $index * 130, $top - 6, $colour);
            $this->text($canvas, $left + $width - 222 + $index * 130, $top - 5, 15, '#0f172a', array_keys($series)[$index], true);
        }

        foreach (array_values($levels) as $i => $level) {
            $this->text($canvas, (int) ($left + $i * $step) - 10, $bottom + 26, 13, '#5b6b80', (string) $level);
        }

        $this->text($canvas, $left + intdiv($width, 2) - 60, $bottom + 52, 14, '#334155', 'Quality setting', true);
    }

    /**
     * @param  array<string, int>  $bars
     */
    private function barChart(GdImage $canvas, array $bars): void
    {
        $max = max($bars);
        $rowHeight = (int) min(80, 470 / count($bars));
        $y = 150;

        foreach ($bars as $label => $bytes) {
            $length = (int) (620 * $bytes / $max);
            $this->text($canvas, 40, $y + (int) ($rowHeight / 2) + 5, 16, '#0f172a', $label, true);
            imagefilledrectangle($canvas, 340, $y + 8, 340 + max(4, $length), $y + $rowHeight - 12, $this->hex($canvas, str_contains($label, 'original') || str_contains($label, 'PNG') ? '#94a3b8' : '#2459e0'));
            $this->text($canvas, 352 + $length, $y + (int) ($rowHeight / 2) + 5, 16, '#334155', $this->bytes($bytes), true);
            $y += $rowHeight;
        }
    }

    private function placeCrop(GdImage $canvas, GdImage $source, int $sourceX, int $sourceY, int $cropWidth, int $cropHeight, int $x, int $y, int $width, int $height, bool $nearest): void
    {
        $sourceX = min($sourceX, max(0, imagesx($source) - $cropWidth));
        $sourceY = min($sourceY, max(0, imagesy($source) - $cropHeight));
        $copy = $nearest ? 'imagecopyresized' : 'imagecopyresampled';
        $copy($canvas, $source, $x, $y, $sourceX, $sourceY, $width, $height, $cropWidth, $cropHeight);
        imagerectangle($canvas, $x, $y, $x + $width - 1, $y + $height - 1, $this->hex($canvas, '#e2e8f0'));
    }

    private function placeContain(GdImage $canvas, GdImage $source, int $x, int $y, int $width, int $height, bool $border = false): void
    {
        imagealphablending($canvas, true);
        $scale = min($width / imagesx($source), $height / imagesy($source));
        $w = (int) (imagesx($source) * $scale);
        $h = (int) (imagesy($source) * $scale);
        $dx = $x + intdiv($width - $w, 2);
        $dy = $y + intdiv($height - $h, 2);
        imagecopyresampled($canvas, $source, $dx, $dy, 0, 0, $w, $h, imagesx($source), imagesy($source));

        if ($border) {
            imagerectangle($canvas, $dx, $dy, $dx + $w - 1, $dy + $h - 1, $this->hex($canvas, '#e2e8f0'));
        }
    }

    private function checkerboard(GdImage $canvas, int $x, int $y, int $width, int $height): void
    {
        for ($row = 0; $row * 20 < $height; $row++) {
            for ($col = 0; $col * 20 < $width; $col++) {
                imagefilledrectangle($canvas, $x + $col * 20, $y + $row * 20, min($x + $width, $x + ($col + 1) * 20) - 1, min($y + $height, $y + ($row + 1) * 20) - 1,
                    $this->hex($canvas, ($row + $col) % 2 ? '#e5e7eb' : '#ffffff'));
            }
        }
    }

    private function chip(GdImage $canvas, int $x, int $y, string $label): void
    {
        $box = imagettfbbox(15, 0, $this->boldFont, $label);
        imagefilledrectangle($canvas, $x, $y, $x + ($box[2] - $box[0]) + 20, $y + 32, $this->rgb($canvas, 15, 23, 42, 20));
        $this->text($canvas, $x + 10, $y + 23, 15, '#ffffff', $label, true);
    }

    private function text(GdImage $image, int $x, int $y, float $size, string $colour, string $text, bool $bold = false): void
    {
        imagettftext($image, $size, 0, $x, $y, $this->hex($image, $colour), $bold ? $this->boldFont : $this->font, $text);
    }

    private function transparent(int $width, int $height): GdImage
    {
        $image = imagecreatetruecolor($width, $height);
        imagesavealpha($image, true);
        imagealphablending($image, false);
        imagefilledrectangle($image, 0, 0, $width, $height, imagecolorallocatealpha($image, 0, 0, 0, 127));
        imagealphablending($image, true);

        return $image;
    }

    private function resized(GdImage $source, int $width, int $height): GdImage
    {
        $image = imagecreatetruecolor($width, $height);
        imagecopyresampled($image, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));

        return $image;
    }

    private function rgb(GdImage $image, float $r, float $g, float $b, int $alpha = 0): int
    {
        $clamp = fn (float $v) => (int) max(0, min(255, round($v)));

        return imagecolorallocatealpha($image, $clamp($r), $clamp($g), $clamp($b), $alpha);
    }

    private function hex(GdImage $image, string $hex): int
    {
        return $this->rgb($image, hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2)));
    }

    // ---------------------------------------------------------------------------------------------------------
    // Encoding helpers
    // ---------------------------------------------------------------------------------------------------------

    private function sourceFile(GdImage $image, string $format, int $quality = 95): string
    {
        $path = tempnam(sys_get_temp_dir(), 'cpx');
        $this->tempFiles[] = $path;

        match ($format) {
            'png' => imagepng($image, $path, 6),
            'jpg' => imagejpeg($image, $path, $quality),
            'webp' => imagewebp($image, $path, $quality),
        };

        return $path;
    }

    private function decode(CompressionResult $result): GdImage
    {
        return imagecreatefromstring($result->data);
    }

    private function encodeWith(callable $encoder): string
    {
        ob_start();
        $encoder();

        return (string) ob_get_clean();
    }

    /**
     * Binary-searches the highest quality whose encoded output is at or under the byte budget.
     *
     * @param  callable(int): string  $encode
     * @return array{0: int, 1: string}
     */
    private function highestQualityWithin(callable $encode, int $budget): array
    {
        $best = [10, $encode(10)];
        $low = 11;
        $high = 100;

        while ($low <= $high) {
            $mid = intdiv($low + $high, 2);
            $data = $encode($mid);

            if (strlen($data) <= $budget) {
                $best = [$mid, $data];
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        }

        return $best;
    }

    private function save(string $key, GdImage $canvas, string $alt, string $caption): void
    {
        $directory = public_path('images/examples');
        imagewebp($canvas, "{$directory}/{$key}.webp", 90);
        imagewebp($this->resized($canvas, 640, 360), "{$directory}/{$key}-640.webp", 88);

        $this->figures[$key] = [
            'src' => "images/examples/{$key}.webp",
            'width' => self::WIDTH,
            'height' => self::HEIGHT,
            'alt' => $alt,
            'caption' => $caption,
            'sources' => [
                ['src' => "images/examples/{$key}-640.webp", 'width' => 640],
                ['src' => "images/examples/{$key}.webp", 'width' => self::WIDTH],
            ],
        ];
    }

    private function bytes(int $bytes): string
    {
        return $bytes >= 1024 * 1024
            ? number_format($bytes / 1048576, 2).' MB'
            : number_format($bytes / 1024, $bytes < 102400 ? 1 : 0).' KB';
    }

    private function logo(): void
    {
        $scale = 4;
        $size = 512 * $scale;
        $image = $this->transparent($size, $size);
        $unit = $size / 32;
        $blue = $this->hex($image, '#2459e0');
        $white = $this->hex($image, '#ffffff');
        $radius = (int) (8 * $unit);

        imagefilledrectangle($image, $radius, 0, $size - $radius, $size, $blue);
        imagefilledrectangle($image, 0, $radius, $size, $size - $radius, $blue);

        foreach ([[$radius, $radius], [$size - $radius, $radius], [$radius, $size - $radius], [$size - $radius, $size - $radius]] as [$cx, $cy]) {
            imagefilledellipse($image, (int) $cx, (int) $cy, $radius * 2, $radius * 2, $blue);
        }

        $stroke = (int) (2.25 * $unit);
        foreach ([[[12, 7], [12, 12], [7, 12]], [[20, 7], [20, 12], [25, 12]], [[7, 20], [12, 20], [12, 25]], [[25, 20], [20, 20], [20, 25]]] as $points) {
            for ($i = 0; $i < 2; $i++) {
                [$x1, $y1] = $points[$i];
                [$x2, $y2] = $points[$i + 1];
                $minX = (int) (min($x1, $x2) * $unit - $stroke / 2);
                $maxX = (int) (max($x1, $x2) * $unit + $stroke / 2);
                $minY = (int) (min($y1, $y2) * $unit - $stroke / 2);
                $maxY = (int) (max($y1, $y2) * $unit + $stroke / 2);
                imagefilledrectangle($image, $minX, $minY, $maxX, $maxY, $white);
            }

            foreach ([$points[0], $points[2]] as [$px, $py]) {
                imagefilledellipse($image, (int) ($px * $unit), (int) ($py * $unit), $stroke, $stroke, $white);
            }
        }

        imagefilledrectangle($image, (int) (13.5 * $unit), (int) (13.5 * $unit), (int) (18.5 * $unit), (int) (18.5 * $unit), $white);

        $output = $this->transparent(512, 512);
        imagealphablending($output, false);
        imagecopyresampled($output, $image, 0, 0, 0, 0, 512, 512, $size, $size);
        imagesavealpha($output, true);
        imagepng($output, public_path(config('site.assets.logo_png')), 9);
    }
}
