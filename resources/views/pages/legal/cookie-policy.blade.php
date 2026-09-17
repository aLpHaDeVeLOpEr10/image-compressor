<x-layouts.prose :seo="$seo" :title="$title" description="Which cookies {{ config('site.brand') }} uses, why they are needed and how you can manage them." updated="2026-09-16">
    <p>This Cookie Policy explains how {{ config('site.brand') }} (<a href="{{ route('home') }}">{{ route('home') }}</a>) uses cookies. It should be read together with our <a href="{{ route('pages.privacy-policy') }}">Privacy Policy</a>.</p>

    <h2 id="what-are-cookies">What cookies are</h2>
    <p>Cookies are small text files that a website stores in your browser. They allow the site to remember information between page loads, such as keeping a form secure. Some cookies are essential for a website to work, while others are used for purposes such as analytics.</p>
    <p>Cookies set by the website you are visiting are called first-party cookies, while cookies set by another service are called third-party cookies. Session cookies are deleted when you close your browser, and persistent cookies remain until they expire or you delete them.</p>

    <h2 id="essential-cookies">Essential cookies</h2>
    <p>These cookies are strictly necessary for the website to function securely. They do not track you across other websites.</p>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Purpose</th>
                <th>Duration</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>XSRF-TOKEN</code></td>
                <td>Protects forms and requests against cross-site request forgery (CSRF).</td>
                <td>{{ config('session.lifetime') }} minutes</td>
            </tr>
            <tr>
                <td><code>{{ config('session.cookie') }}</code></td>
                <td>Maintains session state and security for forms, such as the contact form.</td>
                <td>Expires after {{ config('session.lifetime') }} minutes of inactivity or when you close your browser</td>
            </tr>
        </tbody>
    </table>

    <h2 id="analytics-cookies">Analytics cookies</h2>
    @if (config('site.analytics.ga4_id'))
        <p>We use Google Analytics to understand how visitors use the website. Google Analytics sets the following cookies, and we request IP anonymisation.</p>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Purpose</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>_ga</code></td>
                    <td>Distinguishes unique visitors for Google Analytics statistics.</td>
                    <td>Up to 2 years</td>
                </tr>
                <tr>
                    <td><code>_ga_&lt;container-id&gt;</code></td>
                    <td>Maintains session state for Google Analytics.</td>
                    <td>Up to 2 years</td>
                </tr>
            </tbody>
        </table>
    @else
        <p>We do not currently use analytics or tracking cookies. If we introduce them in the future, we will update this policy.</p>
    @endif

    <h2 id="preference-cookies">Preference cookies</h2>
    <p>We do not currently use preference cookies. We also do not use advertising cookies, and the website does not store data in your browser's local storage.</p>

    <h2 id="managing-cookies">How to manage cookies</h2>
    <p>Most browsers let you view, block and delete cookies through their settings. Check your browser's help pages for instructions. Please note that if you block essential cookies, parts of the website, such as the <a href="{{ route('pages.contact') }}">contact form</a>, may not work correctly.</p>

    <h2 id="changes">Changes to this policy</h2>
    <p>We may update this Cookie Policy if the cookies we use change. The "Last updated" date at the top of this page shows when it was last revised.</p>

    <h2 id="contact">Contact</h2>
    <p>If you have questions about our use of cookies, <x-legal.contact-line />.</p>
</x-layouts.prose>
