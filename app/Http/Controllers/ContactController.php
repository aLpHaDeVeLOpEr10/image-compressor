<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Support\Seo\Seo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class ContactController extends Controller
{
    public function show(): View
    {
        $seo = Seo::make('Contact PicsCompressor', 'Contact the PicsCompressor team about the free online image compressor: report a bug, suggest a feature or ask a privacy question.')
            ->withBreadcrumbs($this->breadcrumbs(['Contact Us' => route('pages.contact')]))
            ->asPage('ContactPage');
        $seo->canonical = route('pages.contact');

        return view('pages.contact', compact('seo'));
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        $message = ContactMessage::create([
            ...$request->safe()->only(['name', 'email', 'subject', 'message']),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
        ]);

        $recipient = config('site.contact.notify_email');

        if (config('site.contact.send_mail') && $recipient) {
            try {
                Mail::to($recipient)->send(new ContactMessageReceived($message));
            } catch (Throwable $e) {
                report($e);
            }
        }

        return to_route('pages.contact')
            ->with('status', 'Thank you for your message. It has been received and will be read by the PicsCompressor team.');
    }
}
