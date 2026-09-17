<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_every_tool(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin/tools')
            ->assertOk()
            ->assertViewHas('tools', fn ($tools) => $tools->keys()->all() === config('tools.pages'))
            ->assertSee('PNG to JPG Converter')
            ->assertSee('/tools/png-to-jpg');
    }

    public function test_tools_can_be_filtered_by_category(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin/tools?category=convert')
            ->assertOk()
            ->assertViewHas('tools', fn ($tools) => ! $tools->has('image-compressor') && $tools->has('png-to-jpg'));
    }

    public function test_tools_can_be_searched(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin/tools?search=webp to jpg')
            ->assertOk()
            ->assertViewHas('tools', fn ($tools) => $tools->keys()->all() === ['webp-to-jpg']);
    }

    public function test_guest_cannot_view_tools(): void
    {
        $this->get('/admin/tools')->assertRedirect('/admin/login');
    }
}
