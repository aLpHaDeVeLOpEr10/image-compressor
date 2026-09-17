New message from the {{ config('site.brand') }} contact form.

Name: {{ $contactMessage->name }}
Email: {{ $contactMessage->email }}
Subject: {{ $contactMessage->subject }}
Received: {{ $contactMessage->created_at }}

{{ $contactMessage->message }}
