<?php

namespace App\Services\ImageCompression;

final readonly class CompressionResult
{
    public function __construct(
        public string $data,
        public string $mime,
        public int $width,
        public int $height,
        public int $quality,
        public bool $resized = false,
        public ?bool $targetMet = null,
    ) {}

    public function size(): int
    {
        return strlen($this->data);
    }
}
