<?php

namespace Tests\Feature;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    private array $valid = [
        'name' => 'Alex Doe',
        'email' => 'alex@example.com',
        'subject' => 'Question about PNG',
        'message' => 'Does the PNG compressor keep transparency?',
    ];

    public function test_valid_message_is_stored_without_sending_mail_by_default(): void
    {
        Mail::fake();

        $this->post('/contact-us', $this->valid)
            ->assertRedirect('/contact-us')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('contact_messages', ['email' => 'alex@example.com', 'subject' => 'Question about PNG']);
        Mail::assertNothingSent();
    }

    public function test_mail_is_sent_when_configured(): void
    {
        Mail::fake();
        config(['site.contact.send_mail' => true, 'site.contact.notify_email' => 'owner@example.com']);

        $this->post('/contact-us', $this->valid)->assertRedirect();

        Mail::assertSent(ContactMessageReceived::class, fn ($mail) => $mail->hasTo('owner@example.com'));
    }

    public function test_validation_errors(): void
    {
        $this->post('/contact-us', ['email' => 'not-an-email', 'message' => 'short'])
            ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_honeypot_blocks_bots(): void
    {
        $this->post('/contact-us', [...$this->valid, 'website' => 'http://spam.example'])->assertSessionHasErrors('website');

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_contact_form_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/contact-us', $this->valid);
        }

        $this->from('/contact-us')->post('/contact-us', $this->valid)->assertSessionHasErrors('form');
        $this->assertSame(5, ContactMessage::count());
    }

    public function test_form_includes_csrf_token(): void
    {
        $this->get('/contact-us')->assertSee('name="_token"', false);
    }
}
