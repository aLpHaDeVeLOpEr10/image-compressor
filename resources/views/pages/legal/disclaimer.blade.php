<x-layouts.prose :seo="$seo" :title="$title" description="Important limitations of the {{ config('site.brand') }} tools and the information published on this website." updated="2026-09-16">
    <p>The information and tools on {{ config('site.brand') }} (<a href="{{ route('home') }}">{{ route('home') }}</a>) are provided free of charge and on an "as is" basis. Please read this disclaimer before relying on the website.</p>

    <h2 id="purpose">General purpose</h2>
    <p>{{ config('site.brand') }} is intended to help you reduce the file size of images for general use, such as websites, email and sharing. It is a general-purpose optimisation tool and is not designed for archival, forensic, medical or other specialist uses where exact image fidelity matters.</p>

    <h2 id="results-vary">Results vary</h2>
    <p>How much an image can be compressed depends on its content, dimensions, format and the settings you choose. Two images of similar size can produce very different results.</p>

    <h2 id="file-sizes">File sizes are not guaranteed</h2>
    <p>Target file sizes are approximate. To reach a target, the <a href="{{ route('home') }}">image compressor</a> first lowers quality and then reduces image dimensions if needed. We cannot guarantee an exact file size, and in some cases the result may not be smaller than the original.</p>

    <h2 id="quality">Quality loss and your original files</h2>
    <p>Lossy compression changes visual quality, and reducing dimensions lowers resolution. Re-encoding also removes embedded metadata such as EXIF data. Always review the result and keep your original files, as we do not store or back up your images.</p>

    <h2 id="your-content">Your content</h2>
    <p>You are responsible for the images you process and for making sure you have the right to use them. See our <a href="{{ route('pages.terms-and-conditions') }}">Terms and Conditions</a> for details.</p>

    <h2 id="availability">Availability</h2>
    <p>We do not guarantee that the website will be available without interruption or free of errors. Features may change, be rate limited or be withdrawn at any time.</p>

    <h2 id="informational-content">Informational content</h2>
    <p>Explanations and tips on this website are for general information only. They are not professional, technical or legal advice, and they may not suit your particular situation. Consider seeking qualified advice before acting on them.</p>

    <h2 id="external-links">External links</h2>
    <p>The website may link to third-party websites. We do not control and are not responsible for their content, availability or privacy practices, and a link does not imply endorsement.</p>

    <h2 id="contact">Contact</h2>
    <p>If you have questions about this disclaimer, <x-legal.contact-line />.</p>
</x-layouts.prose>
