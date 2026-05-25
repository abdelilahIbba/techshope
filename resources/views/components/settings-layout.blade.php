@props([
    'title' => 'Settings',
    'subtitle' => null,
    'active' => 'index',
])
@php
    $navItems = [
        [
            'key' => 'index',
            'label' => 'All Settings',
            'description' => 'Overview and categories',
            'route' => route('pos.settings'),
        ],
        [
            'key' => 'printer',
            'label' => 'Printer Settings',
            'description' => 'Receipts and devices',
            'route' => route('pos.settings.printer'),
        ],
        [
            'key' => 'barcode',
            'label' => 'Barcode Printer',
            'description' => 'Label device setup',
            'route' => route('pos.settings.barcode'),
        ],
        [
            'key' => 'ticket-format',
            'label' => 'Ticket Format',
            'description' => 'Paper and layout',
            'route' => route('pos.settings.ticket-format'),
        ],
        [
            'key' => 'auto-print',
            'label' => 'Auto Print',
            'description' => 'Print on checkout',
            'route' => route('pos.settings.auto-print'),
        ],
        [
            'key' => 'input-mode',
            'label' => 'Input Mode',
            'description' => 'Scanner behavior',
            'route' => route('pos.settings.input-mode'),
        ],
        [
            'key' => 'sound',
            'label' => 'Sound Settings',
            'description' => 'Beep and alerts',
            'route' => route('pos.settings.sound'),
        ],
        [
            'key' => 'license',
            'label' => 'License Management',
            'description' => 'Activation and status',
            'route' => route('pos.settings.license'),
        ],
        [
            'key' => 'stock',
            'label' => 'Stock',
            'description' => 'Inventory defaults',
            'route' => route('pos.settings.stock'),
        ],
        [
            'key' => 'users',
            'label' => 'Users',
            'description' => 'Access and roles',
            'route' => route('pos.settings.users'),
        ],
        [
            'key' => 'taxes',
            'label' => 'Taxes',
            'description' => 'Tax rates and rules',
            'route' => route('pos.settings.taxes'),
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - POS System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sora', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased min-h-screen">
    <div class="pointer-events-none fixed inset-0 -z-10">
        <div class="absolute -top-24 -right-16 h-64 w-64 rounded-full bg-gradient-to-br from-blue-100/70 via-white/40 to-transparent blur-3xl"></div>
        <div class="absolute top-32 -left-20 h-72 w-72 rounded-full bg-gradient-to-tr from-slate-100/80 via-white/40 to-transparent blur-3xl"></div>
    </div>

    @include('partials.pos-nav')

    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-6">
            <aside class="lg:w-72 w-full">
                <div class="bg-white shadow-sm ring-1 ring-slate-900/5 rounded-2xl p-4">
                    <div class="relative">
                        <input type="text" placeholder="Search settings" class="w-full rounded-xl bg-slate-100/80 border border-transparent focus:border-slate-300 focus:ring-2 focus:ring-blue-500/20 px-4 py-2.5 text-sm" />
                        <svg class="absolute right-3 top-2.5 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="M20 20l-3.5-3.5"></path>
                        </svg>
                    </div>
                    <div class="mt-4 space-y-1">
                        @foreach($navItems as $item)
                            <a href="{{ $item['route'] }}" class="group flex items-center justify-between gap-3 rounded-xl px-3 py-3 text-sm transition-all duration-200 {{ $active === $item['key'] ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                <div>
                                    <div class="font-semibold">{{ $item['label'] }}</div>
                                    <div class="text-xs {{ $active === $item['key'] ? 'text-slate-200' : 'text-slate-400' }}">{{ $item['description'] }}</div>
                                </div>
                                <svg class="h-4 w-4 {{ $active === $item['key'] ? 'text-slate-200' : 'text-slate-400 group-hover:text-slate-700' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 18l6-6-6-6" />
                                </svg>
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>

            <main class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-900">{{ $title }}</h1>
                    @if($subtitle)
                        <p class="text-slate-500 mt-1">{{ $subtitle }}</p>
                    @endif
                </div>

                {{ $slot }}
            </main>
        </div>
    </div>

    @if(session('success'))
        <div id="toast-success" class="fixed top-6 right-6 z-50 rounded-2xl bg-white shadow-lg ring-1 ring-emerald-200/60 px-4 py-3 text-sm text-emerald-700 flex items-center gap-2">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 6L9 17l-5-5" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div id="toast-error" class="fixed top-6 right-6 z-50 rounded-2xl bg-white shadow-lg ring-1 ring-rose-200/60 px-4 py-3 text-sm text-rose-700 flex items-center gap-2">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 9v4m0 4h.01" />
                <circle cx="12" cy="12" r="9" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <script>
        const successToast = document.getElementById('toast-success');
        const errorToast = document.getElementById('toast-error');
        [successToast, errorToast].forEach((toast) => {
            if (!toast) return;
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }, 3200);
        });
    </script>
</body>
</html>
