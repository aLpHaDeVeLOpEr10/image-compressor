<?php

/*
| Default content for the homepage (image compressor). Every key can be edited in the admin; saved values win.
| Numbered keys (feature_1, how_to_step_1_title, faq_1_question, ...) form lists: add the next number to add an item,
| or clear the first part of an item to hide it.
*/

return [
    // Hero.
    'h1' => 'Free Online Image Compressor',
    'intro' => ['type' => 'textarea', 'value' => 'Compress JPG, PNG and WebP images, reduce a photo to a target size such as 20KB, 100KB or 1MB, or convert to JPG or WebP. Compare the result with your original and download it.'],

    // Summary box and its example figure (also the page's social share image unless one is uploaded).
    'summary' => ['type' => 'textarea', 'value' => 'To compress an image, upload a JPG, PNG or WebP file, keep the quality around 80% or choose a target size such as 100 KB, then download the result. JPG compression runs in your browser, PNG keeps transparency, and dimensions change only when a target size cannot be met otherwise.'],
    'figure_alt' => ['type' => 'textarea', 'value' => 'Line chart of file size against quality setting for JPG and WebP versions of the same 1600 × 1067 test photo'],
    'figure_caption' => ['type' => 'textarea', 'value' => 'Measured with the PicsCompressor server encoder (PHP GD). At quality 80 the JPG is 168 KB and the WebP 112 KB; at quality 100 the JPG grows to 934 KB. File size rises steeply above about 90.'],

    // Tool card text used in "All tools" grids and the footer.
    'card_description' => ['type' => 'textarea', 'value' => 'Compress JPG, PNG and WebP images by quality or to a target size.'],

    // Structured data feature list.
    'feature_1' => 'Compress JPG, PNG and WebP up to 20 MB',
    'feature_2' => 'Adjustable quality from 10% to 100%',
    'feature_3' => 'Target size presets and custom sizes from 5 KB to 20 MB',
    'feature_4' => 'PNG palette compression that keeps transparency',
    'feature_5' => 'Convert to JPG or WebP',
    'feature_6' => 'Before-and-after comparison',
    'feature_7' => 'Removes EXIF metadata such as GPS location',

    // "Which setting should I use?" table.
    'choose_heading' => 'Which setting should I use?',
    'choose_description' => 'Match what you are trying to do with the right mode or converter.',
    'choose_column_task' => 'If you want to…',
    'choose_column_use' => 'Use',
    'choose_column_why' => 'Why',
    'choose_row_1_task' => 'Upload a photo to a form with a size limit',
    'choose_row_1_label' => 'Target size',
    'choose_row_1_url' => '{url:home}#target-size',
    'choose_row_1_why' => 'Choose 100 KB (or any size from 20KB to 2MB) and JPG output.',
    'choose_row_2_task' => 'Upload a signature image',
    'choose_row_2_label' => 'Target size',
    'choose_row_2_url' => '{url:home}#target-size',
    'choose_row_2_why' => 'A custom 20 KB target suits dark ink on plain white paper.',
    'choose_row_3_task' => 'Shrink a screenshot or logo but keep transparency',
    'choose_row_3_label' => 'PNG compression',
    'choose_row_3_url' => '{url:home}#png',
    'choose_row_3_why' => 'Palette reduction keeps sharp edges and transparent areas.',
    'choose_row_4_task' => 'Speed up images on a website',
    'choose_row_4_label' => 'JPG to WebP',
    'choose_row_4_url' => '{url:tools.jpg-to-webp}',
    'choose_row_4_why' => 'WebP is often smaller than JPG at a similar visual quality.',
    'choose_row_5_task' => 'Open or upload a WebP where only JPG works',
    'choose_row_5_label' => 'WebP to JPG',
    'choose_row_5_url' => '{url:tools.webp-to-jpg}',
    'choose_row_5_why' => 'JPG is the most widely accepted photo format.',

    // How-to steps.
    'how_to_heading' => 'How to compress an image',
    'how_to_description' => 'Three steps, no account and nothing to install.',
    'how_to_step_1_title' => 'Upload your image',
    'how_to_step_1_text' => ['type' => 'textarea', 'value' => 'Drag and drop a JPG, PNG or WebP file, choose it from your device, or paste it from your clipboard.'],
    'how_to_step_2_title' => 'Choose your settings',
    'how_to_step_2_text' => ['type' => 'textarea', 'value' => 'Set the quality, optionally pick a target size such as 100 KB, and keep the original format or convert to JPG or WebP.'],
    'how_to_step_3_title' => 'Compare and download',
    'how_to_step_3_text' => ['type' => 'textarea', 'value' => 'Check the before-and-after comparison and the size saved, then download the compressed image.'],

    // Content sections.
    'modes_heading' => 'One compressor, every mode',
    'modes_body' => ['type' => 'html', 'value' => <<<'HTML'
<p>Image compression removes data that contributes little to how an image looks. A photo straight from a phone camera is often several megabytes, and much of that detail is never visible on a screen. This compressor handles every common job from one page, with three controls:</p>
<ul>
    <li><strong>Quality (10–100%)</strong> — lower values give smaller files with more visible compression. For JPG and WebP photos, 70–85% is a good range.</li>
    <li><strong>Target size</strong> — choose 50 KB, 100 KB, 200 KB, 500 KB or 1 MB, or enter any custom size from 5 KB to 20 MB. The tool finds the highest quality that fits and reduces dimensions only if that is not enough.</li>
    <li><strong>Output format</strong> — keep the original format, or convert to JPG or WebP, which are usually smaller for photographs.</li>
</ul>
<p>Every result shows the real original size, compressed size, dimensions and percentage saved. Re-encoding also removes embedded metadata such as camera EXIF data and GPS location.</p>
HTML],

    'jpg_heading' => 'Compress JPG and JPEG images',
    'jpg_body' => ['type' => 'html', 'value' => <<<'HTML'
<p>JPG (also written JPEG) is the standard format for photos. It uses lossy compression: the quality setting controls how much fine detail is rounded away. Photos from phones and cameras are saved at high quality, so there is usually a lot of room to shrink them. Upload a JPG and keep <strong>Same (JPEG)</strong> as the output.</p>
<ul>
    <li><strong>75–85%</strong> suits websites, sharing and most everyday use.</li>
    <li><strong>55–70%</strong> suits thumbnails and strict size limits.</li>
    <li><strong>Always compress from the original.</strong> Each lossy re-save compounds earlier losses.</li>
    <li><strong>JPG has no transparency.</strong> Converting a transparent image to JPG fills transparent areas with white.</li>
</ul>
<p>JPG output is always encoded by your browser, so the photo is not uploaded.</p>
HTML],
    'jpg_figure_alt' => ['type' => 'textarea', 'value' => 'Four enlarged crops of the same photo saved as JPG at quality 90, 70, 40 and 15, with blocking and colour smearing visible at low quality'],
    'jpg_figure_caption' => ['type' => 'textarea', 'value' => 'Whole-image JPG sizes: quality 90 = 277 KB, 70 = 126 KB, 40 = 74.4 KB, 15 = 38.2 KB. Measured with the PicsCompressor server encoder; enlarged 2× so artifacts are visible.'],

    'png_heading' => 'Compress PNG images and keep transparency',
    'png_body' => ['type' => 'html', 'value' => <<<'HTML'
<p>PNG is lossless, which makes it ideal for screenshots, logos, diagrams and anything with transparency, but large for photos. Upload a PNG and keep <strong>Same (PNG)</strong> as the output:</p>
<ul>
    <li><strong>Below 100% quality</strong>, the image is reduced to a palette of up to 256 colours. Lower quality means fewer colours: about 256 at 90% and above, down to 16 at 10%. Full and partial transparency are kept.</li>
    <li><strong>At 100%</strong>, the PNG is re-saved losslessly with maximum compression.</li>
    <li><strong>Photos saved as PNG</strong> are usually far smaller as JPG, or as WebP if they need transparency. Use the <a href="{url:tools.png-to-jpg}">PNG to JPG</a> or <a href="{url:tools.png-to-webp}">PNG to WebP</a> converter.</li>
</ul>
<p>Palette reduction runs in your browser. Older browsers without the required features fall back to our server, which keeps transparent PNGs lossless.</p>
HTML],

    'webp_heading' => 'Compress WebP images',
    'webp_body' => ['type' => 'html', 'value' => <<<'HTML'
<p>WebP covers what used to need two formats: lossy compression for photos and lossless compression with transparency for graphics. All current major browsers display it, and a lossy WebP is often smaller than a JPG at similar visual quality. Upload a WebP to shrink it further, or choose <strong>WebP</strong> as the output to convert a JPG or PNG.</p>
<ul>
    <li>Start around <strong>75–80%</strong> for photos. WebP and JPG quality numbers are not directly comparable, so judge the result, not the number.</li>
    <li>Transparency is kept. Animated WebP files keep only their first frame.</li>
    <li>Some older software, upload forms and print services do not accept WebP. Use the <a href="{url:tools.webp-to-jpg}">WebP to JPG converter</a> for those.</li>
</ul>
<p>WebP is encoded in your browser where supported (current Chrome, Edge and Firefox); otherwise our server compresses it in memory without storing it.</p>
HTML],

    'target_heading' => 'Compress an image to a specific size (20KB to 2MB)',
    'target_body' => ['type' => 'html', 'value' => <<<'HTML'
<p>Application forms, portals, marketplaces and email often cap file sizes. Choose a preset under <strong>Target size</strong>, or select <strong>Custom size</strong> and enter any value in KB. The tool tries your chosen quality first, then the highest quality that fits, and reduces the width and height only if even the lowest quality is too large. The result screen says whether the target was reached.</p>

<table>
    <thead>
        <tr><th>Target</th><th>Typical use</th><th>Tip</th></tr>
    </thead>
    <tbody>
        <tr><td>20 KB (custom)</td><td>Signature uploads, tiny ID images</td><td>Dark ink on plain white paper, cropped tightly, fits easily.</td></tr>
        <tr><td>50 KB</td><td>Small form photos, avatars, thumbnails</td><td>Crop to the face and use a plain background.</td></tr>
        <tr><td>100 KB</td><td>Application forms, ID and profile photos</td><td>Use JPG output; portraits around 600 × 800 pixels often fit.</td></tr>
        <tr><td>200 KB</td><td>Document uploads, listings, web images</td><td>Resize to the size the image is displayed at first.</td></tr>
        <tr><td>500 KB</td><td>Portfolios, large website photos</td><td>Raise the quality slider to 85–90% to keep detail.</td></tr>
        <tr><td>1 MB</td><td>Email attachments, high-resolution photos</td><td>Many phone photos fit near full resolution as JPG or WebP.</td></tr>
        <tr><td>2 MB (custom)</td><td>Near-full-resolution photos for listings</td><td>Convert large PNG screenshots to JPG or WebP first.</td></tr>
    </tbody>
</table>

<h3>Leave a small margin</h3>
<p>This tool counts 1 KB as 1,024 bytes and 1 MB as 1,024 KB. Some websites count 1 KB as 1,000 bytes, so if a file very close to the limit is rejected, enter a slightly lower custom target, such as 95 KB for a 100 KB limit. Results are usually a little under the target, because encoders cannot produce an exact byte count.</p>

<h3>Photos, signatures and scans</h3>
<p>For small targets, cropping away empty space helps more than lowering quality. Scanned documents stay readable only at reasonable dimensions, so check text at 100% zoom.</p>
HTML],
    'target_figure_alt' => ['type' => 'textarea', 'value' => 'A 12-megapixel landscape test photo compressed with a 100 KB target, shown with its measured size, dimensions and quality'],
    'target_figure_caption' => ['type' => 'textarea', 'value' => 'A 4000 × 3000 photo (2.61 MB) compressed with a 100 KB target: 93.9 KB at 3142 × 2356 pixels, JPG quality 10, after the dimensions were reduced. Measured with the PicsCompressor server compressor, which uses the same quality-then-resize approach as the in-browser tool.'],

    'formats_heading' => 'Supported formats and limits',
    'formats_description' => 'What you can upload, what you can download, and where each image is processed.',
    'formats_body' => ['type' => 'html', 'value' => <<<'HTML'
<table>
    <thead>
        <tr><th>Format</th><th>Upload</th><th>Download as</th><th>Where it is compressed</th></tr>
    </thead>
    <tbody>
        <tr><td>JPG / JPEG</td><td>Yes, up to {max_upload_mb} MB</td><td>JPG or WebP</td><td>JPG output: always in your browser</td></tr>
        <tr><td>PNG</td><td>Yes, up to {max_upload_mb} MB</td><td>PNG, JPG or WebP</td><td>PNG output: in your browser, or our server in older browsers</td></tr>
        <tr><td>WebP</td><td>Yes, up to {max_upload_mb} MB</td><td>WebP or JPG</td><td>WebP output: in your browser where supported, otherwise our server (in memory, not stored)</td></tr>
    </tbody>
</table>
<p>Images can be up to {max_megapixels} megapixels when processed in your browser, or {server_max_megapixels} megapixels when the server fallback is used. One image is compressed at a time; select <strong>Compress Another Image</strong> to start the next.</p>
<p>Need a different format? The <a href="{url:tools.png-to-jpg}">PNG to JPG</a>, <a href="{url:tools.jpg-to-webp}">JPG to WebP</a>, <a href="{url:tools.png-to-webp}">PNG to WebP</a> and <a href="{url:tools.webp-to-jpg}">WebP to JPG</a> converters have the output already selected.</p>
HTML],

    // FAQs (also published as FAQ structured data on the FAQ page).
    'faq_1_question' => 'How do I reduce the size of an image?',
    'faq_1_answer' => ['type' => 'html', 'value' => '<p>Upload the image, choose a quality level (80% is a sensible starting point) or pick a target size, then select <strong>Compress Image</strong>. When the result appears, compare it with the original and select <strong>Download Compressed Image</strong>.</p>'],
    'faq_2_question' => 'How do I compress an image to 100KB, 50KB or 20KB?',
    'faq_2_answer' => ['type' => 'html', 'value' => '<p>Under <strong>Target size</strong>, choose a preset such as 50 KB or 100 KB, or select <strong>Custom size</strong> and enter a value such as 20. Choose JPG output for forms. The tool finds the highest quality that fits and reduces the dimensions only if needed. The result is approximate and usually slightly under the target.</p>'],
    'faq_3_question' => 'Is 100KB here the same as 100KB on an upload form?',
    'faq_3_answer' => ['type' => 'html', 'value' => '<p>This tool counts 1 KB as 1,024 bytes. Some websites count 1,000 bytes, so if a file very close to the limit is rejected, set a custom target a little lower, such as 95 KB.</p>'],
    'faq_4_question' => 'Will compressing my image reduce its quality?',
    'faq_4_answer' => ['type' => 'html', 'value' => '<p>JPG, WebP and palette-based PNG compression are lossy, so some detail is discarded. At moderate settings the difference is usually hard to see at normal viewing size. Use the before-and-after comparison to check, and raise the quality if you notice artifacts.</p>'],
    'faq_5_question' => 'Is JPG the same as JPEG?',
    'faq_5_answer' => ['type' => 'html', 'value' => '<p>Yes. JPG and JPEG are two file extensions for the same format, and the compressor accepts both.</p>'],
    'faq_6_question' => 'Does PNG compression keep transparency?',
    'faq_6_answer' => ['type' => 'html', 'value' => '<p>Yes. When the output stays PNG or is converted to WebP, transparency is kept. Converting to JPG fills transparent areas with white, because JPG does not support transparency.</p>'],
    'faq_7_question' => 'Is my image uploaded to a server?',
    'faq_7_answer' => ['type' => 'html', 'value' => '<p>In modern browsers, no. JPG, WebP and PNG compression runs in your browser. Only if your browser cannot encode WebP or PNG does the tool fall back to our server, where the image is processed in memory and not stored.</p>'],
    'faq_8_question' => 'What is the maximum file size?',
    'faq_8_answer' => ['type' => 'html', 'value' => '<p>You can compress images up to {max_upload_mb} MB in JPG, JPEG, PNG or WebP format, one image at a time.</p>'],
    'faq_9_question' => 'Why is my compressed image larger than the original?',
    'faq_9_answer' => ['type' => 'html', 'value' => '<p>This can happen when the original is already heavily compressed, or when you convert to a format that suits the image less well. Try a lower quality, a different output format, or keep your original. The tool always shows the real sizes so you can decide.</p>'],
    'faq_10_question' => 'Does the image compressor work on my phone?',
    'faq_10_answer' => ['type' => 'html', 'value' => '<p>Yes. It works in current mobile browsers on Android and iOS, with photos from your gallery or camera roll. Very large images may be limited by the memory available on your device.</p>'],
];
