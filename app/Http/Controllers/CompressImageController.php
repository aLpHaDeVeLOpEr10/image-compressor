<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompressImageRequest;
use App\Services\ImageCompression\CompressionOptions;
use App\Services\ImageCompression\GdImageCompressor;
use App\Services\ImageCompression\ImageProcessingException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class CompressImageController extends Controller
{
    public function __invoke(CompressImageRequest $request, GdImageCompressor $compressor): Response|JsonResponse
    {
        $upload = $request->file('image');

        try {
            $result = $compressor->compress($upload->getRealPath(), new CompressionOptions(
                outputMime: $request->string('output')->toString(),
                quality: $request->integer('quality'),
                targetBytes: $request->filled('target_kb') ? $request->integer('target_kb') * 1024 : null,
            ));
        } catch (ImageProcessingException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Something went wrong while compressing your image. Please try again.'], 500);
        }

        return response($result->data, 200, [
            'Content-Type' => $result->mime,
            'Content-Length' => (string) $result->size(),
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition' => 'inline',
            'X-Image-Width' => (string) $result->width,
            'X-Image-Height' => (string) $result->height,
            'X-Image-Quality' => (string) $result->quality,
            'X-Image-Resized' => $result->resized ? '1' : '0',
            'X-Target-Met' => $result->targetMet === null ? '' : ($result->targetMet ? '1' : '0'),
        ]);
    }
}
