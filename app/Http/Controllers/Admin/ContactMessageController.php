<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public const FILTERS = ['all' => 'All', 'unread' => 'Unread', 'read' => 'Read'];

    public function index(Request $request): View
    {
        $filter = array_key_exists((string) $request->query('filter'), self::FILTERS) ? (string) $request->query('filter') : 'all';
        $search = trim((string) $request->query('search'));

        $messages = ContactMessage::query()
            ->when($filter === 'unread', fn (Builder $query) => $query->whereNull('read_at'))
            ->when($filter === 'read', fn (Builder $query) => $query->whereNotNull('read_at'))
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%")))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.messages.index', compact('messages', 'filter', 'search'));
    }

    public function show(ContactMessage $message): View
    {
        $message->markAsRead();

        return view('admin.messages.show', compact('message'));
    }

    public function toggleRead(ContactMessage $message): RedirectResponse
    {
        $message->isRead() ? $message->markAsUnread() : $message->markAsRead();

        return back()->with('status', $message->isRead() ? 'Message marked as read.' : 'Message marked as unread.');
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return to_route('admin.messages.index')->with('status', 'Message deleted.');
    }
}
