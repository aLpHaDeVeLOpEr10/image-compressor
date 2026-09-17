<?php

$maxMb = config('compressor.max_upload_mb');

return [
    [
        'id' => 'basics',
        'title' => 'How image compression works',
        'items' => [
            [
                'question' => 'What does compressing an image actually do?',
                'answer' => '<p>Compression re-encodes the image so it needs fewer bytes. Lossy formats such as JPG and lossy WebP discard fine detail that is hard to see. Lossless formats such as PNG store every pixel exactly but look for more efficient ways to pack the data. This tool can also reduce the number of colours in a PNG, which is lossy.</p>',
            ],
            [
                'question' => 'What is the difference between lossy and lossless compression?',
                'answer' => '<p>Lossless compression keeps every pixel identical to the original, so savings are limited. Lossy compression permanently removes some information in exchange for much smaller files. Most photos on the web use lossy compression because the change is usually hard to notice.</p>',
            ],
            [
                'question' => 'What does the quality slider control?',
                'answer' => '<p>For JPG and WebP output, it sets the encoder quality from 10% to 100%. Lower values produce smaller files with more visible artifacts. For PNG output, lower quality reduces the colour palette, from up to 256 colours down to 16. At 100% the PNG is saved losslessly.</p>',
            ],
            [
                'question' => 'Can compression make an image larger?',
                'answer' => '<p>Yes, occasionally. If the original was already saved at a low quality, re-encoding it at a higher quality, or in a less suitable format, can increase its size. The result screen shows the real sizes and warns you when this happens, so you can keep your original.</p>',
            ],
        ],
    ],
    [
        'id' => 'formats',
        'title' => 'JPG, PNG and WebP',
        'items' => [
            [
                'question' => 'How does JPG compression work here?',
                'answer' => '<p>Your browser decodes the image and re-encodes it with its built-in JPEG encoder at the quality you choose. Transparent areas, if any, are filled with white because JPG does not support transparency. Learn more on the <a href="'.route('home').'#jpg">JPG compressor</a> page.</p>',
            ],
            [
                'question' => 'How does PNG compression work here?',
                'answer' => '<p>Below 100% quality, your browser reduces the image to an indexed palette of up to 256 colours and encodes a compact PNG, keeping full and partial transparency. At 100% it is saved losslessly. Older browsers fall back to our server, where transparent images are kept lossless. See the <a href="'.route('home').'#png">PNG compressor</a> for details.</p>',
            ],
            [
                'question' => 'How does WebP compression work here?',
                'answer' => '<p>Browsers that support WebP encoding, such as current versions of Chrome, Edge and Firefox, compress the image locally using lossy WebP with transparency preserved. If your browser cannot create WebP files, the image is compressed on our server instead. Try the <a href="'.route('home').'#webp">WebP compressor</a>.</p>',
            ],
            [
                'question' => 'Which format gives the smallest file?',
                'answer' => '<p>For photographs, WebP is often smallest, followed closely by JPG. For screenshots, logos and flat graphics, a palette PNG or WebP is usually best. Because results depend on the image, the easiest approach is to try two formats and compare the sizes shown.</p>',
            ],
            [
                'question' => 'Can I convert between formats?',
                'answer' => '<p>Yes. Under <strong>Output format</strong> choose JPG or WebP to convert any supported image. Choose “Same” to keep the original format, including PNG.</p>',
            ],
        ],
    ],
    [
        'id' => 'quality-and-size',
        'title' => 'Quality loss and target file sizes',
        'items' => [
            [
                'question' => 'Will I see a loss in quality?',
                'answer' => '<p>At moderate settings (around 70–85% for photos), most people cannot see a difference at normal viewing size. At low settings you may notice blockiness, blurring, colour banding or halos around edges. Use the before-and-after comparison to check before downloading.</p>',
            ],
            [
                'question' => 'How does the target size option work?',
                'answer' => '<p>The tool first tries your chosen quality. If the file is too large, it searches for the highest quality that fits the target. If even the lowest quality is too large, it reduces the image dimensions step by step until it fits or reaches a practical minimum.</p>',
            ],
            [
                'question' => 'Why isn’t the result exactly the target size?',
                'answer' => '<p>Encoders cannot produce an exact byte count. The tool aims for the best result at or below the target, so the file is usually slightly smaller. That is why sizes are described as approximate.</p>',
            ],
            [
                'question' => 'What if the target cannot be reached?',
                'answer' => '<p>The tool returns the smallest result it could produce and tells you that the target was not reached. You can then try a different output format, or crop the image before compressing.</p>',
            ],
            [
                'question' => 'Does the tool change my image dimensions?',
                'answer' => '<p>Only when you set a target size that cannot be reached by lowering quality alone. If that happens, the result shows both the original and new dimensions. Without a target, dimensions are never changed.</p>',
            ],
        ],
    ],
    [
        'id' => 'privacy',
        'title' => 'Privacy and security',
        'items' => [
            [
                'question' => 'Are my images uploaded or stored?',
                'answer' => '<p>In modern browsers, compression to JPG, WebP and PNG runs on your device, so the image is not uploaded. If your browser cannot encode WebP or PNG, the image is sent to our server, processed in memory and returned immediately. Images are never saved to our storage or database. See our <a href="'.route('pages.privacy-policy').'">privacy policy</a>.</p>',
            ],
            [
                'question' => 'Is metadata such as location removed?',
                'answer' => '<p>Yes. Because the image is re-encoded, embedded metadata such as EXIF camera information and GPS coordinates is not included in the compressed file.</p>',
            ],
            [
                'question' => 'Do I need to create an account?',
                'answer' => '<p>No. There is no registration or login.</p>',
            ],
        ],
    ],
    [
        'id' => 'limits-and-devices',
        'title' => 'Upload limits, devices and downloads',
        'items' => [
            [
                'question' => 'What is the maximum upload size?',
                'answer' => '<p>Each image can be up to '.$maxMb.' MB. Extremely large pixel dimensions may also be limited by your device memory, or by server limits when the server fallback is used.</p>',
            ],
            [
                'question' => 'Can I compress several images at once?',
                'answer' => '<p>Not at the moment. The tool compresses one image at a time. Select <strong>Compress Another Image</strong> to start the next one without reloading the page.</p>',
            ],
            [
                'question' => 'Does the compressor work on mobile phones?',
                'answer' => '<p>Yes. It works in current mobile browsers on Android and iOS. You can choose photos from your gallery or camera roll. Very high-resolution images may be slower on older devices.</p>',
            ],
            [
                'question' => 'How do I download the compressed image?',
                'answer' => '<p>After compression, select <strong>Download Compressed Image</strong>. The file is saved with “-compressed” added to the original name, for example <code>photo-compressed.jpg</code>, so your original is never overwritten.</p>',
            ],
            [
                'question' => 'Where do downloaded files go on my phone?',
                'answer' => '<p>On Android, downloads usually appear in the Downloads folder or your browser’s download list. On iPhone, Safari saves files to the Downloads folder in the Files app. From there you can share the file or save it to Photos.</p>',
            ],
        ],
    ],
];
