<?php

namespace App\Services\ImageCompression;

final readonly class CompressionOptions
{
    public function __construct(
        public string $outputMime,
        public int $quality,
        public ?int $targetBytes = null,
    ) {}
}
