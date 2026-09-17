<x-layouts.prose :seo="$seo" :title="$title" description="How {{ config('site.brand') }} handles your images, contact messages and other information when you use this website." updated="2026-09-16">
    <p>This Privacy Policy explains what information {{ \App\Support\SiteIdentity::operatorName() }} ("we", "us", "our") collects when you use {{ config('site.brand') }} at <a href="{{ route('home') }}">{{ route('home') }}</a>, how we use it and the choices you have. We have designed the service to collect as little information as possible: there are no user accounts, no registration and no payments.</p>

    <h2 id="contents">Contents</h2>
    <ul>
        <li><a href="#information-we-collect">Information we collect</a></li>
        <li><a href="#uploaded-images">Uploaded images</a></li>
        <li><a href="#temporary-processing">Temporary processing</a></li>
        <li><a href="#cookies">Cookies</a></li>
        <li><a href="#analytics">Analytics</a></li>
        <li><a href="#contact-forms">Contact forms</a></li>
        <li><a href="#log-data">Log data</a></li>
        <li><a href="#third-party-services">Third-party services</a></li>
        <li><a href="#data-retention">Data retention</a></li>
        <li><a href="#your-rights">Your rights</a></li>
        <li><a href="#security">Security</a></li>
        <li><a href="#childrens-privacy">Children's privacy</a></li>
        <li><a href="#changes">Changes to this policy</a></li>
        <li><a href="#contact">Contact information</a></li>
    </ul>

    <h2 id="information-we-collect">Information we collect</h2>
    <p>Depending on how you use the website, we may process the following information:</p>
    <ul>
        <li><strong>Images you choose to compress.</strong> How these are handled depends on the output format, as described below.</li>
        <li><strong>Contact form submissions.</strong> Your name, email address, subject and message, together with your IP address and browser user agent.</li>
        <li><strong>Technical data.</strong> Information such as your IP address, the page requested, the time of the request and your browser user agent, which is recorded in standard server logs and used briefly for rate limiting.</li>
        <li><strong>Cookies.</strong> A small number of essential cookies and, where enabled, analytics cookies.</li>
    </ul>
    <p>We do not ask you to create an account and we do not collect payment information.</p>

    <h2 id="uploaded-images">Uploaded images</h2>
    <p>The <a href="{{ route('home') }}">image compressor</a> accepts JPG/JPEG, PNG and WebP files up to {{ config('compressor.max_upload_mb') }} MB, one image at a time. Where your image is processed depends on the output format you choose:</p>
    <ul>
        <li><strong>In modern browsers:</strong> compression to JPG, WebP and PNG runs entirely in your browser. Your image is not uploaded to our server.</li>
        <li><strong>Server fallback</strong> (only for WebP or PNG output in browsers that cannot encode those formats themselves): your image is sent over an encrypted HTTPS connection to our server, compressed there, and the result is returned to you immediately.</li>
    </ul>
    <p>Re-encoding an image removes embedded metadata such as EXIF data (which can include camera details, dates or location) in both cases. We do not look at, analyse, share or use your images for any purpose other than compressing them at your request.</p>

    <h2 id="temporary-processing">Temporary processing</h2>
    <p>When an image is compressed on our server, it is processed in memory using PHP's GD library. The application does not save the original or the compressed image to disk, does not write it to a database and does not keep it after the request has finished. The web server software may briefly hold the upload in a temporary file while the request is handled; this file is deleted automatically at the end of the request.</p>
    <p>You should still keep your own copy of any image you compress, as we cannot recover files for you.</p>

    <h2 id="cookies">Cookies</h2>
    <p>We use essential cookies to keep the website secure and to make forms work, for example to protect against cross-site request forgery. These cookies are necessary for the site to function. For full details, including cookie names and durations, please see our <a href="{{ route('pages.cookie-policy') }}">Cookie Policy</a>.</p>

    <h2 id="analytics">Analytics</h2>
    @if (config('site.analytics.ga4_id'))
        <p>We use Google Analytics to understand how visitors use the website, such as which pages are viewed and how people arrive at the site. Google Analytics uses cookies and collects information such as pages visited, approximate location, device and browser type. We request IP anonymisation. This information is processed by Google and helps us improve the website. You can block analytics cookies in your browser settings or by using Google's browser add-on for opting out of Google Analytics.</p>
    @else
        <p>We do not currently use analytics or tracking cookies on this website. If this changes, we will update this policy and our Cookie Policy.</p>
    @endif

    <h2 id="contact-forms">Contact forms</h2>
    <p>When you send us a message through our <a href="{{ route('pages.contact') }}">contact page</a>, we collect your name, email address, subject and message. We also record your IP address and browser user agent to help prevent spam and abuse. This information is stored in our database and, if email notifications are configured, may be forwarded by email to the site operator.</p>
    <p>We use this information only to read and respond to your message and to protect the form from misuse. We do not add you to a mailing list or use your details for marketing.</p>

    <h2 id="log-data">Log data</h2>
    <p>Like most websites, our web server and application may record log entries when you visit, such as your IP address, the URL requested, the date and time and your browser user agent. These logs are used to operate the service, diagnose errors and detect abuse. The image compression endpoint is rate limited per IP address; your IP address is used temporarily in our cache for this purpose.</p>

    <h2 id="third-party-services">Third-party services</h2>
    <ul>
        <li><strong>Hosting:</strong> the website is hosted by {{ \App\Support\SiteIdentity::value('legal.hosting_provider') ?? 'our hosting provider' }}, which processes technical data as needed to serve the website.</li>
        <li><strong>Fonts:</strong> fonts are self-hosted and bundled with the website, so no requests are made to third-party font services.</li>
        @if (config('site.analytics.ga4_id'))
            <li><strong>Google Analytics:</strong> used for website analytics as described above.</li>
        @endif
    </ul>
    <p>We do not sell your personal information and we do not share it with advertisers.</p>

    <h2 id="data-retention">Data retention</h2>
    <ul>
        <li><strong>Images:</strong> not retained. Images processed in your browser never reach us, and images processed on our server are discarded at the end of the request.</li>
        <li><strong>Contact messages:</strong> kept for as long as needed to respond to and handle your request. @if (\App\Support\SiteIdentity::value('legal.contact_retention')) We aim to delete them within {{ \App\Support\SiteIdentity::value('legal.contact_retention') }}. @endif</li>
        <li><strong>Rate limiting data:</strong> held briefly in cache and expires automatically.</li>
        <li><strong>Server logs:</strong> kept {{ \App\Support\SiteIdentity::value('legal.log_retention') ? 'for up to '.\App\Support\SiteIdentity::value('legal.log_retention') : 'only as long as needed' }} for security and troubleshooting, then deleted.</li>
    </ul>

    <h2 id="your-rights">Your rights</h2>
    <p>Depending on where you live, you may have rights over your personal information, such as the right to:</p>
    <ul>
        <li>ask whether we hold personal information about you and request a copy;</li>
        <li>ask us to correct inaccurate information;</li>
        <li>ask us to delete your information;</li>
        <li>object to or ask us to restrict certain processing;</li>
        <li>withdraw consent where processing is based on consent;</li>
        <li>complain to your local data protection authority.</li>
    </ul>
    <p>We do not sell personal information. To make a request, contact us using the details below. Because we do not operate user accounts, we may ask for information (such as the email address you used in a contact form) to locate your data.</p>

    <h2 id="security">Security</h2>
    <p>We take reasonable measures to protect information, including encrypted HTTPS connections, protection against cross-site request forgery, rate limiting and not storing uploaded images. No method of transmission or storage is completely secure, however, so we cannot guarantee absolute security.</p>

    <h2 id="childrens-privacy">Children's privacy</h2>
    <p>{{ config('site.brand') }} is not directed at children, and we do not knowingly collect personal information from children. If you believe a child has sent us personal information through our contact form, please contact us and we will delete it.</p>

    <h2 id="changes">Changes to this policy</h2>
    <p>We may update this Privacy Policy from time to time, for example if the service changes. The "Last updated" date at the top of this page shows when it was last revised. Please review it periodically.</p>

    <h2 id="contact">Contact information</h2>
    <p>If you have questions about this Privacy Policy or want to exercise your rights, <x-legal.contact-line :who="\App\Support\SiteIdentity::operatorName()" />.</p>
</x-layouts.prose>
