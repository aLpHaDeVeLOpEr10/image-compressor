<?php

namespace Tests\Feature;

use App\Models\Tool;
use App\Models\User;
use App\Tools\ToolRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AddToolTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private string $viewsRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();

        // Generated Blade files go to a temporary view location, never into resources/views.
        $this->viewsRoot = storage_path('framework/testing/views-'.uniqid());
        config(['tools.content_views_path' => "{$this->viewsRoot}/tools/content"]);
        View::getFinder()->prependLocation($this->viewsRoot);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->viewsRoot);

        parent::tearDown();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function addTool(array $overrides = []): TestResponse
    {
        $response = $this->actingAs($this->admin)->post('/admin/tools', [
            'type' => 'parent',
            'name' => 'GIF Compressor',
            'slug' => 'gif-compressor',
            'blade_view' => 'gif-compressor',
            'status' => Tool::STATUS_PUBLISHED,
            'meta_title' => 'Compress GIF Images Online Free',
            'meta_description' => 'Reduce the size of GIF images in your browser.',
            ...$overrides,
        ]);

        $this->app->forgetInstance(ToolRegistry::class);

        return $response;
    }

    public function test_add_tool_form_renders(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/tools/create')
            ->assertOk()
            ->assertSee('Blade file name')
            ->assertSee('PNG to JPG Converter');
    }

    public function test_parent_tool_is_created_with_its_blade_file_and_served_publicly(): void
    {
        $this->addTool()
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/tools/gif-compressor/edit');

        $tool = Tool::where('key', 'gif-compressor')->with('contentFields')->firstOrFail();

        $this->assertNull($tool->parent_id);
        $this->assertSame('gif-compressor', $tool->slug);
        $this->assertSame('gif-compressor', $tool->blade_view);
        $this->assertSame(['h1', 'intro', 'summary', 'card_description', 'body_heading', 'body_html'], $tool->contentFields->pluck('key')->take(6)->all());
        $this->assertContains('widget_choose_button', $tool->contentFields->pluck('key')->all());

        $file = "{$this->viewsRoot}/tools/content/gif-compressor.blade.php";
        $this->assertFileExists($file);
        $this->assertStringContainsString("\$content['body_html']", File::get($file));

        $this->get('/tools/gif-compressor')
            ->assertOk()
            ->assertSee('<title>Compress GIF Images Online Free', false)
            ->assertSee('GIF Compressor')
            ->assertSee('About the GIF Compressor')
            ->assertSee('<link rel="canonical" href="'.url('/tools/gif-compressor').'"', false);

        $this->get('/sitemap.xml')->assertSee(url('/tools/gif-compressor'));
        $this->get('/admin/tools')->assertSee('Added in admin')->assertSee('/tools/gif-compressor');
        $this->get('/admin/tools/gif-compressor/edit')->assertOk()->assertSee('gif-compressor.blade.php');
    }

    public function test_blank_slug_and_blade_file_name_are_generated_from_the_title(): void
    {
        $this->addTool(['name' => 'HEIC to JPG Converter', 'slug' => '', 'blade_view' => ''])->assertSessionHasNoErrors();

        $tool = Tool::where('key', 'heic-to-jpg-converter')->firstOrFail();
        $this->assertSame('heic-to-jpg-converter', $tool->blade_view);
        $this->assertFileExists("{$this->viewsRoot}/tools/content/heic-to-jpg-converter.blade.php");
    }

    public function test_existing_blade_file_is_reused_and_not_overwritten(): void
    {
        File::ensureDirectoryExists("{$this->viewsRoot}/tools/content");
        File::put("{$this->viewsRoot}/tools/content/shared-view.blade.php", '<p>Hand-written content</p>');

        $this->addTool(['blade_view' => 'shared-view.blade.php'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', fn (string $status) => str_contains($status, 'existing Blade file'));

        $this->assertSame('<p>Hand-written content</p>', File::get("{$this->viewsRoot}/tools/content/shared-view.blade.php"));
        $this->get('/tools/gif-compressor')->assertOk()->assertSee('Hand-written content');
    }

    public function test_slug_and_blade_file_name_are_validated(): void
    {
        $this->addTool(['slug' => 'png-to-jpg'])->assertSessionHasErrors(['slug' => 'Another tool already uses this slug.']);
        $this->addTool(['blade_view' => '../../evil'])->assertSessionHasErrors('blade_view');

        $this->assertSame(0, Tool::count());
        $this->assertDirectoryDoesNotExist("{$this->viewsRoot}/tools/content");
    }

    public function test_draft_tool_is_not_public(): void
    {
        $this->addTool(['status' => Tool::STATUS_DRAFT])->assertSessionHasNoErrors();

        $this->get('/tools/gif-compressor')->assertNotFound();
        $this->get('/sitemap.xml')->assertDontSee('/tools/gif-compressor');
    }

    public function test_child_tool_can_be_added_from_the_same_form_and_uses_the_parent_view(): void
    {
        $this->addTool()->assertSessionHasNoErrors();

        $this->addTool(['type' => 'child', 'parent' => '', 'locale' => ''])
            ->assertSessionHasErrors(['parent' => 'Select the parent tool.', 'locale' => 'Select a language for the child tool.']);

        $this->addTool([
            'type' => 'child',
            'parent' => 'gif-compressor',
            'locale' => 'es',
            'name' => 'Compresor de GIF',
            'slug' => 'compresor-gif',
            'meta_title' => 'Comprimir GIF en línea',
            'meta_description' => 'Reduce el tamaño de tus GIF.',
        ])->assertRedirect('/admin/tools/gif-compressor--es/edit');

        $child = Tool::where('key', 'gif-compressor--es')->with('contentFields')->firstOrFail();
        $this->assertSame(Tool::where('key', 'gif-compressor')->firstOrFail()->contentFields()->count(), $child->contentFields->count());

        $this->get('/es/compresor-gif')
            ->assertOk()
            ->assertSee('<html lang="es"', false)
            ->assertSee('About the GIF Compressor');
    }

    public function test_added_tool_can_be_deleted_permanently_with_its_language_versions_but_keeps_its_blade_file(): void
    {
        $this->addTool();
        $this->addTool(['type' => 'child', 'parent' => 'gif-compressor', 'locale' => 'es', 'slug' => 'compresor-gif']);

        $this->delete('/admin/tools/gif-compressor')->assertRedirect('/admin/tools');
        $this->delete('/admin/tools/trash/gif-compressor')->assertRedirect('/admin/tools/trash');

        $this->assertSame(0, Tool::withTrashed()->count());
        $this->assertDatabaseCount('tool_content_fields', 0);
        $this->assertFileExists("{$this->viewsRoot}/tools/content/gif-compressor.blade.php");
    }
}
