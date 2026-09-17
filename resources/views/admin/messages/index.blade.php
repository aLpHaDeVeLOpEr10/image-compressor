<x-layouts.admin
    title="Messages"
    description="Everything sent through the contact form, newest first."
    :breadcrumbs="[['label' => 'Messages', 'url' => route('admin.messages.index')]]"
>
    <x-admin.card flush>
        <div class="flex flex-col gap-3 border-b border-line px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <nav aria-label="Filter messages" class="flex gap-1 rounded-lg bg-canvas p-1">
                @foreach (\App\Http\Controllers\Admin\ContactMessageController::FILTERS as $key => $label)
                    <a href="{{ route('admin.messages.index', array_filter(['filter' => $key === 'all' ? null : $key, 'search' => $search])) }}"
                       @class([
                           'rounded-md px-3.5 py-1.5 text-sm font-medium transition-colors',
                           'bg-surface text-ink shadow-card' => $filter === $key,
                           'text-muted hover:text-ink' => $filter !== $key,
                       ])
                       @if ($filter === $key) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('admin.messages.index') }}" class="relative sm:w-72" role="search">
                @if ($filter !== 'all')
                    <input type="hidden" name="filter" value="{{ $filter }}">
                @endif
                <label for="search" class="sr-only">Search messages</label>
                <x-ui.icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted" />
                <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Name, email or subject" class="form-input pl-10">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-canvas text-xs font-semibold tracking-wider text-muted uppercase">
                    <tr>
                        <th scope="col" class="px-5 py-3 sm:px-6">Sender</th>
                        <th scope="col" class="px-5 py-3">Subject</th>
                        <th scope="col" class="px-5 py-3">Status</th>
                        <th scope="col" class="px-5 py-3">Received</th>
                        <th scope="col" class="px-5 py-3 text-right sm:px-6"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($messages as $message)
                        <tr class="hover:bg-canvas">
                            <td class="px-5 py-4 sm:px-6">
                                <p @class(['text-ink', 'font-semibold' => ! $message->isRead(), 'font-medium' => $message->isRead()])>{{ $message->name }}</p>
                                <p class="text-xs text-muted">{{ $message->email }}</p>
                            </td>
                            <td class="max-w-xs px-5 py-4">
                                <a href="{{ route('admin.messages.show', $message) }}" class="block truncate text-body hover:text-brand-700">{{ $message->subject }}</a>
                            </td>
                            <td class="px-5 py-4">
                                @if ($message->isRead())
                                    <span class="inline-flex rounded-full bg-canvas px-2.5 py-0.5 text-xs font-medium text-muted ring-1 ring-line">Read</span>
                                @else
                                    <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-700 ring-1 ring-brand-200">Unread</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-muted">
                                <time datetime="{{ $message->created_at->toIso8601String() }}" title="{{ $message->created_at->toDayDateTimeString() }}">{{ $message->created_at->diffForHumans() }}</time>
                            </td>
                            <td class="px-5 py-4 text-right sm:px-6">
                                <x-ui.button :href="route('admin.messages.show', $message)" variant="secondary" class="px-3 py-1.5">Open</x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-14 text-center">
                                <p class="text-sm font-medium text-ink">No messages found</p>
                                <p class="mt-1 text-sm text-muted">Try a different filter or search term.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($messages->hasPages())
            <div class="border-t border-line px-5 py-4 sm:px-6">{{ $messages->links() }}</div>
        @endif
    </x-admin.card>
</x-layouts.admin>
