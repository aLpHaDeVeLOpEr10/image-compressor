<?php

namespace Tests\Feature;

use App\Models\Tool;
use App\Models\User;
use App\Tools\ToolRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageContentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    /**
     * Open the homepage editor, change the given content values and save, as an admin would.
     *
     * @param  array<string, string>  $changes
     */
    private function saveHomepage(array $changes, string $metaTitle = 'Compress Images Online'): void
    {
        $this->actingAs($this->admin)->get('/admin/tools/image-compressor/edit')->assertOk();

        $record = Tool::where('key', 'image-compressor')->with('contentFields')->firstOrFail();
        $fields = $record->contentFields
            ->map(fn ($field) => ['key' => $field->key, 'type' => $field->type, 'value' => $changes[$field->key] ?? (string) $field->value])
            ->all();

        $this->put('/admin/tools/image-compressor', [
            'name' => $record->name,
            'status' => Tool::STATUS_PUBLISHED,
            'meta_title' => $metaTitle,
            'meta_description' => $record->meta_description,
            'fields_json' => json_encode($fields),
        ])->assertSessionHasNoErrors();

        $this->assertSame(count($fields), $record->contentFields()->count());

        $this->app->forgetInstance(ToolRegistry::class);
    }

    public function test_homepage_editor_lists_every_default_key_in_order(): void
    {
        $this->actingAs($this->admin)->get('/admin/tools/image-compressor/edit')->assertOk();

        $keys = Tool::where('key', 'image-compressor')->firstOrFail()->contentFields->pluck('key')->all();

        $this->assertSame(array_keys(app(ToolRegistry::class)->defaultFields('image-compressor')), $keys);
        $this->assertSame('h1', $keys[0]);
        $this->assertContains('faq_10_answer', $keys);
        $this->assertContains('widget_msg_failed', $keys);
        $this->assertContains('site_footer_copyright', $keys);
    }

    public function test_homepage_renders_default_content_with_placeholders_resolved(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('One compressor, every mode', $html);
        $this->assertStringContainsString('href="'.route('tools.png-to-jpg').'"', $html);
        $this->assertStringContainsString('Yes, up to '.config('compressor.max_upload_mb').' MB', $html);
        $this->assertStringContainsString('&copy; '.now()->year, htmlentities(html_entity_decode($html)));
        $this->assertStringNotContainsString('{url:', $html);
        $this->assertStringNotContainsString('{max_upload_mb}', $html);
    }

    public function test_saved_values_replace_homepage_text_widget_site_text_and_seo(): void
    {
        $this->saveHomepage([
            'h1' => 'Shrink Your Photos',
            'modes_heading' => 'Every compression mode',
            'faq_1_question' => 'How small can a photo get?',
            'widget_choose_button' => 'Pick a Photo',
            'widget_msg_failed' => 'Compression failed, sorry.',
            'site_footer_note' => 'Nothing is kept on our servers.',
            'schema_subcategory' => 'Photo optimisation',
        ], metaTitle: 'Shrink Photos Online Free');

        $this->get('/')
            ->assertOk()
            ->assertSee('<title>Shrink Photos Online Free', false)
            ->assertSee('Shrink Your Photos')
            ->assertSee('Every compression mode')
            ->assertSee('How small can a photo get?')
            ->assertSee('Pick a Photo')
            ->assertSee('"failed":"Compression failed, sorry."', false)
            ->assertSee('"applicationSubCategory":"Photo optimisation"', false)
            ->assertDontSee('One compressor, every mode');

        $this->get('/about-us')->assertOk()->assertSee('Nothing is kept on our servers.');
    }

    public function test_clearing_a_list_item_hides_it_and_text_values_are_escaped(): void
    {
        $this->saveHomepage([
            'h1' => '',
            'faq_10_question' => '',
            'hero_point_1' => '<script>alert("x")</script>',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Free Online Image Compressor')
            ->assertDontSee('Does the image compressor work on my phone?')
            ->assertSee('What is the maximum file size?')
            ->assertDontSee('<script>alert("x")</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_sync_command_saves_every_default_key_and_keeps_edited_values(): void
    {
        $homepage = Tool::factory()->create(['key' => 'image-compressor', 'slug' => null]);
        $homepage->contentFields()->create(['key' => 'h1', 'type' => 'text', 'value' => 'Edited heading', 'position' => 0]);

        $this->artisan('tools:sync-content')->assertSuccessful();

        $defaults = app(ToolRegistry::class)->defaultFields('image-compressor');
        $homepage->load('contentFields');

        $this->assertSame(count(config('tools.pages')), Tool::count());
        $this->assertSame(array_keys($defaults), $homepage->contentFields->pluck('key')->all());
        $this->assertSame('Edited heading', $homepage->contentFields->firstWhere('key', 'h1')->value);
        $this->assertSame($defaults['faq_1_question']['value'], $homepage->contentFields->firstWhere('key', 'faq_1_question')->value);

        $this->artisan('tools:sync-content')->assertSuccessful();
        $this->assertSame(count($defaults), $homepage->contentFields()->count());
    }

    public function test_missing_default_keys_can_be_added_without_duplicates(): void
    {
        $this->actingAs($this->admin)->get('/admin/tools/image-compressor/edit')->assertOk();

        $record = Tool::where('key', 'image-compressor')->firstOrFail();
        $record->contentFields()->whereIn('key', ['faq_2_question', 'site_skip_link'])->delete();

        $this->get('/admin/tools/image-compressor/edit')->assertSee('2 default keys');

        $this->post('/admin/tools/image-compressor/sync-defaults')
            ->assertRedirect('/admin/tools/image-compressor/edit')
            ->assertSessionHas('status', '2 default keys added.');

        $keys = $record->contentFields()->pluck('key');
        $this->assertCount(count(app(ToolRegistry::class)->defaultFields('image-compressor')), $keys);
        $this->assertSame($keys->unique()->count(), $keys->count());
        $this->assertSame(['faq_2_question', 'site_skip_link'], $keys->slice(-2)->values()->all());
    }
}
