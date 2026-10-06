<?php

/*
| Default content for the PNG to WebP Converter tool. Every key can be edited in the admin; saved values win.
| Numbered keys (feature_1, how_to_step_1_title, benefit_1_title, faq_1_question, ...) form lists: add the next number
| to add an item, or clear the first part of an item to hide it.
*/

return [
    // Hero.
    'h1' => 'Convert PNG to WebP Online',
    'intro' => ['type' => 'textarea', 'value' => 'Convert transparent PNG images to WebP and keep the transparent background. Ideal for product cut-outs and illustrations with soft shadows.'],

    // Summary box and its example figure (also the page's social share image unless one is uploaded).
    'summary' => ['type' => 'textarea', 'value' => 'Upload your PNG, keep the output format set to WebP, choose a quality around 80% and check the edges in the comparison slider, then download the .webp file. Transparency is kept, but the output is lossy WebP, so fine edges and small text can soften slightly.'],
    'figure_alt' => ['type' => 'textarea', 'value' => 'A product cut-out PNG with a soft transparent shadow next to the WebP version, which keeps the same transparency'],
    'figure_caption' => ['type' => 'textarea', 'value' => 'The PNG with transparency is 15.3 KB; the WebP at quality 80 is 8.7 KB and keeps the transparent background and soft shadow. Measured with the PicsCompressor server encoder.'],

    // Tool card text used in "All tools" grids.
    'card_description' => ['type' => 'textarea', 'value' => 'Convert PNG to WebP and keep transparent backgrounds.'],

    // Structured data feature list.
    'feature_1' => 'Converts PNG images up to {max_upload_mb} MB to lossy WebP',
    'feature_2' => 'Keeps transparency (alpha channel)',
    'feature_3' => 'Encodes in your browser where WebP encoding is supported',
    'feature_4' => 'Quality slider from 10% to 100%, or an approximate target file size',
    'feature_5' => 'No account or sign-up required',

    // Introduction section.
    'why_heading' => 'Transparency without the PNG file size',
    'why_body' => ['type' => 'html', 'value' => <<<'HTML'
<p>PNG is the usual choice for images with transparent backgrounds: product photos cut out from their background, illustrations with soft drop shadows, stickers and hero graphics. Because PNG is lossless, those images are often very large, especially when they contain photographic detail or smooth gradients.</p>
<p>Converting to JPG would make them smaller but fill the background with white. Palette-based PNG compression keeps transparency, but soft shadows and gradients can show banding when the colours are reduced. Lossy WebP sits between the two: it keeps the alpha channel and compresses photo-like content efficiently.</p>
<p>This page is set up to output WebP. Upload a PNG, choose a quality, and download a <code>.webp</code> file with its transparency intact. For JPG photos, use the <a href="{url:tools.jpg-to-webp}">JPG to WebP converter</a>.</p>
HTML],

    // How-to steps.
    'how_to_heading' => 'How to convert PNG to WebP',
    'how_to_step_1_title' => 'Add your PNG',
    'how_to_step_1_text' => ['type' => 'textarea', 'value' => 'Drag and drop, choose a file or paste an image. WebP output is already selected.'],
    'how_to_step_2_title' => 'Choose a quality',
    'how_to_step_2_text' => ['type' => 'textarea', 'value' => 'Start around 80% and raise it if edges or small text look soft.'],
    'how_to_step_3_title' => 'Check edges, then download',
    'how_to_step_3_text' => ['type' => 'textarea', 'value' => 'Zoom in on outlines and shadows in the comparison slider, then download the .webp file.'],

    // Detailed guidance section.
    'details_heading' => 'Checking quality when converting PNG to WebP',
    'details_body' => ['type' => 'html', 'value' => <<<'HTML'
<h3>Good candidates for WebP</h3>
<ul>
    <li><strong>Product cut-outs</strong> for online stores, where the background must stay transparent.</li>
    <li><strong>Illustrations with soft shadows or gradients</strong> that band when reduced to a small palette.</li>
    <li><strong>Photos saved as PNG</strong> that also need transparency, such as portraits with a removed background.</li>
</ul>

<h3>Check transparent edges</h3>
<p>The output is lossy WebP, and the transparency itself is compressed along with the colours. Around the outline of a cut-out, hair, fur or a soft shadow, lower quality settings can produce slightly rough or blurred edges. Zoom in on those areas in the comparison slider. If you see halos or jagged outlines, raise the quality until they disappear.</p>

<h3>UI graphics, icons and text</h3>
<p>Screenshots, interface elements and images containing small text rely on sharp, pixel-exact edges. Lossy WebP can soften them. Check lettering and thin lines carefully at full size, not just in a small preview, and use a high quality if you convert these at all. If they still look soft, keep them as PNG.</p>

<h3>When a palette PNG is better</h3>
<p>Simple logos, icons and flat illustrations with only a handful of colours often compress very well as an indexed palette PNG, while staying perfectly crisp. Try the <a href="{url:home}#png">PNG compressor</a> on the same file and compare the result with the WebP. If transparency does not matter at all, the <a href="{url:tools.png-to-jpg}">PNG to JPG converter</a> gives maximum compatibility.</p>

<h3>Browser support and compatibility</h3>
<p>All current major browsers display WebP, including transparent WebP, so it works well for websites and stores. Some older software, upload forms and print services still only accept JPG or PNG, so keep your original PNG as the master copy.</p>

<h3>Where conversion happens</h3>
<p>Current Chrome, Edge and Firefox can encode WebP, so the PNG is converted on your device. Other browsers fall back to our server, which converts the image in memory, keeps transparency and discards the file afterwards. Need to shrink an existing WebP? Use the <a href="{url:home}#webp">WebP compressor</a>.</p>
HTML],

    // Benefits grid. Icons are names from resources/views/components/ui/icon.blade.php.
    'benefits_heading' => 'Why convert PNG to WebP?',
    'benefit_1_icon' => 'layers',
    'benefit_1_title' => 'Keeps transparency',
    'benefit_1_text' => ['type' => 'textarea', 'value' => 'Transparent and semi-transparent areas stay transparent, unlike JPG.'],
    'benefit_2_icon' => 'minimize',
    'benefit_2_title' => 'Lighter cut-outs',
    'benefit_2_text' => ['type' => 'textarea', 'value' => 'Photo-like PNGs with transparency are often much smaller as lossy WebP.'],
    'benefit_3_icon' => 'search',
    'benefit_3_title' => 'Compare before download',
    'benefit_3_text' => ['type' => 'textarea', 'value' => 'Use the slider to check edges, shadows and text at your chosen quality.'],
    'benefit_4_icon' => 'globe',
    'benefit_4_title' => 'Browser support',
    'benefit_4_text' => ['type' => 'textarea', 'value' => 'Displayed by all current major web browsers.'],

    // FAQs.
    'faq_1_question' => 'Does PNG to WebP keep the transparent background?',
    'faq_1_answer' => ['type' => 'html', 'value' => '<p>Yes. WebP supports an alpha channel, so transparent and semi-transparent areas are kept. This is the main difference from the <a href="{url:tools.png-to-jpg}">PNG to JPG converter</a>, which fills transparency with white.</p>'],
    'faq_2_question' => 'Is the converted WebP lossless?',
    'faq_2_answer' => ['type' => 'html', 'value' => '<p>No. The converter creates lossy WebP. Most photos and soft illustrations look very close to the original at a good quality setting, but sharp edges, thin lines and small text can soften. Use the comparison slider to check before downloading.</p>'],
    'faq_3_question' => 'Should I convert logos and icons to WebP?',
    'faq_3_answer' => ['type' => 'html', 'value' => '<p>It depends. Simple graphics with a few flat colours can be small and perfectly sharp as a palette PNG, which the <a href="{url:home}#png">PNG compressor</a> creates. WebP tends to help more with photo-like images, gradients and soft shadows. Try both and compare the file sizes and edges.</p>'],
    'faq_4_question' => 'Can I use WebP images with transparency on my website?',
    'faq_4_answer' => ['type' => 'html', 'value' => '<p>Yes. All current major browsers display WebP, including its transparency. Some older software, upload forms and print services only accept JPG or PNG, so keep your original PNG for those.</p>'],
    'faq_5_question' => 'Is my PNG uploaded during conversion?',
    'faq_5_answer' => ['type' => 'html', 'value' => '<p>Not in browsers that can encode WebP, such as current Chrome, Edge and Firefox. In other browsers the image is sent to our server, converted in memory with transparency kept, and discarded.</p>'],
    'faq_6_question' => 'Is there a size limit?',
    'faq_6_answer' => ['type' => 'html', 'value' => '<p>You can convert one PNG at a time, up to {max_upload_mb} MB. Very large images are also limited by pixel count: 50 megapixels in the browser and 25 megapixels when the server fallback is used.</p>'],
];
