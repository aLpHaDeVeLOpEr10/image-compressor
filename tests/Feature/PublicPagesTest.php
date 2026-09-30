<?php

namespace Tests\Feature;

use App\Support\Figures;
use App\Tools\ToolPage;
use App\Tools\ToolRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public static function pages(): array
    {
        $tools = ['png-to-jpg', 'jpg-to-webp', 'png-to-webp', 'webp-to-jpg'];

        return collect([
            '/', ...array_map(fn (string $tool) => "/tools/{$tool}", $tools), '/about-us', '/editorial-policy', '/contact-us', '/faq', '/privacy-policy', '/terms-and-conditions',
            '/cookie-policy', '/disclaimer',
        ])->mapWithKeys(fn (string $path) => [$path => [$path]])->all();
    }

    #[DataProvider('pages')]
    public function test_page_renders_with_seo_essentials(string $path): void
    {
        config(['site.indexable' => true]);

        $html = $this->get($path)->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'), 'Exactly one h1 expected');
        $this->assertMatchesRegularExpression('#<title>[^<]{10,}</title>#', $html);
        $this->assertStringContainsString('<meta name="description"', $html);
        $this->assertStringContainsString('<link rel="canonical" href="'.url($path === '/' ? '' : $path).'"', $html);
        $this->assertStringContainsString('max-image-preview:large', $html);
        $this->assertStringContainsString('property="og:image:alt"', $html);
        $this->assertSame(1, substr_count($html, 'application/ld+json'), 'One JSON-LD graph expected');

        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $match);
        $types = collect(json_decode($match[1], true)['@graph'])->pluck('@type');
        $this->assertContains('Organization', $types);
        $this->assertContains('WebSite', $types);
    }

    public function test_tool_pages_have_unique_titles_descriptions_and_existing_figures(): void
    {
        $tools = app(ToolRegistry::class)->all();

        $this->assertSame($tools->count(), $tools->pluck('title')->unique()->count(), 'Tool titles must be unique');
        $this->assertSame($tools->count(), $tools->pluck('description')->unique()->count(), 'Tool descriptions must be unique');
        $this->assertSame($tools->count(), $tools->pluck('h1')->unique()->count(), 'Tool H1s must be unique');

        $tools->each(function (ToolPage $tool) {
            $this->assertNotSame('', $tool->summary, "{$tool->key} needs a summary");
            $this->assertNotNull(Figures::get($tool->figure), "{$tool->key} references a missing figure");
            $this->assertFileExists(public_path(Figures::get($tool->figure)['src']));
        });
    }

    public function test_homepage_is_the_image_compressor_tool(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-compressor', false)
            ->assertSee('"@type":"WebApplication"', false)
            ->assertSee('"url":"'.route('home').'"', false)
            ->assertSee('id="jpg"', false)
            ->assertSee('id="png"', false)
            ->assertSee('id="webp"', false)
            ->assertSee('id="target-size"', false)
            ->assertSee(url('/tools/png-to-jpg'));

        $this->get('/sitemap.xml')->assertDontSee('/tools/image-compressor');
    }

    public function test_converter_page_describes_the_application_entity(): void
    {
        $this->get('/tools/png-to-jpg')
            ->assertOk()
            ->assertSee('"@type":"WebApplication"', false)
            ->assertSee('"name":"CompressPix PNG to JPG Converter"', false)
            ->assertSee('"output":"image\/jpeg"', false)
            ->assertSee('<figure', false)
            ->assertSee('Short answer');
    }

    public function test_converter_pages_only_accept_their_own_input_format(): void
    {
        $pngToJpg = $this->get('/tools/png-to-jpg')
            ->assertOk()
            ->assertSee('accept=".png,image/png"', false)
            ->assertSee('Supported:</span> PNG', false)
            ->getContent();

        $this->assertSame(['image/png'], $this->widgetSettings($pngToJpg)['input']);
        $this->assertSame('PNG', $this->widgetSettings($pngToJpg)['inputLabel']);

        $jpgToWebp = $this->get('/tools/jpg-to-webp')
            ->assertOk()
            ->assertSee('accept=".jpg,.jpeg,image/jpeg"', false)
            ->assertSee('Supported:</span> JPG, JPEG', false)
            ->getContent();

        $this->assertSame(['image/jpeg'], $this->widgetSettings($jpgToWebp)['input']);

        $homepage = $this->get('/')
            ->assertOk()
            ->assertSee('accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"', false)
            ->assertSee('Supported:</span> JPG, JPEG, PNG, WebP', false)
            ->getContent();

        $this->assertSame(['image/jpeg', 'image/png', 'image/webp'], $this->widgetSettings($homepage)['input']);
    }

    /**
     * The compressor widget's settings, read back from the data-settings attribute on the rendered page.
     *
     * @return array<string, mixed>
     */
    private function widgetSettings(string $html): array
    {
        $this->assertSame(1, preg_match("/data-settings='([^']+)'/", $html, $match), 'Widget settings expected on the page');

        return json_decode(html_entity_decode($match[1], ENT_QUOTES), true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_only_the_merged_compressor_and_converters_exist(): void
    {
        foreach (['/tools/image-compressor', '/tools/compress-jpg', '/tools/compress-png', '/tools/compress-webp', '/tools/compress-image-to-100kb'] as $path) {
            $this->get($path)->assertNotFound();
        }
    }

    public function test_blog_no_longer_exists(): void
    {
        $this->get('/blog')->assertNotFound();
        $this->get('/blog/jpg-vs-png-vs-webp')->assertNotFound();
        $this->get('/sitemap.xml')->assertDontSee('/blog');
    }

    public function test_sitemap_lists_indexable_urls_with_lastmod_and_images(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(url('/tools/webp-to-jpg'))
            ->assertSee(url('/privacy-policy'))
            ->assertSee('<lastmod>', false)
            ->assertSee('<image:loc>'.asset('images/examples/png-to-jpg-transparency.webp').'</image:loc>', false)
            ->assertDontSee('<loc>'.url('/sitemap').'</loc>', false)
            ->assertDontSee('process/compress');
    }

    public function test_robots_txt_and_headers_follow_indexable_setting(): void
    {
        config(['site.indexable' => true]);
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /process/')
            ->assertSee('Disallow: /up')
            ->assertSee('Sitemap: '.url('/sitemap.xml'));
        $this->get('/')->assertHeaderMissing('X-Robots-Tag');

        config(['site.indexable' => false]);
        $this->get('/robots.txt')->assertDontSee("Disallow: /\n", false);
        $this->get('/')->assertHeader('X-Robots-Tag', 'noindex, nofollow')->assertSee('noindex, nofollow', false);
    }

    public function test_llms_txt_lists_tools_and_pages(): void
    {
        $this->get('/llms.txt')
            ->assertOk()
            ->assertSee('# CompressPix')
            ->assertSee(url('/tools/png-to-jpg'))
            ->assertSee(url('/faq'))
            ->assertDontSee('/blog');
    }

    public function test_placeholder_identity_values_are_never_rendered(): void
    {
        config([
            'site.company_name' => '[Company Name]',
            'site.legal.jurisdiction' => '[Jurisdiction]',
        ]);

        foreach (['/privacy-policy', '/terms-and-conditions', '/contact-us', '/about-us', '/cookie-policy'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertDontSee('[Company Name]')
                ->assertDontSee('[Jurisdiction]')
                ->assertDontSee('[Hosting Provider]')
                ->assertDontSee('example.com');
        }
    }

    public function test_configured_identity_appears_in_legal_pages_and_structured_data(): void
    {
        config(['site.company_name' => 'Pixel Works Ltd', 'site.legal.jurisdiction' => 'England and Wales']);

        $this->get('/terms-and-conditions')->assertSee('Pixel Works Ltd')->assertSee('laws of England and Wales');
        $this->get('/')->assertSee('"legalName":"Pixel Works Ltd"', false)->assertDontSee('mailto:', false);
    }

    public function test_launch_check_fails_until_the_site_is_configured(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000', 'site.indexable' => false, 'site.company_name' => null]);
        $this->artisan('site:launch-check')->assertFailed();

        config([
            'app.url' => 'https://compresspix.com',
            'app.debug' => false,
            'site.indexable' => true,
            'site.company_name' => 'Pixel Works Ltd',
            'site.country' => 'United Kingdom',
            'site.legal.hosting_provider' => 'Example Hosting',
            'site.legal.jurisdiction' => 'England and Wales',
            'site.legal.contact_retention' => '12 months',
            'site.legal.log_retention' => '30 days',
        ]);
        $this->artisan('site:launch-check')->assertSuccessful();
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get('/')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_admin_area_is_not_publicly_accessible(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }
}
