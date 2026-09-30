<?php

/*
| Default text shared by every tool page: page chrome, structured data and the compressor widget.
| A plain string is a "text" field; use ['type' => ..., 'value' => ...] for textarea or html fields.
| Values can use placeholders such as {max_upload_mb} and {url:pages.faq}; see App\Tools\ToolContent::resolve().
| Widget messages starting with widget_msg_ are used by the JavaScript and may contain :tokens filled in at runtime.
*/

return [
    // Highlights section below the tool: a title and short description per item.
    'hero_points_heading' => 'Simple, free and private',
    'hero_point_1' => 'Free to use',
    'hero_point_1_text' => ['type' => 'textarea', 'value' => 'Compress and convert as many images as you like, with no fees or watermarks.'],
    'hero_point_2' => 'No registration',
    'hero_point_2_text' => ['type' => 'textarea', 'value' => 'No account or email needed. Open the page and start right away.'],
    'hero_point_3' => 'JPG, PNG and WebP',
    'hero_point_3_text' => ['type' => 'textarea', 'value' => 'Works with the most common image formats and converts between them.'],
    'hero_point_4' => 'Up to {max_upload_mb} MB',
    'hero_point_4_text' => ['type' => 'textarea', 'value' => 'Upload large photos straight from your phone or camera.'],
    'language_switcher_label' => 'Language',

    // Summary box.
    'summary_label' => 'Short answer',
    'summary_updated_label' => 'Last updated',

    // FAQ and related tools sections.
    'faq_heading' => 'Frequently asked questions',
    'faq_description' => '',
    'faq_more_html' => ['type' => 'html', 'value' => 'More answers in our <a href="{url:pages.faq}" class="link">image compression FAQ</a>.'],
    'related_heading' => 'All tools',
    'related_description' => '',
    'tool_card_cta' => 'Open tool',

    // Structured data (WebApplication).
    'schema_subcategory' => 'Image compression',
    'schema_operating_system' => 'Any (runs in a web browser)',
    'schema_browser_requirements' => 'Requires JavaScript and a modern web browser.',

    // Compressor widget: frame and privacy note.
    'widget_aria_label' => 'Image compressor',
    'widget_noscript' => ['type' => 'textarea', 'value' => 'The image compressor needs JavaScript. Please enable JavaScript in your browser to compress images.'],
    'widget_privacy_note' => ['type' => 'textarea', 'value' => 'Your image is processed for compression and is not permanently stored. In modern browsers, compression runs on your device, so the image is not uploaded. Older browsers that cannot encode WebP or PNG fall back to our server, where the image is processed in memory and discarded as soon as the result is returned.'],
    'widget_privacy_link' => 'Privacy policy',

    // Compressor widget: upload area.
    'widget_drop_heading' => 'Drop your image here',
    'widget_drop_or' => 'or',
    'widget_choose_button' => 'Choose Image',
    'widget_supported_label' => 'Supported:',
    'widget_max_size_label' => 'Maximum file size:',
    'widget_max_size_value' => '{max_upload_mb} MB',
    'widget_paste_hint' => 'You can also paste an image from your clipboard.',

    // Compressor widget: preview.
    'widget_preview_alt' => 'Preview of the selected image',
    'widget_file_name_label' => 'File name',
    'widget_dimensions_label' => 'Dimensions',
    'widget_file_size_label' => 'File size',
    'widget_format_label' => 'Format',
    'widget_size_label' => 'Size',

    // Compressor widget: settings.
    'widget_quality_label' => 'Quality:',
    'widget_quality_smaller' => 'Smaller file',
    'widget_quality_higher' => 'Higher quality',
    'widget_target_legend' => 'Target size',
    'widget_no_target' => 'No target',
    'widget_custom_size' => 'Custom size',
    'widget_custom_target_label' => 'Custom target (KB)',
    'widget_custom_target_placeholder' => 'e.g. 150',
    'widget_output_legend' => 'Output format',
    'widget_output_original' => 'Same as original',
    'widget_output_jpg' => 'JPG',
    'widget_output_webp' => 'WebP',
    'widget_compress_button' => 'Compress Image',
    'widget_choose_different' => 'Choose a different image',

    // Compressor widget: result.
    'widget_download_button' => 'Download Compressed Image',
    'widget_compare_heading' => 'Before and after',
    'widget_compare_tabs_label' => 'Comparison view',
    'widget_tab_slider' => 'Slider',
    'widget_tab_side' => 'Side by side',
    'widget_original' => 'Original',
    'widget_compressed' => 'Compressed',
    'widget_original_alt' => 'Original image',
    'widget_compressed_alt' => 'Compressed image',
    'widget_compare_range_label' => 'Comparison position: drag to reveal more of the original or compressed image',
    'widget_compare_hint' => 'Drag the slider, or use the arrow keys, to compare. Zoom in on your device to check fine detail.',
    'widget_adjust_button' => 'Adjust settings',
    'widget_another_button' => 'Compress Another Image',

    // Compressor widget: messages shown by JavaScript.
    'widget_msg_unsupported' => 'Please upload a JPG, PNG, or WebP image.',
    'widget_msg_wrong_format' => 'This tool only accepts :formats images. Please upload a :formats file.',
    'widget_msg_too_large' => 'Your image exceeds the maximum allowed file size of :mb MB.',
    'widget_msg_invalid' => "We couldn't process this image. Please try another file.",
    'widget_msg_too_many_pixels' => 'This image has very large dimensions and cannot be processed. Please resize it and try again.',
    'widget_msg_server_too_many_pixels' => 'This image is too large to process on our server. Choose JPG output, or resize the image first.',
    'widget_msg_failed' => 'Something went wrong while compressing your image. Please try again.',
    'widget_msg_custom_target' => 'Enter a target between 5 and :max KB.',
    'widget_msg_pasted_image' => 'Pasted image',
    'widget_msg_loaded' => ':name loaded. :dimensions pixels, :size. Choose settings and compress.',
    'widget_msg_target_enter' => 'Enter a custom target size in KB.',
    'widget_msg_target_summary' => 'Target size: approximately :size. Quality is lowered first, and dimensions are reduced only if needed. The exact result can vary.',
    'widget_msg_no_target' => 'No target size. The image is compressed at the selected quality.',
    'widget_msg_quality_target' => 'With a target size, this is the highest quality that will be used.',
    'widget_msg_quality_png' => 'For PNG, lower quality uses fewer colours. 100% keeps the PNG lossless.',
    'widget_msg_quality_default' => 'Lower quality produces smaller files. 70–85% is a good balance for most photos.',
    'widget_msg_same_format' => 'Same (:format)',
    'widget_msg_png_help' => 'PNG output reduces colours to a palette of up to 256 and keeps transparency. For photos, JPG or WebP is usually much smaller.',
    'widget_msg_png_server' => 'Your browser cannot run PNG compression, so PNG output is processed on our server.',
    'widget_msg_webp_help' => 'WebP supports transparency and is often smaller than JPG or PNG.',
    'widget_msg_webp_server' => 'Your browser cannot create WebP files, so WebP output is processed on our server.',
    'widget_msg_jpg_transparency' => 'JPG does not support transparency. Transparent areas become white.',
    'widget_msg_jpg_help' => 'JPG is ideal for photos. Compression is done in your browser.',
    'widget_msg_uploading' => 'Uploading and compressing…',
    'widget_msg_compressing_browser' => 'Compressing in your browser…',
    'widget_msg_compressing' => 'Compressing…',
    'widget_msg_trying_quality' => 'Trying quality :quality%',
    'widget_msg_trying_colours' => 'Trying :colours colours',
    'widget_msg_reducing' => 'Reducing dimensions to :width × :height',
    'widget_msg_saved' => 'You saved :percent',
    'widget_msg_unchanged' => 'The file size is unchanged',
    'widget_msg_larger' => 'The result is :percent larger',
    'widget_msg_detail' => 'Original: :original · Compressed: :compressed · Saved: :saved',
    'widget_msg_target_reached' => 'Target size reached: approximately :target requested, :produced produced.',
    'widget_msg_target_missed' => 'This image could not be reduced to approximately :target. This is the smallest result we could produce.',
    'widget_msg_resized' => 'Dimensions were reduced from :from to :to to get close to the target size.',
    'widget_msg_already_optimized' => 'The original (:size) is already well optimized for these settings. Try a lower quality or a different output format, or keep your original file.',
    'widget_msg_processed_server' => 'Compressed on our server in memory. The image was not stored.',
    'widget_msg_processed_browser' => 'Compressed in your browser. The image was not uploaded.',
    'widget_msg_ready' => 'Ready for a new image.',
    'widget_msg_network' => 'Could not reach the server. Please check your connection and try again.',
    'widget_msg_session_expired' => 'Your session has expired. Please refresh the page and try again.',
    'widget_msg_server_too_large' => 'Your image exceeds the maximum allowed file size.',
];
