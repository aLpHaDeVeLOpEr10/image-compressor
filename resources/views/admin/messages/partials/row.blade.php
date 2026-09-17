<a href="{{ route('admin.messages.show', $message) }}" class="flex items-start gap-4 border-b border-line px-5 py-4 last:border-b-0 hover:bg-canvas sm:px-6">
    <span @class([
        'flex size-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold',
        'bg-brand-50 text-brand-700' => ! $message->isRead(),
        'bg-canvas text-muted' => $message->isRead(),
    ])>{{ mb_strtoupper(mb_substr($message->name, 0, 1)) }}</span>
    <div class="min-w-0 flex-1">
        <div class="flex items-baseline justify-between gap-3">
            <p @class(['truncate text-sm text-ink', 'font-semibold' => ! $message->isRead(), 'font-medium' => $message->isRead()])>
                {{ $message->name }}
                @unless ($message->isRead())
                    <span class="ml-1.5 inline-block size-2 rounded-full bg-brand-600 align-middle"><span class="sr-only">Unread</span></span>
                @endunless
            </p>
            <time datetime="{{ $message->created_at->toIso8601String() }}" class="shrink-0 text-xs text-muted" title="{{ $message->created_at->toDayDateTimeString() }}">{{ $message->created_at->diffForHumans() }}</time>
        </div>
        <p class="mt-0.5 truncate text-sm text-body">{{ $message->subject }}</p>
        <p class="truncate text-xs text-muted">{{ $message->email }}</p>
    </div>
</a>
