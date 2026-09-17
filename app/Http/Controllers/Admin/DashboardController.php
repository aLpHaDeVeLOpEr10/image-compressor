<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Tools\ToolRegistry;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(ToolRegistry $tools): View
    {
        $stats = [
            ['label' => 'Total messages', 'value' => ContactMessage::count(), 'icon' => 'mail'],
            ['label' => 'Unread messages', 'value' => ContactMessage::unread()->count(), 'icon' => 'info'],
            ['label' => 'Messages, last 7 days', 'value' => ContactMessage::where('created_at', '>=', now()->subDays(7))->count(), 'icon' => 'clipboard'],
            ['label' => 'Live tools', 'value' => $tools->all()->count(), 'icon' => 'layers'],
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentMessages' => ContactMessage::latest()->limit(5)->get(),
            'tools' => $tools->all(),
        ]);
    }
}
