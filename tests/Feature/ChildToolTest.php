<?php

namespace Tests\Feature;

use App\Models\Tool;
use App\Models\User;
use App\Tools\ToolRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ChildToolTest extends TestCase
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
     */
    private function createChild(string $parentKey, array $overrides = []): TestResponse
    {
        $response = $this->actingAs($this->admin)->post("/admin/tools/{$parentKey}/children", [
            'locale' => 'es',
            'name' => 'Convertidor de PNG a JPG',
            'slug' => 'png-a-jpg',
            'status' => Tool::STATUS_PUBLISHED,
            'meta_title' => 'Convertir PNG a JPG gratis',
            'meta_description' => 'Convierte imágenes PNG a JPG en tu navegador.',
            ...$overrides,
        ]);

        $this->app->forgetInstance(ToolRegistry::class);

        return $response;
    }

    /**
     * Save new values for some of a tool's content keys through the editor.
     *
     * @param  array<string, string>  $changes
     */
    private function saveContent(Tool $tool, array $changes): void
    {
        $tool->load('contentFields');

        $this->actingAs($this->admin)->put("/admin/tools/{$tool->key}", [
            'name' => $tool->name,
            'slug' => $tool->slug,
            'status' => $tool->status,
            'meta_title' => $tool->meta_title,
            'meta_description' => $tool->meta_description,
            'fields_json' => json_encode($tool->contentFields
                ->map(fn ($field) => ['key' => $field->key, 'type' => $field->type, 'value' => $changes[$field->key] ?? (string) $field->value])
                ->all()),
        ])->assertSessionHasNoErrors();

        $this->app->forgetInstance(ToolRegistry::class);
    }

    public function test_create_child_form_offers_unused_languages(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/tools/png-to-jpg/children/create')
            ->assertOk()
            ->assertSee('Spanish (es)')
            ->assertSee('/{lang}/');
    }

    public function test_child_tool_copies_every_parent_key_and_value(): void
    {
        $this->actingAs($this->admin)->get('/admin/tools/png-to-jpg/edit')->assertOk();
        $parent = Tool::where('key', 'png-to-jpg')->with('contentFields')->firstOrFail();

        $this->createChild('png-to-jpg')
            ->assertRedirect('/admin/tools/png-to-jpg--es/edit')
            ->assertSessionHas('status');

        $child = Tool::where('key', 'png-to-jpg--es')->with('contentFields')->firstOrFail();

        $this->assertSame($parent->id, $child->parent_id);
        $this->assertSame('es', $child->locale);
        $this->assertSame('png-a-jpg', $child->slug);
        $this->assertSame(
            $parent->contentFields->map->only(['key', 'type', 'value', 'position'])->all(),
            $child->contentFields->map->only(['key', 'type', 'value', 'position'])->all(),
        );

        $this->get('/admin/tools/png-to-jpg--es/edit')->assertOk()->assertSee('Spanish version of PNG to JPG Converter');
    }

    public function test_child_page_is_served_in_its_language_and_linked_from_the_parent(): void
    {
        $this->createChild('png-to-jpg');
        $this->saveContent(Tool::where('key', 'png-to-jpg--es')->firstOrFail(), ['h1' => 'Convierte PNG a JPG en línea']);

        $this->get('/es/png-a-jpg')
            ->assertOk()
            ->assertSee('<html lang="es"', false)
            ->assertSee('<title>Convertir PNG a JPG gratis', false)
            ->assertSee('Convierte PNG a JPG en línea')
            ->assertSee('<link rel="canonical" href="'.url('/es/png-a-jpg').'"', false)
            ->assertSee('hreflang="en" href="'.url('/tools/png-to-jpg').'"', false);

        $this->get('/tools/png-to-jpg')
            ->assertOk()
            ->assertSee('<html lang="en"', false)
            ->assertSee('hreflang="es" href="'.url('/es/png-a-jpg').'"', false)
            ->assertSee('hreflang="x-default" href="'.url('/tools/png-to-jpg').'"', false)
            ->assertDontSee('Convierte PNG a JPG en línea');

        $this->get('/sitemap.xml')->assertOk()->assertSee(url('/es/png-a-jpg'));
    }

    public function test_language_switcher_links_every_published_version_and_marks_the_current_one(): void
    {
        $this->get('/tools/png-to-jpg')->assertOk()->assertDontSee('data-dropdown', false);

        $this->createChild('png-to-jpg');
        $this->createChild('png-to-jpg', ['locale' => 'fr', 'slug' => 'png-en-jpg', 'status' => Tool::STATUS_DRAFT]);

        $this->get('/es/png-a-jpg')
            ->assertOk()
            ->assertSee('data-dropdown', false)
            ->assertSee('href="'.url('/tools/png-to-jpg').'"', false)
            ->assertSeeInOrder(['href="'.url('/es/png-a-jpg').'"', 'aria-current="page"'], false)
            ->assertDontSee('/fr/png-en-jpg');
    }

    public function test_draft_child_is_not_public(): void
    {
        $this->createChild('png-to-jpg', ['status' => Tool::STATUS_DRAFT]);

        $this->get('/es/png-a-jpg')->assertNotFound();
        $this->get('/tools/png-to-jpg')->assertOk()->assertDontSee('hreflang="es"', false);
        $this->get('/sitemap.xml')->assertDontSee('/es/png-a-jpg');
    }

    public function test_homepage_child_without_slug_is_served_at_the_language_root_with_its_site_text(): void
    {
        $this->createChild('image-compressor', ['name' => 'Compresor de imágenes', 'slug' => ''])
            ->assertSessionHasNoErrors();

        $child = Tool::where('key', 'image-compressor--es')->firstOrFail();
        $this->assertNull($child->slug);

        $this->saveContent($child, ['site_footer_note' => 'Nunca guardamos tus imágenes.']);

        $this->get('/es')->assertOk()->assertSee('Nunca guardamos tus imágenes.');
        $this->get('/')->assertOk()->assertDontSee('Nunca guardamos tus imágenes.')->assertSee('Images are never permanently stored.');
    }

    public function test_each_language_can_be_added_once_per_tool(): void
    {
        $this->createChild('png-to-jpg')->assertSessionHasNoErrors();

        $this->createChild('png-to-jpg', ['slug' => 'otro'])
            ->assertSessionHasErrors(['locale' => 'This tool already has a version in that language.']);
    }

    public function test_slug_must_be_unique_within_a_language_only(): void
    {
        $this->createChild('png-to-jpg')->assertSessionHasNoErrors();

        $this->createChild('webp-to-jpg', ['slug' => 'png-a-jpg'])
            ->assertSessionHasErrors(['slug' => 'Another tool in this language already uses this slug.']);

        $this->createChild('webp-to-jpg', ['locale' => 'fr', 'slug' => 'png-a-jpg'])->assertSessionHasNoErrors();
    }

    public function test_child_can_be_trashed_on_its_own(): void
    {
        $this->createChild('png-to-jpg');
        $child = Tool::where('key', 'png-to-jpg--es')->firstOrFail();

        $this->delete('/admin/tools/png-to-jpg--es')->assertRedirect('/admin/tools/png-to-jpg/edit');
        $this->app->forgetInstance(ToolRegistry::class);

        $this->assertSoftDeleted($child);
        $this->assertNotSoftDeleted(Tool::where('key', 'png-to-jpg')->firstOrFail());
        $this->get('/es/png-a-jpg')->assertNotFound();
        $this->get('/tools/png-to-jpg')->assertOk()->assertDontSee('hreflang="es"', false);
    }

    public function test_unknown_language_route_is_not_found(): void
    {
        $this->get('/es/does-not-exist')->assertNotFound();
        $this->get('/xx/png-a-jpg')->assertNotFound();
    }
}
