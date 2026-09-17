<?php

/*
| Default content for the PNG to JPG Converter tool. Every key can be edited in the admin; saved values win.
| Numbered keys (feature_1, how_to_step_1_title, benefit_1_title, faq_1_question, ...) form lists: add the next number
| to add an item, or clear the first part of an item to hide it.
*/

return [
    // Hero.
    'h1' => 'Convert PNG to JPG Online',
    'intro' => ['type' => 'textarea', 'value' => 'Turn large PNG photos and screenshots into widely accepted JPG files. Conversion runs in your browser, so your image is not uploaded.'],

    // Summary box and its example figure (also the page's social share image unless one is uploaded).
    'summary' => ['type' => 'textarea', 'value' => 'Upload your PNG, keep the output format set to JPG, choose a quality (around 80–90% suits most photos) or a target size, then download the .jpg file. JPG has no transparency, so any transparent areas in the PNG are filled with white.'],
    'figure_alt' => ['type' => 'textarea', 'value' => 'A logo PNG with a transparent background next to the converted JPG, where the transparent area has become solid white'],
    'figure_caption' => ['type' => 'textarea', 'value' => 'The transparent PNG is 13.7 KB. Converted to JPG at quality 85 it is 31.0 KB, and the checkerboard (transparency) is replaced by white because JPG cannot store transparency. For flat graphics like this logo JPG can be larger than PNG; the big savings come from photos saved as PNG.'],

    // Tool card text used in "All tools" grids.
    'card_description' => ['type' => 'textarea', 'value' => 'Convert PNG photos and screenshots to smaller, widely accepted JPG files.'],

    // Structured data feature list.
    'feature_1' => 'Converts PNG images up to {max_upload_mb} MB to JPG',
    'feature_2' => 'JPG encoding runs in your browser; the image is not uploaded',
    'feature_3' => 'Quality slider from 10% to 100%, or an approximate target file size',
    'feature_4' => 'Transparent areas are filled with white',
    'feature_5' => 'No account or sign-up required',

    // Introduction section.
    'why_heading' => 'Why convert PNG to JPG?',
    'why_body' => ['type' => 'html', 'value' => <<<'HTML'
<p>PNG stores every pixel exactly. That is ideal for logos and interface graphics, but it makes photographs and busy screenshots very large. A phone photo or a full-screen capture saved as PNG can easily be several megabytes, which is slow to email, awkward to attach and often too big for upload limits.</p>
<p>JPG is a lossy format built for photographs. It discards detail that is hard to see, so the same photo is usually much smaller as JPG. It is also the most widely accepted photo format: many application forms, marketplaces, print services and older programs accept JPG even when they reject PNG.</p>
<p>This page is set up to output JPG. Upload a PNG, choose a quality or a target size, and download a <code>.jpg</code> file. If you would rather keep PNG and just make it smaller, try the <a href="{url:home}#png">PNG compressor</a>.</p>
HTML],

    // How-to steps.
    'how_to_heading' => 'How to convert PNG to JPG',
    'how_to_step_1_title' => 'Add your PNG',
    'how_to_step_1_text' => ['type' => 'textarea', 'value' => 'Drag and drop, choose a file or paste an image. JPG output is already selected.'],
    'how_to_step_2_title' => 'Set quality or a target size',
    'how_to_step_2_text' => ['type' => 'textarea', 'value' => 'Use a higher quality for screenshots with text, or a target size such as 200 KB for an upload form.'],
    'how_to_step_3_title' => 'Check and download',
    'how_to_step_3_text' => ['type' => 'textarea', 'value' => 'Compare edges and backgrounds with the slider, then download your .jpg file.'],

    // Detailed guidance section.
    'details_heading' => 'Transparency, quality and when to keep PNG',
    'details_body' => ['type' => 'html', 'value' => <<<'HTML'
<h3>Transparent backgrounds become white</h3>
<p>JPG has no alpha channel, so it cannot store transparency. When you convert, fully transparent pixels are filled with white, and partially transparent edges such as soft shadows or smoothed outlines are blended onto white. A logo that looked fine on a dark page will now sit in a white rectangle.</p>
<p>To check whether your PNG has transparency, open it in an image viewer or editor. Transparent areas are usually shown as a grey and white checkerboard. If you need to keep them, convert with the <a href="{url:tools.png-to-webp}">PNG to WebP converter</a> instead, which keeps transparency.</p>

<h3>Choosing quality for photos and screenshots</h3>
<p>Photos hide compression well, so a quality around 80–85% is a sensible starting point. Screenshots with small text, sharp lines and flat colours show JPG artefacts more easily, as faint smudges around letters. For those, start around 90% and zoom in on text before downloading.</p>
<p>If a form sets a maximum file size, use a target size instead. The tool lowers quality first and only reduces the image dimensions if the target cannot be reached otherwise. Results are approximate, so check the final size shown before you upload.</p>

<h3>When not to convert</h3>
<ul>
    <li><strong>Logos and icons</strong> with transparent backgrounds or a few flat colours. They often stay sharper and can be small as a palette PNG.</li>
    <li><strong>Text-heavy screenshots and diagrams</strong> where crisp edges matter more than file size.</li>
    <li><strong>Images you will edit again.</strong> Each JPG save discards more detail, so edit the PNG and convert once at the end.</li>
</ul>
<p>Whatever you choose, keep your original PNG. The converted JPG cannot give back the detail or transparency it removed.</p>

<h3>Privacy and file details</h3>
<p>JPG output is always encoded in your browser, so your PNG is not uploaded during conversion. Re-encoding also removes embedded metadata. The download keeps your original name with <code>-compressed.jpg</code> added. Already have a JPG that is too large? Use the <a href="{url:home}#jpg">JPG compressor</a>.</p>
HTML],

    // Benefits grid. Icons are names from resources/views/components/ui/icon.blade.php.
    'benefits_heading' => 'Why use this PNG to JPG converter?',
    'benefit_1_icon' => 'minimize',
    'benefit_1_title' => 'Smaller photos',
    'benefit_1_text' => ['type' => 'textarea', 'value' => 'Photos and busy screenshots are usually far smaller as JPG than as PNG.'],
    'benefit_2_icon' => 'check-circle',
    'benefit_2_title' => 'Widely accepted',
    'benefit_2_text' => ['type' => 'textarea', 'value' => 'JPG works with most upload forms, print services and older software.'],
    'benefit_3_icon' => 'lock',
    'benefit_3_title' => 'Stays on your device',
    'benefit_3_text' => ['type' => 'textarea', 'value' => 'JPG conversion runs in your browser, so the image is not uploaded.'],
    'benefit_4_icon' => 'target',
    'benefit_4_title' => 'Hit a size limit',
    'benefit_4_text' => ['type' => 'textarea', 'value' => 'Set a target size such as 100 KB or 500 KB for forms with strict limits.'],

    // FAQs.
    'faq_1_question' => 'What happens to transparent areas when I convert PNG to JPG?',
    'faq_1_answer' => ['type' => 'html', 'value' => '<p>JPG cannot store transparency, so transparent pixels are filled with white. Semi-transparent edges, such as soft shadows or anti-aliased outlines, are blended onto white. If you need to keep transparency, use the <a href="{url:tools.png-to-webp}">PNG to WebP converter</a> instead.</p>'],
    'faq_2_question' => 'How can I tell if my PNG has transparency?',
    'faq_2_answer' => ['type' => 'html', 'value' => '<p>Many image viewers and editors show transparent areas as a grey and white checkerboard pattern. If you see that behind your image, or the background changes colour when the viewer\'s background changes, the PNG contains transparency and those areas will turn white as JPG.</p>'],
    'faq_3_question' => 'Will my PNG be uploaded to a server?',
    'faq_3_answer' => ['type' => 'html', 'value' => '<p>No. JPG output is always encoded in your browser, so the image stays on your device during PNG to JPG conversion.</p>'],
    'faq_4_question' => 'Is converting PNG to JPG lossless?',
    'faq_4_answer' => ['type' => 'html', 'value' => '<p>No. PNG is lossless and JPG is a lossy format, so some detail is discarded. At high quality settings the difference is usually hard to see in photos, but sharp text and flat colours can show artefacts around edges. Keep your original PNG in case you need it later.</p>'],
    'faq_5_question' => 'Can I convert several PNG files at once?',
    'faq_5_answer' => ['type' => 'html', 'value' => '<p>The converter handles one image at a time. Each file can be up to {max_upload_mb} MB, and the downloaded file is named after the original, for example <code>photo-compressed.jpg</code>.</p>'],
    'faq_6_question' => 'Can I convert JPG back to PNG here?',
    'faq_6_answer' => ['type' => 'html', 'value' => '<p>No. The tool outputs JPG or WebP, or keeps a PNG as PNG, but it does not create PNG files from JPG. Converting a JPG to PNG would also not restore detail that JPG compression has already removed.</p>'],
];
