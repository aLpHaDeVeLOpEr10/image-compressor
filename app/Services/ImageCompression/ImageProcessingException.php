<?php

namespace App\Services\ImageCompression;

use RuntimeException;

class ImageProcessingException extends RuntimeException
{
    public static function invalidImage(): self
    {
        return new self("We couldn't process this image. Please try another file.");
    }

    public static function tooManyPixels(): self
    {
        return new self('This image has very large dimensions and cannot be processed. Please resize it and try again.');
    }
}
