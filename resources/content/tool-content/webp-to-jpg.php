<?php

/*
| Default content for the WebP to JPG Converter tool. Every key can be edited in the admin; saved values win.
| Numbered keys (feature_1, how_to_step_1_title, benefit_1_title, faq_1_question, ...) form lists: add the next number
| to add an item, or clear the first part of an item to hide it.
*/

return [
    // Hero.
    'h1' => 'Convert WebP to JPG Online',
    'intro' => ['type' => 'textarea', 'value' => 'Turn WebP images into JPG files that older apps, upload forms and print services accept. Conversion runs in your browser.'],

    // Summary box and its example figure (also the page's social share image unless one is uploaded).
    'summary' => ['type' => 'textarea', 'value' => 'Upload your WebP, keep the output format set to JPG, choose a high quality such as 85–92% and download the .jpg file. Converting is a second lossy encode, so a high setting protects detail. Transparent areas become white, and animated WebP keeps only its first frame.'],
    'figure_alt' => ['type' => 'textarea', 'value' => 'Bar chart comparing the file size of a WebP photo with JPG conversions at quality 92, 85 and 75'],
    'figure_caption' => ['type' => 'textarea', 'value' => 'Original WebP 152 KB; JPG at quality 92 367 KB, 85 249 KB and 75 180 KB. Converting WebP to JPG is a second lossy encode, so a higher JPG quality keeps more of the remaining detail.'],

    // Tool card text used in "All tools" grids.
    'card_description' => ['type' => 'textarea', 'value' => 'Convert WebP images to JPG for apps, forms and print services.'],

    // Structured data feature list.
    'feature_1' => 'Converts WebP images up to {max_upload_mb} MB to JPG',
    'feature_2' => 'JPG encoding runs in your browser; the image is not uploaded',
    'feature_3' => 'Quality slider from 10% to 100%, or an approximate target file size',
    'feature_4' => 'Transparent areas are filled with white',
    'feature_5' => 'No account or sign-up required',

    // Introduction section.
    'why_heading' => 'When you need a JPG instead of WebP',
    'why_body' => ['type' => 'html', 'value' => <<<'HTML'
<p>Many websites now serve images as WebP. When you save one of those images, you end up with a <code>.webp</code> file that works fine in a browser but can cause trouble elsewhere. Some older photo viewers and editors cannot open it, some upload forms reject it, and some print services only accept JPG or PNG.</p>
<p>JPG is the most widely accepted photo format, so converting to JPG is usually the simplest fix. This page is set up to output JPG: upload a WebP, choose a quality, and download a <code>.jpg</code> file.</p>
<p>Only convert images you have the right to use. Saving an image from a website does not give you permission to reuse, print or publish it, so check the licence or ask the owner first.</p>
HTML],

    // How-to steps.
    'how_to_heading' => 'How to convert WebP to JPG',
    'how_to_step_1_title' => 'Add your WebP',
    'how_to_step_1_text' => ['type' => 'textarea', 'value' => 'Drag and drop, choose a file or paste an image. JPG output is already selected.'],
    'how_to_step_2_title' => 'Use a high quality',
    'how_to_step_2_text' => ['type' => 'textarea', 'value' => 'Around 85–92% protects detail, because this is a second lossy encode.'],
    'how_to_step_3_title' => 'Download the JPG',
    'how_to_step_3_text' => ['type' => 'textarea', 'value' => 'Check the result in the comparison slider, then download your .jpg file.'],

    // Detailed guidance section.
    'details_heading' => 'What changes when WebP becomes JPG',
    'details_body' => ['type' => 'html', 'value' => <<<'HTML'
<h3>A second lossy encode</h3>
<p>Most WebP images on the web are lossy: detail was already discarded when they were created. Saving as JPG compresses the image again with a different method, and the two sets of artefacts add up. Converting cannot restore anything the WebP lost, so the best you can do is avoid losing more.</p>
<p>That is why a high quality setting, around 85–92%, is a good choice here. The JPG may well be larger than the WebP it came from, which is normal: JPG and WebP quality numbers are not directly comparable, and JPG often needs more bytes for similar visual quality.</p>

<h3>Staying under an upload limit</h3>
<p>If a form only accepts JPG files under a certain size, set a target size instead of a quality. The tool lowers the quality first and reduces the dimensions only if needed to get close to the target. Results are approximate, so check the final size before uploading. You can also shrink an existing JPG further with the <a href="{url:home}#jpg">JPG compressor</a>.</p>

<h3>Transparency becomes white</h3>
<p>WebP can contain transparency, but JPG cannot. Transparent areas are filled with white, and soft or semi-transparent edges are blended onto white. If the image is a logo or cut-out that must stay transparent, keep the WebP, or reduce its size with the <a href="{url:home}#webp">WebP compressor</a>.</p>

<h3>Animated WebP files</h3>
<p>WebP can also hold animation, while JPG is always a still image. If you upload an animated WebP, only the first frame is kept in the JPG.</p>

<h3>Privacy and file details</h3>
<p>JPG output is always encoded in your browser, so your WebP is not uploaded. Re-encoding removes embedded metadata, and the download keeps the original name with <code>-compressed.jpg</code> added. Working with PNG files instead? The <a href="{url:tools.png-to-jpg}">PNG to JPG converter</a> works the same way.</p>

<h3>Going the other way</h3>
<p>If you publish images on a website, WebP is often the lighter format, and the <a href="{url:tools.jpg-to-webp}">JPG to WebP converter</a> creates WebP files from your JPGs.</p>
HTML],

    // Benefits grid. Icons are names from resources/views/components/ui/icon.blade.php.
    'benefits_heading' => 'Why convert WebP to JPG?',
    'benefit_1_icon' => 'check-circle',
    'benefit_1_title' => 'Opens almost anywhere',
    'benefit_1_text' => ['type' => 'textarea', 'value' => 'JPG works with older apps, upload forms and print services that reject WebP.'],
    'benefit_2_icon' => 'sliders',
    'benefit_2_title' => 'Control the quality',
    'benefit_2_text' => ['type' => 'textarea', 'value' => 'Pick a high setting to limit extra loss from the second encode.'],
    'benefit_3_icon' => 'lock',
    'benefit_3_title' => 'Stays on your device',
    'benefit_3_text' => ['type' => 'textarea', 'value' => 'JPG conversion runs in your browser, so the image is not uploaded.'],
    'benefit_4_icon' => 'user-x',
    'benefit_4_title' => 'No account',
    'benefit_4_text' => ['type' => 'textarea', 'value' => 'Convert one image at a time, up to {max_upload_mb} MB, without signing up.'],

    // FAQs.
    'faq_1_question' => 'Why won\'t my WebP image open or upload?',
    'faq_1_answer' => ['type' => 'html', 'value' => '<p>WebP is displayed by all current major browsers, but some older software, upload forms and print services only accept JPG or PNG. Converting the image to JPG usually solves this, because JPG is the most widely accepted photo format.</p>'],
    'faq_2_question' => 'What quality should I use for WebP to JPG?',
    'faq_2_answer' => ['type' => 'html', 'value' => '<p>Use a high quality, around 85–92%, if detail matters. A lossy WebP has already discarded some detail, and saving it as JPG is a second lossy encode. A low setting would add visible artefacts on top of the original compression.</p>'],
    'faq_3_question' => 'What happens to transparent WebP images?',
    'faq_3_answer' => ['type' => 'html', 'value' => '<p>JPG has no transparency, so transparent areas are filled with white and semi-transparent edges are blended onto white. If you need a transparent file, keep the WebP.</p>'],
    'faq_4_question' => 'Can I convert an animated WebP to JPG?',
    'faq_4_answer' => ['type' => 'html', 'value' => '<p>JPG cannot store animation. If you upload an animated WebP, only the first frame is kept and saved as a still JPG.</p>'],
    'faq_5_question' => 'Is my WebP uploaded to a server?',
    'faq_5_answer' => ['type' => 'html', 'value' => '<p>No. JPG output is always encoded in your browser, so the image stays on your device during WebP to JPG conversion.</p>'],
    'faq_6_question' => 'Can I convert WebP to PNG instead?',
    'faq_6_answer' => ['type' => 'html', 'value' => '<p>No. This tool outputs JPG or WebP, and only keeps PNG when the uploaded file is already a PNG. For transparent images, keeping the WebP is the way to preserve transparency here.</p>'],
];
