<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompressImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $config = config('compressor');

        return [
            'image' => [
                'required',
                'file',
                'max:'.($config['max_upload_mb'] * 1024),
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:'.implode(',', array_keys($config['formats'])),
            ],
            'output' => ['required', Rule::in(['image/png', 'image/jpeg', 'image/webp'])],
            'quality' => ['required', 'integer', "between:{$config['min_quality']},{$config['max_quality']}"],
            'target_kb' => ['nullable', 'integer', 'min:5', "max:{$config['max_custom_target_kb']}"],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'image.required' => 'Please choose an image to compress.',
            'image.file' => "We couldn't process this image. Please try another file.",
            'image.uploaded' => 'Your image exceeds the maximum allowed file size.',
            'image.max' => 'Your image exceeds the maximum allowed file size.',
            'image.mimes' => 'Please upload a JPG, PNG, or WebP image.',
            'image.mimetypes' => 'Please upload a JPG, PNG, or WebP image.',
            'output.*' => 'Please choose a supported output format.',
            'quality.*' => 'Quality must be between 10 and 100.',
            'target_kb.*' => 'Please enter a valid target size.',
        ];
    }
}
