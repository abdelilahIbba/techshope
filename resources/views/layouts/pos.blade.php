<!DOCTYPE html>
<html lang="fr" class="bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'POS System')</title>
    @vite(['resources/css/app.css'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Montserrat:wght@900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Modern Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Sidebar transitions */
        .sidebar { transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-text { transition: opacity 0.2s, width 0.2s; white-space: nowrap; overflow: hidden; }
        .sidebar.collapsed { width: 4.5rem; }
        .sidebar.collapsed .sidebar-text { opacity: 0; width: 0; margin-left: 0; }
        .sidebar.collapsed .logo-text { opacity: 0; width: 0; }
        
        .main-content { transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
    @stack('styles')
<script src="{{ asset('js/htmx.min.js') }}" defer></script>
</head>
<body class="text-slate-800 antialiased flex h-screen overflow-hidden" hx-boost="true">

    <!-- Sidebar -->
    <aside id="app-sidebar" class="sidebar w-64 bg-slate-900 text-slate-300 flex flex-col shadow-2xl relative shrink-0 z-50">
        
        <!-- Toggle Button (Absolute, partly outside) -->
        <button id="sidebar-toggle" class="absolute -right-3 top-6 bg-slate-800 text-slate-300 hover:text-white rounded-full p-1 shadow-md border-2 border-slate-900 transition-colors z-50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" id="toggle-icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <!-- Brand/Logo -->
        <div class="h-16 flex items-center px-4 shrink-0 border-b border-slate-800">
            <a href="{{ route('pos.dashboard') }}" class="flex items-center gap-3 overflow-hidden">
                <div class="p-1.5 bg-gradient-to-br from-orange-400 to-red-500 rounded-lg flex items-center justify-center shrink-0 shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <div class="logo-text font-['Montserrat'] text-xl font-black leading-tight tracking-tight text-white sidebar-text">
                    <span class="text-orange-400">Tick</span>Droguerie
                </div>
            </a>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto overflow-x-hidden py-4 px-3 space-y-1 scroll-smooth">
            
            <x-nav-link href="{{ route('pos.dashboard') }}" :active="request()->routeIs('pos.dashboard*')" icon="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                Tableau de bord
            </x-nav-link>

            <x-nav-link href="{{ route('pos.index') }}" :active="request()->routeIs('pos.index*')" icon="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                Points de Vente
            </x-nav-link>

            <x-nav-link href="{{ route('pos.stock') }}" :active="request()->routeIs('pos.stock*')" icon="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4">
                Gestion de Stock
            </x-nav-link>
            
            <x-nav-link href="{{ route('pos.reports') }}" :active="request()->routeIs('pos.reports*')" icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                Rapports & Stats
            </x-nav-link>

            <!-- Notifications with Badge -->
            <a href="{{ route('pos.notifications') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors mb-1 group {{ request()->routeIs('pos.notifications*') ? 'bg-blue-600/10 text-blue-400' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800' }}">
                <div class="relative shrink-0 flex items-center justify-center">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if(($notificationCount ?? 0) > 0)
                        <span class="absolute -top-1 -right-1 flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500 border border-slate-900"></span>
                        </span>
                    @endif
                </div>
                <span class="sidebar-text truncate ml-1 flex-1">Notifications</span>
                @if(($notificationCount ?? 0) > 0)
                    <span class="sidebar-text ml-auto bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">
                        {{ $notificationCount }}
                    </span>
                @endif
            </a>
            
            <x-nav-link href="{{ route('pos.settings') }}" :active="request()->routeIs('pos.settings*')" icon="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                Paramètres
            </x-nav-link>

        </nav>

        <!-- Sidebar footer: User, License, Logout -->
        <div class="p-3 border-t border-slate-800 flex flex-col gap-2">
            <!-- Included license badge, which handles its own padding/styling but we wrap it to ensure it fits -->
            <div class="sidebar-text truncate">
                @include('partials.license-badge')
            </div>

            <form method="POST" action="{{ route('logout') }}" class="w-full mt-1">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-rose-400 hover:text-white hover:bg-rose-500/90 transition-colors group">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span class="sidebar-text ml-1 opacity-100 min-w-0 truncate">Déconnexion</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main View -->
    <main class="flex-1 flex flex-col min-w-0 bg-slate-50 relative h-screen overflow-hidden">
        <!-- Render views in this slot -->
        @yield('content')
    </main>

    <!-- Sidebar Collapse Script -->
    <script>
(function() {

        var sidebar = document.getElementById('app-sidebar');
        var toggleBtn = document.getElementById('sidebar-toggle');
        var toggleIcon = document.getElementById('toggle-icon');

        // Check local storage for preference
        var isCollapsedMenu = localStorage.getItem('sidebar_collapsed') === 'true';
        if (isCollapsedMenu) {
            sidebar.classList.add('collapsed');
            toggleIcon.classList.add('rotate-180');
        }

        toggleBtn.addEventListener('click', () => {
            var isCurrentlyCollapsed = sidebar.classList.toggle('collapsed');
            toggleIcon.classList.toggle('rotate-180');
            localStorage.setItem('sidebar_collapsed', isCurrentlyCollapsed);
            
            // Adjust layouts in POS index if specific components need repositioning
            window.dispatchEvent(new Event('sidebarToggle'));
        });
    
})();
</script>
    @stack('scripts')
</body>
</html>



