<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CompressImageTest extends TestCase
{
    private function image(string $name, string $format, bool $transparent = false, int $width = 400, int $height = 300): UploadedFile
    {
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 255, 255, 255, $transparent ? 127 : 0));

        mt_srand(3);
        for ($i = 0; $i < 300; $i++) {
            imagefilledellipse($image, mt_rand(20, $width), mt_rand(20, $height), mt_rand(5, 80), mt_rand(5, 80),
                imagecolorallocatealpha($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255), $transparent ? 30 : 0));
        }

        $path = tempnam(sys_get_temp_dir(), 'img');
        match ($format) {
            'jpeg' => imagejpeg($image, $path, 95),
            'png' => imagepng($image, $path),
            'webp' => imagewebp($image, $path, 95),
        };

        return new UploadedFile($path, $name, "image/{$format}", null, true);
    }

    private function compress(array $data): TestResponse
    {
        return $this->withHeader('Accept', 'application/json')->post('/process/compress', $data);
    }

    public function test_png_output_is_a_valid_png(): void
    {
        $response = $this->compress(['image' => $this->image('photo.jpg', 'jpeg'), 'output' => 'image/png', 'quality' => 60]);

        $response->assertOk()->assertHeader('Content-Type', 'image/png')->assertHeader('X-Image-Width', '400');
        $this->assertSame(IMAGETYPE_PNG, getimagesizefromstring($response->getContent())[2]);
    }

    public function test_webp_and_jpeg_outputs(): void
    {
        foreach (['image/webp' => IMAGETYPE_WEBP, 'image/jpeg' => IMAGETYPE_JPEG] as $mime => $type) {
            $response = $this->compress(['image' => $this->image('pic.png', 'png'), 'output' => $mime, 'quality' => 70]);

            $response->assertOk()->assertHeader('Content-Type', $mime);
            $this->assertSame($type, getimagesizefromstring($response->getContent())[2]);
        }
    }

    public function test_target_size_is_met_by_lowering_quality_or_dimensions(): void
    {
        $response = $this->compress([
            'image' => $this->image('big.jpg', 'jpeg', width: 1600, height: 1200),
            'output' => 'image/jpeg',
            'quality' => 90,
            'target_kb' => 20,
        ]);

        $response->assertOk()->assertHeader('X-Target-Met', '1');
        $this->assertLessThanOrEqual(20 * 1024, strlen($response->getContent()));
    }

    public function test_transparent_png_keeps_alpha_on_server(): void
    {
        $response = $this->compress(['image' => $this->image('logo.png', 'png', transparent: true), 'output' => 'image/png', 'quality' => 50]);

        $response->assertOk();
        $result = imagecreatefromstring($response->getContent());
        $this->assertSame(127, (imagecolorat($result, 0, 0) >> 24) & 0x7F);
    }

    public function test_rejects_non_image_files_even_with_image_extension(): void
    {
        $php = UploadedFile::fake()->createWithContent('shell.jpg', '<?php echo "owned"; ?>');

        $this->compress(['image' => $php, 'output' => 'image/png', 'quality' => 80])
            ->assertStatus(422)
            ->assertJsonMissingPath('exception');

        $html = UploadedFile::fake()->createWithContent('image.png', '<html><script>alert(1)</script></html>');

        $this->compress(['image' => $html, 'output' => 'image/png', 'quality' => 80])->assertStatus(422);
    }

    public function test_rejects_oversized_uploads(): void
    {
        config(['compressor.max_upload_mb' => 1]);

        $this->compress(['image' => UploadedFile::fake()->create('big.jpg', 2048, 'image/jpeg'), 'output' => 'image/png', 'quality' => 80])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Your image exceeds the maximum allowed file size.');
    }

    public function test_rejects_images_with_too_many_pixels_without_leaking_details(): void
    {
        config(['compressor.server_max_pixels' => 1000]);

        $this->compress(['image' => $this->image('a.png', 'png'), 'output' => 'image/png', 'quality' => 80])
            ->assertStatus(422)
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('file');
    }

    public function test_rejects_invalid_parameters(): void
    {
        $this->compress(['image' => $this->image('a.jpg', 'jpeg'), 'output' => 'text/html', 'quality' => 80])->assertStatus(422);
        $this->compress(['image' => $this->image('a.jpg', 'jpeg'), 'output' => 'image/png', 'quality' => 5])->assertStatus(422);
        $this->compress(['image' => $this->image('a.jpg', 'jpeg'), 'output' => 'image/png', 'quality' => 80, 'target_kb' => -1])->assertStatus(422);
        $this->compress(['output' => 'image/png', 'quality' => 80])->assertStatus(422);
    }

    public function test_endpoint_is_rate_limited(): void
    {
        config(['compressor.rate_limit_per_minute' => 3]);

        for ($i = 0; $i < 3; $i++) {
            $this->compress(['output' => 'image/png', 'quality' => 80])->assertStatus(422);
        }

        $this->compress(['output' => 'image/png', 'quality' => 80])->assertStatus(429);
    }

    public function test_get_requests_are_not_allowed(): void
    {
        $this->get('/process/compress')->assertStatus(405);
    }

    public function test_nothing_is_written_to_storage(): void
    {
        $count = fn () => iterator_count(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(storage_path('app'), \FilesystemIterator::SKIP_DOTS)));
        $before = $count();

        $this->compress(['image' => $this->image('a.jpg', 'jpeg'), 'output' => 'image/png', 'quality' => 80])->assertOk();

        $this->assertSame($before, $count());
    }
}
