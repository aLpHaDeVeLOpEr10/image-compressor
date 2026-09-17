<?php

namespace Tests\Feature;

use App\Models\Tool;
use App\Models\User;
use App\Tools\ToolRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminToolEditorTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'name' => 'PNG to JPG Converter',
            'slug' => 'png-to-jpg',
            'status' => Tool::STATUS_PUBLISHED,
            'meta_title' => 'Convert PNG to JPG Online Free',
            'meta_description' => 'Convert PNG images to JPG in your browser.',
            'fields' => [
                ['key' => 'h1', 'type' => 'text', 'value' => 'Turn PNG into JPG <b>fast</b>'],
                ['key' => 'banner_btn_label', 'type' => 'text', 'value' => 'Start converting'],
            ],
            ...$overrides,
        ];
    }

    public function test_opening_the_editor_creates_the_record_from_the_content_file(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/tools/png-to-jpg/edit')
            ->assertOk()
            ->assertSee('Convert PNG to JPG Online');

        $tool = Tool::where('key', 'png-to-jpg')->firstOrFail();

        $this->assertSame('png-to-jpg', $tool->slug);
        $keys = $tool->contentFields->pluck('key')->all();

        $this->assertSame(array_keys(app(ToolRegistry::class)->defaultFields('png-to-jpg')), $keys);
        $this->assertSame(['h1', 'intro', 'summary', 'figure_alt'], array_slice($keys, 0, 4));
        $this->assertContains('faq_6_answer', $keys);
        $this->assertContains('widget_choose_button', $keys);
        $this->assertNotContains('site_footer_note', $keys);
        $this->assertSame('Convert PNG to JPG Online', $tool->contentFields->first()->value);
    }

    public function test_converter_sections_benefits_and_faqs_come_from_saved_content(): void
    {
        $this->actingAs($this->admin)->get('/admin/tools/png-to-jpg/edit')->assertOk();

        $tool = Tool::where('key', 'png-to-jpg')->with('contentFields')->firstOrFail();
        $changes = [
            'why_heading' => 'Why switch to JPG?',
            'how_to_step_1_title' => 'Drop in a PNG',
            'details_body' => '<h3>Edited details</h3><p>New guidance.</p>',
            'benefit_1_title' => '',
            'faq_1_question' => 'Does JPG keep transparency?',
            'widget_choose_button' => 'Pick a PNG',
        ];

        $this->put('/admin/tools/png-to-jpg', [
            ...$this->payload(),
            'fields_json' => json_encode($tool->contentFields
                ->map(fn ($field) => ['key' => $field->key, 'type' => $field->type, 'value' => $changes[$field->key] ?? (string) $field->value])
                ->all()),
        ])->assertSessionHasNoErrors();

        $this->app->forgetInstance(ToolRegistry::class);

        $this->get('/tools/png-to-jpg')
            ->assertOk()
            ->assertSee('Why switch to JPG?')
            ->assertSee('Drop in a PNG')
            ->assertSee('<h3>Edited details</h3>', false)
            ->assertSee('Does JPG keep transparency?')
            ->assertSee('Pick a PNG')
            ->assertDontSee('Smaller photos')
            ->assertSee('Widely accepted')
            ->assertDontSee('Why convert PNG to JPG?');
    }

    public function test_unknown_tool_returns_not_found(): void
    {
        $this->actingAs($this->admin)->get('/admin/tools/not-a-tool/edit')->assertNotFound();
    }

    public function test_saved_content_is_stored_in_order_and_shown_on_the_public_page(): void
    {
        $this->actingAs($this->admin)
            ->put('/admin/tools/png-to-jpg', $this->payload())
            ->assertRedirect('/admin/tools/png-to-jpg/edit')
            ->assertSessionHasNoErrors();

        $tool = Tool::where('key', 'png-to-jpg')->firstOrFail();
        $this->assertSame(['h1', 'banner_btn_label'], $tool->contentFields->pluck('key')->all());
        $this->assertSame([0, 1], $tool->contentFields->pluck('position')->all());

        $this->app->forgetInstance(ToolRegistry::class);

        $this->get('/tools/png-to-jpg')
            ->assertOk()
            ->assertSee('<title>Convert PNG to JPG Online Free', false)
            ->assertSee('Turn PNG into JPG &lt;b&gt;fast&lt;/b&gt;', false)
            ->assertDontSee('Turn PNG into JPG <b>fast</b>', false);
    }

    public function test_content_field_keys_are_validated(): void
    {
        $this->actingAs($this->admin)
            ->put('/admin/tools/png-to-jpg', $this->payload(['fields' => [
                ['key' => 'Bad Key', 'type' => 'text', 'value' => 'x'],
                ['key' => 'same_key', 'type' => 'text', 'value' => 'x'],
                ['key' => 'same_key', 'type' => 'unknown', 'value' => 'x'],
            ]]))
            ->assertSessionHasErrors([
                'fields.0.key' => 'Keys must start with a letter and use only lowercase letters, numbers and underscores.',
                'fields.1.key' => 'This key is used more than once.',
                'fields.2.type',
            ]);

        $this->assertDatabaseCount('tool_content_fields', 0);
    }

    public function test_draft_tool_is_hidden_from_the_public_site(): void
    {
        $this->actingAs($this->admin)
            ->put('/admin/tools/png-to-jpg', $this->payload(['status' => Tool::STATUS_DRAFT]))
            ->assertSessionHasNoErrors();

        $this->app->forgetInstance(ToolRegistry::class);

        $this->get('/tools/png-to-jpg')->assertNotFound();
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('/tools/png-to-jpg');
    }

    public function test_homepage_tool_must_stay_published(): void
    {
        $this->actingAs($this->admin)
            ->put('/admin/tools/image-compressor', $this->payload(['slug' => null, 'status' => Tool::STATUS_DRAFT]))
            ->assertSessionHasErrors(['status' => 'The homepage tool must stay published.']);
    }

    public function test_slug_used_by_another_tool_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->put('/admin/tools/png-to-jpg', $this->payload(['slug' => 'webp-to-jpg']))
            ->assertSessionHasErrors(['slug' => 'Another tool already uses this slug.']);
    }

    public function test_blank_slug_is_generated_from_the_title_and_used_for_routes(): void
    {
        $this->actingAs($this->admin)
            ->put('/admin/tools/png-to-jpg', $this->payload(['slug' => '', 'name' => 'PNG to JPEG Converter']))
            ->assertSessionHasNoErrors();

        $this->assertSame(['png-to-jpg' => 'png-to-jpeg-converter'], ToolRegistry::routeSlugs());
    }

    public function test_share_image_can_be_uploaded_and_replaced(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)
            ->put('/admin/tools/png-to-jpg', $this->payload(['image' => UploadedFile::fake()->image('share.jpg', 1200, 630)]))
            ->assertSessionHasNoErrors();

        $firstImage = Tool::where('key', 'png-to-jpg')->value('og_image');
        Storage::disk('public')->assertExists($firstImage);

        $this->put('/admin/tools/png-to-jpg', $this->payload(['remove_image' => '1']))->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing($firstImage);
        $this->assertNull(Tool::where('key', 'png-to-jpg')->value('og_image'));
    }

    public function test_non_admin_cannot_update_tools(): void
    {
        $this->actingAs(User::factory()->create())
            ->put('/admin/tools/png-to-jpg', $this->payload())
            ->assertForbidden();

        $this->assertDatabaseCount('tools', 0);
    }
}
