@php
    $brand = config('site.brand');
    $company = \App\Support\SiteIdentity::value('company_name') === config('site.brand') ? null : \App\Support\SiteIdentity::value('company_name');
    $country = \App\Support\SiteIdentity::value('country');
    $foundingYear = \App\Support\SiteIdentity::value('founding_year');
    $primary = app(\App\Tools\ToolRegistry::class)->primary();
@endphp

<x-layouts.app :seo="$seo">
    <x-ui.page-header
        title="About {{ $brand }}"
        description="A free, privacy-conscious image compressor, and the people and principles behind it."
        :breadcrumbs="$seo->breadcrumbs"
    />

    <x-ui.container class="py-12 sm:py-16">
        <div class="prose-content max-w-3xl">
            <x-content.summary title="{{ $brand }} in brief">
                {{ $brand }} is a free online image compressor for JPG, PNG and WebP files{{ $company ? ', operated by '.$company : '' }}{{ $country ? ' in '.$country : '' }}. It reduces file size, hits approximate target sizes from a few kilobytes to several megabytes, converts between formats, and runs the compression in your browser wherever possible.
            </x-content.summary>

            <h2 id="who-we-are">Who runs {{ $brand }}</h2>
            <p>
                {{ $brand }} is {{ $company ? 'operated by '.$company : 'an independent website' }}{{ $country ? ', based in '.$country : '' }}{{ $foundingYear ? ', and has been available since '.$foundingYear : '' }}.
                We build and maintain the compressor, write the help content on this site and answer messages sent through our <a href="{{ route('pages.contact') }}">contact page</a>.
            </p>
            <p>The site has no user accounts, no paid tier and no advertising. We do not sell or share personal information, as described in our <a href="{{ route('pages.privacy-policy') }}">privacy policy</a>.</p>

            <h2 id="what-we-do">What the tools do</h2>
            <p>The <a href="{{ $primary->url() }}">image compressor</a> reduces the file size of JPG, PNG and WebP images so they load faster, fit upload limits and are easier to share. You choose a quality level or an approximate target size, keep the format or convert it, compare the result with your original and download it, without an account or software to install.</p>
            <p>The one compressor covers every mode: <a href="{{ route('home') }}#jpg">JPG</a>, <a href="{{ route('home') }}#png">PNG</a> and <a href="{{ route('home') }}#webp">WebP</a> compression, and <a href="{{ route('home') }}#target-size">target sizes from 20KB to 2MB</a>. Our converters use the same engine with the output format pre-selected: <a href="{{ route('tools.png-to-jpg') }}">PNG to JPG</a>, <a href="{{ route('tools.jpg-to-webp') }}">JPG to WebP</a>, <a href="{{ route('tools.png-to-webp') }}">PNG to WebP</a> and <a href="{{ route('tools.webp-to-jpg') }}">WebP to JPG</a>.</p>

            <h2 id="how-it-works">How the compressor works</h2>
            <p>We keep images on your device wherever the browser can do the work:</p>
            <ul>
                <li><strong>JPG output</strong> is always encoded by your browser's built-in JPEG encoder. Transparent areas are filled with white, because JPG has no transparency.</li>
                <li><strong>WebP output</strong> is encoded by your browser when it supports WebP encoding, which current versions of Chrome, Edge and Firefox do.</li>
                <li><strong>PNG output</strong> below 100% quality uses our own in-browser encoder: it reduces the image to an indexed palette of 16 to 256 colours (median-cut quantization refined with k-means), keeps full and partial transparency, and writes a compact PNG. At 100% the PNG is saved losslessly.</li>
                <li><strong>Server fallback:</strong> only when a browser cannot produce WebP or PNG is the image sent over HTTPS to our server. It is processed in memory with the PHP GD library, returned immediately and never written to disk or a database.</li>
            </ul>
            <p><strong>Target sizes.</strong> When you set a target, the tool first tries your chosen quality. If the file is too large, it searches for the highest quality that fits. If even the lowest quality is too large, it reduces the width and height step by step until the image fits or reaches a practical minimum. Results are approximate, and the result screen always says whether the target was reached and whether the dimensions changed. We count 1 KB as 1,024 bytes.</p>
            <p><strong>Metadata.</strong> Because images are re-encoded, embedded EXIF data such as camera details and GPS location is not carried into the compressed file.</p>
            <p><strong>Limits.</strong> Files up to {{ config('compressor.max_upload_mb') }} MB, one image at a time, up to {{ number_format(config('compressor.max_pixels') / 1_000_000) }} megapixels in the browser or {{ number_format(config('compressor.server_max_pixels') / 1_000_000) }} megapixels on the server. We do not currently offer batch compression, exact resizing, or AVIF, HEIC or GIF files.</p>

            <h2 id="how-we-test">How we test results</h2>
            <p>The compression service has an automated test suite. It checks that PNG, JPG and WebP outputs are valid images, that target sizes are met by lowering quality or dimensions, that transparent PNGs keep their transparency, that oversized or disguised non-image files are rejected, and that nothing is written to storage.</p>
            <p>The example images on our tool pages are generated from test images with the same quality-then-resize approach as the tool. The file sizes, dimensions and quality levels in their captions are measured values, not estimates. Browser encoders can produce slightly different sizes from our server encoder, which is why every result in the tool shows the real numbers for your own image.</p>

            <h2 id="help">Help and FAQ</h2>
            <p>Our tool pages and <a href="{{ route('pages.faq') }}">FAQ</a> explain image formats and compression in plain language. How we research, check and update them is described in our <a href="{{ route('pages.editorial') }}">editorial policy</a>.</p>

            <h2 id="contact">Get in touch</h2>
            <p>Found a problem, a mistake on a page or have a suggestion? Please tell us on our <a href="{{ route('pages.contact') }}">contact page</a>. Reports that include the image format, approximate file size, browser and device help us reproduce issues quickly.</p>
        </div>
    </x-ui.container>

    <x-tools.related-tools :tools="$relatedTools" title="Our most used tools" />
</x-layouts.app>
