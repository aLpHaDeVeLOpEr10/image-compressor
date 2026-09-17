<?php

/*
| Default site-wide text for the header, footer and page layout. Edited on the homepage tool in the admin,
| but shown on every page of the site.
*/

return [
    'site_skip_link' => 'Skip to content',

    // Header navigation: each item links to a tool by its key (see config/tools.php). Draft tools are hidden.
    'site_nav_1_label' => 'PNG to JPG',
    'site_nav_1_tool' => 'png-to-jpg',
    'site_nav_2_label' => 'JPG to WebP',
    'site_nav_2_tool' => 'jpg-to-webp',
    'site_nav_3_label' => 'PNG to WebP',
    'site_nav_3_tool' => 'png-to-webp',
    'site_nav_4_label' => 'WebP to JPG',
    'site_nav_4_tool' => 'webp-to-jpg',
    'site_header_button' => 'Contact Us',
    'site_header_button_url' => '{url:pages.contact}',
    'site_menu_open' => 'Open menu',
    'site_menu_close' => 'Close menu',

    // Footer.
    'site_footer_tagline' => ['type' => 'textarea', 'value' => 'Free, browser-based image compression for JPG, PNG and WebP. No sign-up, no installs.'],
    'site_footer_badge_1' => 'Free to use',
    'site_footer_badge_2' => 'Private by design',
    'site_footer_badge_3' => 'No registration',
    'site_footer_tools_heading' => 'Tools',
    'site_footer_company_heading' => 'Company',
    'site_footer_company_1_label' => 'About Us',
    'site_footer_company_1_url' => '{url:pages.about}',
    'site_footer_company_2_label' => 'FAQ',
    'site_footer_company_2_url' => '{url:pages.faq}',
    'site_footer_company_3_label' => 'Editorial Policy',
    'site_footer_company_3_url' => '{url:pages.editorial}',
    'site_footer_company_4_label' => 'Contact Us',
    'site_footer_company_4_url' => '{url:pages.contact}',
    'site_footer_legal_heading' => 'Legal',
    'site_footer_legal_1_label' => 'Privacy Policy',
    'site_footer_legal_1_url' => '{url:pages.privacy-policy}',
    'site_footer_legal_2_label' => 'Terms & Conditions',
    'site_footer_legal_2_url' => '{url:pages.terms-and-conditions}',
    'site_footer_legal_3_label' => 'Cookie Policy',
    'site_footer_legal_3_url' => '{url:pages.cookie-policy}',
    'site_footer_legal_4_label' => 'Disclaimer',
    'site_footer_legal_4_url' => '{url:pages.disclaimer}',
    'site_footer_copyright' => '© {year} {operator}. All rights reserved.',
    'site_footer_note' => 'Images are never permanently stored.',
];
