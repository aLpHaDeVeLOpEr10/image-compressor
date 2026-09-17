<?php

namespace Tests\Feature;

use App\Models\Tool;
use App\Models\User;
use App\Tools\ToolRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToolTrashTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    /**
     * Send an admin request, then reset the tool registry so the next request sees the change.
     */
    private function afterRequest(): void
    {
        $this->app->forgetInstance(ToolRegistry::class);
    }

    private function createSpanishVersion(string $parentKey, string $slug): void
    {
        $this->post("/admin/tools/{$parentKey}/children", [
            'locale' => 'es',
            'name' => 'Versión española',
            'slug' => $slug,
            'status' => Tool::STATUS_PUBLISHED,
            'meta_title' => 'Título',
            'meta_description' => 'Descripción',
        ])->assertSessionHasNoErrors();

        $this->afterRequest();
    }

    public function test_sidebar_shows_the_tools_menu_with_trash_count(): void
    {
        $this->get('/admin/tools')
            ->assertOk()
            ->assertSeeInOrder(['All tools', 'Add tool', 'Trash'])
            ->assertSee(route('admin.tools.trash'));
    }

    public function test_built_in_tool_can_be_trashed_and_restored_but_not_deleted_permanently(): void
    {
        $this->delete('/admin/tools/png-to-jpg')->assertRedirect('/admin/tools');
        $this->afterRequest();

        $this->assertSoftDeleted(Tool::withTrashed()->where('key', 'png-to-jpg')->firstOrFail());
        $this->get('/tools/png-to-jpg')->assertNotFound();
        $this->get('/sitemap.xml')->assertDontSee('/tools/png-to-jpg');
        $this->get('/admin/tools/png-to-jpg/edit')->assertNotFound();
        $this->get('/admin/tools')->assertDontSee('/tools/png-to-jpg');
        $this->get('/admin/tools/trash')->assertOk()->assertSee('PNG to JPG Converter')->assertSee('Restore only');

        $this->delete('/admin/tools/trash/png-to-jpg')->assertForbidden();

        $this->patch('/admin/tools/trash/png-to-jpg/restore')->assertRedirect('/admin/tools/trash');
        $this->afterRequest();

        $this->get('/tools/png-to-jpg')->assertOk();
        $this->get('/admin/tools/trash')->assertSee('The trash is empty');
    }

    public function test_homepage_tool_cannot_be_trashed(): void
    {
        $this->delete('/admin/tools/image-compressor')->assertForbidden();

        $this->assertSame(0, Tool::onlyTrashed()->count());
    }

    public function test_trashing_a_parent_trashes_and_restores_its_language_versions_with_it(): void
    {
        $this->createSpanishVersion('png-to-jpg', 'png-a-jpg');

        $this->delete('/admin/tools/png-to-jpg')->assertRedirect();
        $this->afterRequest();

        $this->assertSame(2, Tool::onlyTrashed()->count());
        $this->get('/es/png-a-jpg')->assertNotFound();
        $this->get('/admin/tools/trash')
            ->assertSee('With 1 language version')
            ->assertDontSee('Spanish version of');

        $this->patch('/admin/tools/trash/png-to-jpg/restore');
        $this->afterRequest();

        $this->assertSame(0, Tool::onlyTrashed()->count());
        $this->get('/es/png-a-jpg')->assertOk();
    }

    public function test_language_version_cannot_be_restored_while_its_parent_is_trashed(): void
    {
        $this->createSpanishVersion('png-to-jpg', 'png-a-jpg');
        $this->delete('/admin/tools/png-to-jpg');

        $this->patch('/admin/tools/trash/png-to-jpg--es/restore')
            ->assertRedirect('/admin/tools/trash')
            ->assertSessionHasErrors('restore');

        $this->assertSoftDeleted(Tool::withTrashed()->where('key', 'png-to-jpg--es')->firstOrFail());
    }

    public function test_trashed_language_version_can_be_deleted_permanently(): void
    {
        $this->createSpanishVersion('png-to-jpg', 'png-a-jpg');
        $child = Tool::where('key', 'png-to-jpg--es')->firstOrFail();

        $this->delete('/admin/tools/png-to-jpg--es');
        $this->get('/admin/tools/trash')->assertSee('Spanish version of PNG to JPG Converter')->assertSee('Delete permanently');

        $this->delete('/admin/tools/trash/png-to-jpg--es')->assertRedirect('/admin/tools/trash');

        $this->assertModelMissing($child);
        $this->assertDatabaseMissing('tool_content_fields', ['tool_id' => $child->id]);
    }

    public function test_sync_command_does_not_recreate_trashed_tools(): void
    {
        $this->delete('/admin/tools/png-to-jpg');
        $this->afterRequest();

        $this->artisan('tools:sync-content')->assertSuccessful();

        $this->assertSame(1, Tool::withTrashed()->where('key', 'png-to-jpg')->count());
        $this->assertSoftDeleted(Tool::withTrashed()->where('key', 'png-to-jpg')->firstOrFail());
    }
}
