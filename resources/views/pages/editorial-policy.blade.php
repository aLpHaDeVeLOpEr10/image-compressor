<x-layouts.prose :seo="$seo" title="Editorial Policy" description="How {{ config('site.brand') }} researches, tests, reviews and updates its tool pages and help content." :updated="config('site.pages_updated_at')">
    <p>{{ config('site.brand') }} publishes practical explanations about image compression and file formats on each tool page and in its FAQ. This policy explains how that content is written and kept accurate. It applies to our <a href="{{ route('pages.faq') }}">FAQ</a> and the text on our tool pages.</p>

    <h2 id="purpose">Purpose</h2>
    <p>Our content exists to help people make images smaller without guesswork: choosing a format, picking a quality setting, meeting an upload limit or speeding up a website. We write for the person with the image in front of them, not for search engines, and we do not publish pages whose only purpose is to rank for a keyword.</p>

    <h2 id="accuracy">Accuracy and sources</h2>
    <ul>
        <li><strong>Behaviour of our tools</strong> is described from the actual code: how quality, target sizes, format conversion, transparency and the server fallback work. If the tool changes, the affected pages are updated.</li>
        <li><strong>Format facts</strong> such as how JPEG, PNG and WebP compress are based on the format specifications and the documentation of the encoders involved.</li>
        <li><strong>Numbers</strong> in example images are measured by running test images through the same compression approach the tool uses. We do not publish invented statistics, and we describe results that vary by image as approximate.</li>
        <li><strong>Browser support</strong> statements refer to current versions of major browsers at the time of the last update.</li>
    </ul>

    <h2 id="independence">Independence</h2>
    <p>We do not accept payment for mentions, reviews or links, and our pages do not contain affiliate links. When we compare formats or approaches, we describe trade-offs rather than promoting a particular product. We do not compare ourselves with named competitors unless we have tested them fairly and can show the method.</p>

    <h2 id="limitations">Honest limitations</h2>
    <p>Compression always involves trade-offs. We point out when a setting causes visible quality loss, when a target size cannot be reached without resizing, when a result can be larger than the original, and when another format or tool would serve you better, including features our tools do not offer.</p>

    <h2 id="updates">Reviews and updates</h2>
    <p>Tool pages show the date their content was last meaningfully updated. Minor corrections such as typos do not change the date. We review pages when browser support, format recommendations or our tools change.</p>

    <h2 id="ai">Use of AI tools</h2>
    <p>We may use software tools, including AI writing assistants, to help draft or edit text. Every published page is checked against how the tool actually behaves and against the sources above before it goes live, and we are responsible for its accuracy.</p>

    <h2 id="corrections">Corrections</h2>
    <p>If you find a mistake, please tell us through our <a href="{{ route('pages.contact') }}">contact page</a>. We review every report and correct confirmed errors promptly.</p>

    <p>Learn more about who runs the site and how the compressor works on our <a href="{{ route('pages.about') }}">About page</a>.</p>
</x-layouts.prose>
