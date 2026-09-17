<x-layouts.prose :seo="$seo" :title="$title" description="The terms that apply when you use {{ config('site.brand') }} and its free image compression tools." updated="2026-09-16">
    <p>These Terms and Conditions ("Terms") govern your use of {{ config('site.brand') }}, available at <a href="{{ route('home') }}">{{ route('home') }}</a> and operated by {{ \App\Support\SiteIdentity::operatorName() }} ("we", "us", "our"). Please read them carefully.</p>

    <h2 id="contents">Contents</h2>
    <ul>
        <li><a href="#acceptance">Acceptance of these terms</a></li>
        <li><a href="#service">Description of the service</a></li>
        <li><a href="#user-responsibilities">User responsibilities</a></li>
        <li><a href="#prohibited-use">Prohibited use</a></li>
        <li><a href="#intellectual-property">Intellectual property</a></li>
        <li><a href="#uploaded-content">Uploaded content</a></li>
        <li><a href="#availability">Service availability</a></li>
        <li><a href="#warranties-liability">Disclaimer of warranties and limitation of liability</a></li>
        <li><a href="#changes">Changes to the service and these terms</a></li>
        <li><a href="#termination">Termination and suspension</a></li>
        <li><a href="#governing-law">Governing law</a></li>
        <li><a href="#contact">Contact</a></li>
    </ul>

    <h2 id="acceptance">Acceptance of these terms</h2>
    <p>By accessing or using the website, you agree to these Terms and to our <a href="{{ route('pages.privacy-policy') }}">Privacy Policy</a>, <a href="{{ route('pages.cookie-policy') }}">Cookie Policy</a> and <a href="{{ route('pages.disclaimer') }}">Disclaimer</a>. If you do not agree, please do not use the website.</p>

    <h2 id="service">Description of the service</h2>
    <p>{{ config('site.brand') }} provides free online tools for reducing the file size of images, along with information about image compression and formats. The <a href="{{ route('home') }}">image compressor</a> accepts JPG/JPEG, PNG and WebP images up to {{ config('compressor.max_upload_mb') }} MB, one image at a time.</p>
    <p>Depending on the output format, compression runs either in your browser or on our server. Images processed on our server are handled in memory and are not kept after the request. The <a href="{{ route('pages.privacy-policy') }}">Privacy Policy</a> explains this in more detail.</p>
    <p>No account or registration is required and the service is provided free of charge.</p>

    <h2 id="user-responsibilities">User responsibilities</h2>
    <p>When using the website, you agree to:</p>
    <ul>
        <li>use the service only for lawful purposes and in line with these Terms;</li>
        <li>only process images that you own or have permission to use;</li>
        <li>keep your own copies of original files, as we do not store or back up your images;</li>
        <li>check compressed results before relying on them.</li>
    </ul>

    <h2 id="prohibited-use">Prohibited use</h2>
    <p>You must not:</p>
    <ul>
        <li>upload or process illegal content, including content that is obscene, exploitative, defamatory or that promotes violence or unlawful activity;</li>
        <li>upload content that infringes the copyright, trademark, privacy or other rights of any person, or that you do not have the right to use;</li>
        <li>upload files containing malware, viruses or any other harmful code;</li>
        <li>abuse, overload or interfere with the website, its servers or networks;</li>
        <li>use bots, scripts or other automated means to scrape the website or to send bulk requests to the image processing endpoint;</li>
        <li>attempt to bypass rate limits, security measures or access controls;</li>
        <li>attempt to gain unauthorised access to any part of the website or its systems.</li>
    </ul>

    <h2 id="intellectual-property">Intellectual property</h2>
    <p>The website, including its design, text, graphics, logos, software and the {{ config('site.brand') }} name and brand, is owned by or licensed to {{ \App\Support\SiteIdentity::operatorName() }} and is protected by intellectual property laws. You may not copy, reproduce or redistribute it without our permission, except as allowed by law.</p>
    <p>You keep all rights to the images you process. We claim no ownership of your images or of the compressed results.</p>

    <h2 id="uploaded-content">Uploaded content</h2>
    <p>You are solely responsible for the images you choose to process and for how you use the results. By using the service, you confirm that you have the necessary rights to process those images. You grant us only the limited permission needed to process your image in order to return the compressed version to you. We do not review or monitor images and are not responsible for their content.</p>

    <h2 id="availability">Service availability</h2>
    <p>We aim to keep the website available, but we do not guarantee that it will be uninterrupted, error free or available at any particular time. We may limit usage, including through rate limits, and may carry out maintenance or updates that temporarily affect access.</p>

    <h2 id="warranties-liability">Disclaimer of warranties and limitation of liability</h2>
    <p>The service is provided free of charge on an "as is" and "as available" basis, without warranties of any kind, whether express or implied, including warranties of merchantability, fitness for a particular purpose and non-infringement. In particular, we do not guarantee that compression will reach a specific file size, that a result will be smaller than the original, or that visual quality will be unchanged.</p>
    <p>To the fullest extent permitted by law, {{ \App\Support\SiteIdentity::operatorName() }} will not be liable for any indirect, incidental, special, consequential or punitive damages, or for any loss of data, files, profits or business, arising from or related to your use of, or inability to use, the website. Nothing in these Terms excludes or limits any liability that cannot be excluded or limited under applicable law.</p>

    <h2 id="changes">Changes to the service and these terms</h2>
    <p>We may change, suspend or discontinue any part of the service at any time. We may also update these Terms from time to time. The "Last updated" date at the top of this page shows when they were last revised. Continuing to use the website after changes are published means you accept the updated Terms.</p>

    <h2 id="termination">Termination and suspension</h2>
    <p>We may restrict, suspend or block access to the website, including by IP address, if we reasonably believe you have breached these Terms, are misusing the service or are putting the service or other users at risk.</p>

    <h2 id="governing-law">Governing law</h2>
    @if (\App\Support\SiteIdentity::value('legal.jurisdiction'))
        <p>These Terms are governed by the laws of {{ \App\Support\SiteIdentity::value('legal.jurisdiction') }}. Any disputes arising from these Terms or your use of the website will be subject to the jurisdiction of the courts of {{ \App\Support\SiteIdentity::value('legal.jurisdiction') }}, unless applicable law requires otherwise.</p>
    @else
        <p>These Terms are governed by the laws of the country in which the operator of the website is established. Any disputes will be subject to the jurisdiction of its courts, unless applicable law requires otherwise.</p>
    @endif

    <h2 id="contact">Contact</h2>
    <p>If you have questions about these Terms, <x-legal.contact-line />.</p>
</x-layouts.prose>
