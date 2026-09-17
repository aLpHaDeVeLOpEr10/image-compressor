@props(['name', 'strokeWidth' => 1.75])

<svg {{ $attributes->merge(['class' => 'size-5 shrink-0']) }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $strokeWidth }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($name)
        @case('upload')
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><path d="m17 8-5-5-5 5" /><path d="M12 3v12" />
            @break
        @case('download')
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><path d="m7 10 5 5 5-5" /><path d="M12 15V3" />
            @break
        @case('image')
            <rect width="18" height="18" x="3" y="3" rx="2" /><circle cx="9" cy="9" r="2" /><path d="m21 15-3.09-3.09a2 2 0 0 0-2.82 0L6 21" />
            @break
        @case('images')
            <path d="M18 22H4a2 2 0 0 1-2-2V6" /><path d="m22 13-1.3-1.3a2.4 2.4 0 0 0-3.4 0L11 18" /><circle cx="12" cy="8" r="2" /><rect width="16" height="16" x="6" y="2" rx="2" />
            @break
        @case('check')
            <path d="M20 6 9 17l-5-5" />
            @break
        @case('check-circle')
            <circle cx="12" cy="12" r="10" /><path d="m9 12 2 2 4-4" />
            @break
        @case('x')
            <path d="M18 6 6 18" /><path d="m6 6 12 12" />
            @break
        @case('menu')
            <path d="M4 6h16" /><path d="M4 12h16" /><path d="M4 18h16" />
            @break
        @case('chevron-down')
            <path d="m6 9 6 6 6-6" />
            @break
        @case('chevron-right')
            <path d="m9 18 6-6-6-6" />
            @break
        @case('arrow-right')
            <path d="M5 12h14" /><path d="m12 5 7 7-7 7" />
            @break
        @case('zap')
            <path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z" />
            @break
        @case('shield')
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /><path d="m9 12 2 2 4-4" />
            @break
        @case('lock')
            <rect width="18" height="11" x="3" y="11" rx="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" />
            @break
        @case('smartphone')
            <rect width="14" height="20" x="5" y="2" rx="2" /><path d="M12 18h.01" />
            @break
        @case('sliders')
            <path d="M4 21v-7" /><path d="M4 10V3" /><path d="M12 21v-9" /><path d="M12 8V3" /><path d="M20 21v-5" /><path d="M20 12V3" /><path d="M2 14h4" /><path d="M10 8h4" /><path d="M18 16h4" />
            @break
        @case('target')
            <circle cx="12" cy="12" r="10" /><circle cx="12" cy="12" r="6" /><circle cx="12" cy="12" r="2" />
            @break
        @case('refresh')
            <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" /><path d="M3 3v5h5" /><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16" /><path d="M16 16h5v5" />
            @break
        @case('alert')
            <circle cx="12" cy="12" r="10" /><path d="M12 8v4" /><path d="M12 16h.01" />
            @break
        @case('info')
            <circle cx="12" cy="12" r="10" /><path d="M12 16v-4" /><path d="M12 8h.01" />
            @break
        @case('file')
            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" /><path d="M14 2v6h6" />
            @break
        @case('mail')
            <rect width="20" height="16" x="2" y="4" rx="2" /><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
            @break
        @case('database')
            <ellipse cx="12" cy="5" rx="9" ry="3" /><path d="M3 5v14a9 3 0 0 0 18 0V5" /><path d="M3 12a9 3 0 0 0 18 0" />
            @break
        @case('share')
            <circle cx="18" cy="5" r="3" /><circle cx="6" cy="12" r="3" /><circle cx="18" cy="19" r="3" /><path d="m8.59 13.51 6.83 3.98" /><path d="m15.41 6.51-6.82 3.98" />
            @break
        @case('globe')
            <circle cx="12" cy="12" r="10" /><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20" /><path d="M2 12h20" />
            @break
        @case('tag')
            <path d="M12 2H2v10l9.29 9.29a2.41 2.41 0 0 0 3.42 0l6.58-6.58a2.41 2.41 0 0 0 0-3.42L12 2z" /><path d="M7 7h.01" />
            @break
        @case('user-x')
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="m17 8 5 5" /><path d="m22 8-5 5" />
            @break
        @case('layers')
            <path d="m12 2 10 5-10 5L2 7l10-5z" /><path d="m2 17 10 5 10-5" /><path d="m2 12 10 5 10-5" />
            @break
        @case('search')
            <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />
            @break
        @case('columns')
            <rect width="18" height="18" x="3" y="3" rx="2" /><path d="M12 3v18" />
            @break
        @case('minimize')
            <path d="M8 3v3a2 2 0 0 1-2 2H3" /><path d="M21 8h-3a2 2 0 0 1-2-2V3" /><path d="M3 16h3a2 2 0 0 1 2 2v3" /><path d="M16 21v-3a2 2 0 0 1 2-2h3" />
            @break
        @case('book')
            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" /><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
            @break
        @case('clipboard')
            <rect width="8" height="4" x="8" y="2" rx="1" /><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
            @break
        @case('eye-off')
            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" /><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" /><path d="M6.61 6.61A13.53 13.53 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" /><path d="m2 2 20 20" />
            @break
        @case('gauge')
            <path d="m12 14 4-4" /><path d="M3.34 19a10 10 0 1 1 17.32 0" />
            @break
        @case('eye')
            <path d="M2.06 12.35a1 1 0 0 1 0-.7 10.75 10.75 0 0 1 19.88 0 1 1 0 0 1 0 .7 10.75 10.75 0 0 1-19.88 0" /><circle cx="12" cy="12" r="3" />
            @break
        @case('pencil')
            <path d="M21.17 6.81a1 1 0 0 0-3.98-3.98L3.84 16.17a2 2 0 0 0-.5.83l-1.32 4.35a.5.5 0 0 0 .62.62l4.35-1.32a2 2 0 0 0 .83-.5z" /><path d="m15 5 4 4" />
            @break
        @case('settings')
            <path d="M9.67 4.14a2.3 2.3 0 0 1 4.66 0 2.3 2.3 0 0 0 3.26 1.88 2.3 2.3 0 0 1 2.33 4.04 2.3 2.3 0 0 0 0 3.76 2.3 2.3 0 0 1-2.33 4.04 2.3 2.3 0 0 0-3.26 1.88 2.3 2.3 0 0 1-4.66 0 2.3 2.3 0 0 0-3.26-1.88 2.3 2.3 0 0 1-2.33-4.04 2.3 2.3 0 0 0 0-3.76 2.3 2.3 0 0 1 2.33-4.04 2.3 2.3 0 0 0 3.26-1.88" /><circle cx="12" cy="12" r="3" />
            @break
        @case('grip')
            <circle cx="9" cy="5" r="1" /><circle cx="9" cy="12" r="1" /><circle cx="9" cy="19" r="1" /><circle cx="15" cy="5" r="1" /><circle cx="15" cy="12" r="1" /><circle cx="15" cy="19" r="1" />
            @break
        @case('plus')
            <path d="M5 12h14" /><path d="M12 5v14" />
            @break
        @case('layout-grid')
            <rect width="7" height="7" x="3" y="3" rx="1" /><rect width="7" height="7" x="14" y="3" rx="1" /><rect width="7" height="7" x="14" y="14" rx="1" /><rect width="7" height="7" x="3" y="14" rx="1" />
            @break
        @case('panel-left')
            <rect width="18" height="18" x="3" y="3" rx="2" /><path d="M9 3v18" />
            @break
        @case('bell')
            <path d="M10.27 21a2 2 0 0 0 3.46 0" /><path d="M3.26 15.33A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.67C19.41 13.96 18 12.5 18 8A6 6 0 0 0 6 8c0 4.5-1.41 5.96-2.74 7.33" />
            @break
        @case('log-out')
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><path d="m16 17 5-5-5-5" /><path d="M21 12H9" />
            @break
        @case('external-link')
            <path d="M15 3h6v6" /><path d="M10 14 21 3" /><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
            @break
        @case('trash')
            <path d="M3 6h18" /><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" /><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" /><path d="M10 11v6" /><path d="M14 11v6" />
            @break
        @case('chevron-left')
            <path d="m15 18-6-6 6-6" />
            @break
        @case('reply')
            <path d="m9 17-5-5 5-5" /><path d="M20 18v-2a4 4 0 0 0-4-4H4" />
            @break
        @default
            <circle cx="12" cy="12" r="9" />
    @endswitch
</svg>
