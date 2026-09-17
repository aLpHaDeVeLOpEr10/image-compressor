<?php

/*
| Default content for the JPG to WebP Converter tool. Every key can be edited in the admin; saved values win.
| Numbered keys (feature_1, how_to_step_1_title, benefit_1_title, faq_1_question, ...) form lists: add the next number
| to add an item, or clear the first part of an item to hide it.
*/

return [
    // Hero.
    'h1' => 'Convert JPG to WebP Online',
    'intro' => ['type' => 'textarea', 'value' => 'Convert JPG photos to WebP for lighter web pages. Compare the original and converted file sizes before you download.'],

    // Summary box and its example figure (also the page's social share image unless one is uploaded).
    'summary' => ['type' => 'textarea', 'value' => 'Upload your JPG, keep the output format set to WebP, pick a quality around 75–80% and compare the new file size with the original, then download the .webp file. Converting cannot restore detail that JPG compression has already removed, so start from the best JPG you have.'],
    'figure_alt' => ['type' => 'textarea', 'value' => 'Bar chart comparing the file size of a JPG photo with WebP conversions at quality 85, 80 and 75'],
    'figure_caption' => ['type' => 'textarea', 'value' => 'Original JPG 309 KB; WebP at quality 85 171 KB, 80 134 KB and 75 101 KB. WebP and JPG quality numbers are not directly comparable, so compare the images, not just the numbers.'],

    // Tool card text used in "All tools" grids.
    'card_description' => ['type' => 'textarea', 'value' => 'Convert JPG photos to WebP for lighter websites, blogs and stores.'],

    // Structured data feature list.
    'feature_1' => 'Converts JPG and JPEG images up to {max_upload_mb} MB to lossy WebP',
    'feature_2' => 'Encodes in your browser where WebP encoding is supported',
    'feature_3' => 'Shows original and converted file sizes side by side',
    'feature_4' => 'Quality slider from 10% to 100%, or an approximate target file size',
    'feature_5' => 'No account or sign-up required',

    // Introduction section.
    'why_heading' => 'Why convert JPG to WebP for the web?',
    'why_body' => ['type' => 'html', 'value' => <<<'HTML'
<p>Images are often the heaviest part of a web page. Product galleries, blog headers and portfolio photos are usually JPG, and for many of them a lossy WebP at similar visual quality is a smaller file. Smaller images download faster, especially on mobile connections.</p>
<p>WebP is displayed by all current major browsers, which makes it a practical format for websites, blogs and online stores. It is not always smaller than JPG, though, so this converter shows the original and converted sizes side by side. You can see the real difference for your image instead of relying on a general rule.</p>
<p>This page is set up to output WebP. Upload a JPG, adjust the quality, and download a <code>.webp</code> file. If you already have WebP files that need shrinking, use the <a href="{url:home}#webp">WebP compressor</a>.</p>
HTML],

    // How-to steps.
    'how_to_heading' => 'How to convert JPG to WebP',
    'how_to_step_1_title' => 'Add your JPG',
    'how_to_step_1_text' => ['type' => 'textarea', 'value' => 'Drag and drop, choose a file or paste a JPG or JPEG image. WebP output is already selected.'],
    'how_to_step_2_title' => 'Adjust quality',
    'how_to_step_2_text' => ['type' => 'textarea', 'value' => 'Start around 75–80% and compare the new file size with the original.'],
    'how_to_step_3_title' => 'Download the WebP',
    'how_to_step_3_text' => ['type' => 'textarea', 'value' => 'Check textures in the comparison slider, then download your .webp file.'],

    // Detailed guidance section.
    'details_heading' => 'Getting good results from JPG to WebP',
    'details_body' => ['type' => 'html', 'value' => <<<'HTML'
<h3>Compare sizes, not just settings</h3>
<p>WebP and JPG quality numbers are not directly comparable. A JPG saved at 90% and a WebP at 90% will not look or weigh the same. Instead of matching numbers, look at the result: compare the file sizes shown, zoom in on skin, foliage, fabric and other fine textures, and lower the quality only while the image still looks right.</p>
<p>If a JPG was already compressed heavily, the WebP may barely be smaller. In that case there is little reason to switch, and the original JPG may be the better file to keep. Setting a target size works too, but the tool may reduce dimensions if quality alone cannot reach it, so check that the image is still large enough for your layout.</p>

<h3>Convert once, from the best source</h3>
<p>Both JPG and lossy WebP discard detail. Converting cannot bring back what the JPG already lost, and every extra lossy save removes a little more. Convert from the highest-quality JPG you have, avoid converting the same image back and forth, and keep the original so you can re-export later with different settings.</p>

<h3>Using WebP on your site</h3>
<p>Many content management systems and store platforms accept WebP uploads, but support depends on the platform, its version and any plugins, so check before replacing a whole library. If you hand-code pages, the HTML <code>&lt;picture&gt;</code> element lets you offer WebP with a JPG fallback.</p>

<h3>When to keep JPG</h3>
<ul>
    <li><strong>Email attachments</strong> and files you share with people using unknown software.</li>
    <li><strong>Printing</strong>, since some print services only accept JPG or PNG.</li>
    <li><strong>Upload forms</strong> that list JPG as the accepted format.</li>
    <li><strong>Older desktop software</strong> that cannot open WebP.</li>
</ul>
<p>For those cases, make the JPG smaller with the <a href="{url:home}#jpg">JPG compressor</a>. If you receive a WebP that will not open somewhere, the <a href="{url:tools.webp-to-jpg}">WebP to JPG converter</a> turns it back into a JPG. Transparent PNG graphics are better handled by the <a href="{url:tools.png-to-webp}">PNG to WebP converter</a>.</p>

<h3>Where conversion happens</h3>
<p>Current Chrome, Edge and Firefox can encode WebP, so the image is converted on your device. In browsers that cannot, the tool sends the image to our server, converts it in memory and discards it. Either way, EXIF metadata such as GPS location is removed.</p>
HTML],

    // Benefits grid. Icons are names from resources/views/components/ui/icon.blade.php.
    'benefits_heading' => '',

    // FAQs.
    'faq_1_question' => 'Will converting JPG to WebP always make the file smaller?',
    'faq_1_answer' => ['type' => 'html', 'value' => '<p>Often, but not always. A lossy WebP is frequently smaller than a JPG at similar visual quality, but an already heavily compressed JPG may not shrink much. The result shows both file sizes, so keep whichever file is smaller at a quality you are happy with.</p>'],
    'faq_2_question' => 'Does converting to WebP improve image quality?',
    'faq_2_answer' => ['type' => 'html', 'value' => '<p>No. Any detail JPG compression removed is gone, and WebP is also a lossy encode. Convert from the highest-quality original you have rather than from a copy that has already been compressed several times.</p>'],
    'faq_3_question' => 'Should I use the same quality number as my JPG?',
    'faq_3_answer' => ['type' => 'html', 'value' => '<p>Not necessarily. WebP and JPG quality numbers are not directly comparable, so the same percentage can give a different look and size. Start around 75–80% and check fine textures in the comparison slider.</p>'],
    'faq_4_question' => 'Can I upload WebP images to WordPress, Shopify or other platforms?',
    'faq_4_answer' => ['type' => 'html', 'value' => '<p>Many website platforms and content management systems accept WebP, but support varies by platform, version, theme and plugins. Check that your platform accepts WebP uploads before replacing all your JPG images.</p>'],
    'faq_5_question' => 'Is my JPG uploaded when converting to WebP?',
    'faq_5_answer' => ['type' => 'html', 'value' => '<p>In browsers that can encode WebP, such as current Chrome, Edge and Firefox, conversion happens on your device. Otherwise the image is sent to our server, processed in memory and discarded after the WebP is returned.</p>'],
    'faq_6_question' => 'Does the conversion keep my photo\'s EXIF data?',
    'faq_6_answer' => ['type' => 'html', 'value' => '<p>No. Re-encoding removes EXIF metadata such as GPS location and camera details. The image is still displayed the right way up.</p>'],
];
