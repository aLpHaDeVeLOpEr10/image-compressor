<x-layouts.admin
    :title="$message->subject"
    description="Message from {{ $message->name }}, received {{ $message->created_at->diffForHumans() }}."
    :breadcrumbs="[
        ['label' => 'Messages', 'url' => route('admin.messages.index')],
        ['label' => $message->subject, 'url' => route('admin.messages.show', $message)],
    ]"
>
    <x-slot:actions>
        <x-ui.button :href="'mailto:'.$message->email.'?subject='.rawurlencode('Re: '.$message->subject)" variant="secondary" icon="reply">Reply by email</x-ui.button>
        <x-ui.button :href="route('admin.messages.index')" variant="secondary" icon="chevron-left">Back to messages</x-ui.button>
    </x-slot:actions>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <x-admin.card title="Message">
            <div class="text-sm leading-7 whitespace-pre-line text-body sm:text-base">{{ $message->message }}</div>
        </x-admin.card>

        <div class="space-y-6">
            <x-admin.card title="Details" description="Who sent it and from where.">
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="font-medium text-ink">From</dt>
                        <dd class="mt-0.5 text-body">{{ $message->name }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-ink">Email</dt>
                        <dd class="mt-0.5"><a href="mailto:{{ $message->email }}" class="link break-all">{{ $message->email }}</a></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-ink">Received</dt>
                        <dd class="mt-0.5 text-body"><time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->toDayDateTimeString() }}</time></dd>
                    </div>
                    @if ($message->ip_address)
                        <div>
                            <dt class="font-medium text-ink">IP address</dt>
                            <dd class="mt-0.5 text-body">{{ $message->ip_address }}</dd>
                        </div>
                    @endif
                    @if ($message->user_agent)
                        <div>
                            <dt class="font-medium text-ink">User agent</dt>
                            <dd class="mt-0.5 break-words text-muted">{{ $message->user_agent }}</dd>
                        </div>
                    @endif
                </dl>
            </x-admin.card>

            <x-admin.card title="Actions">
                <div class="grid gap-3">
                    <form method="POST" action="{{ route('admin.messages.toggle-read', $message) }}">
                        @csrf
                        @method('PATCH')
                        <x-ui.button type="submit" variant="secondary" icon="mail" class="w-full">{{ $message->isRead() ? 'Mark as unread' : 'Mark as read' }}</x-ui.button>
                    </form>
                    <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('Delete this message permanently?')">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="secondary" icon="trash" class="w-full border-danger-200 text-danger-700 hover:border-danger-200 hover:bg-danger-50">Delete message</x-ui.button>
                    </form>
                </div>
            </x-admin.card>
        </div>
    </div>
</x-layouts.admin>
