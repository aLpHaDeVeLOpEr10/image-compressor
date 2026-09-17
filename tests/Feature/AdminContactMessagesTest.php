<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContactMessagesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_dashboard_shows_message_counts_and_recent_messages(): void
    {
        ContactMessage::factory()->count(2)->create();
        ContactMessage::factory()->read()->create(['subject' => 'Already handled']);

        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats) => $stats[0]['value'] === 3 && $stats[1]['value'] === 2)
            ->assertSee('Already handled');
    }

    public function test_messages_can_be_filtered_by_unread(): void
    {
        ContactMessage::factory()->create(['subject' => 'Needs a reply']);
        ContactMessage::factory()->read()->create(['subject' => 'Already handled']);

        $this->actingAs($this->admin)
            ->get('/admin/messages?filter=unread')
            ->assertOk()
            ->assertSee('Needs a reply')
            ->assertDontSee('Already handled');
    }

    public function test_messages_can_be_searched(): void
    {
        ContactMessage::factory()->create(['subject' => 'PNG transparency question']);
        ContactMessage::factory()->create(['subject' => 'WebP support']);

        $this->actingAs($this->admin)
            ->get('/admin/messages?search=transparency')
            ->assertOk()
            ->assertSee('PNG transparency question')
            ->assertDontSee('WebP support');
    }

    public function test_viewing_a_message_marks_it_as_read_and_escapes_content(): void
    {
        $message = ContactMessage::factory()->create(['message' => "<script>alert('xss')</script>"]);

        $this->actingAs($this->admin)
            ->get("/admin/messages/{$message->id}")
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee("<script>alert('xss')</script>", false);

        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_message_read_state_can_be_toggled(): void
    {
        $message = ContactMessage::factory()->read()->create();

        $this->actingAs($this->admin)
            ->patch("/admin/messages/{$message->id}/read")
            ->assertRedirect()
            ->assertSessionHas('status', 'Message marked as unread.');

        $this->assertNull($message->fresh()->read_at);
    }

    public function test_message_can_be_deleted(): void
    {
        $message = ContactMessage::factory()->create();

        $this->actingAs($this->admin)
            ->delete("/admin/messages/{$message->id}")
            ->assertRedirect('/admin/messages');

        $this->assertModelMissing($message);
    }

    public function test_non_admin_cannot_delete_messages(): void
    {
        $message = ContactMessage::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete("/admin/messages/{$message->id}")
            ->assertForbidden();

        $this->assertModelExists($message);
    }
}
