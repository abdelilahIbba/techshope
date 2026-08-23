@php
    $currentRoute = Route::currentRouteName();
    
    $navItems = [
        [
            'label' => 'Tableau de bord',
            'route' => 'pos.dashboard',
            'active' => $currentRoute === 'pos.dashboard' || $currentRoute === 'pos.dashboard.print',
            'class' => 'nav-link'
        ],
        [
            'label' => 'POS',
            'route' => 'pos.index',
            'active' => $currentRoute === 'pos.index',
            'class' => 'nav-link'
        ],
        [
            'label' => 'Gestion de stock',
            'route' => 'pos.stock',
            'active' => $currentRoute === 'pos.stock',
            'class' => 'nav-link'
        ],
        [
            'label' => 'Notifications stock',
            'route' => 'pos.notifications',
            'active' => $currentRoute === 'pos.notifications',
            'class' => 'nav-link notification-badge relative',
            'badge' => $notificationCount ?? 0
        ],
        [
            'label' => 'Rapports',
            'route' => 'pos.reports',
            'active' => str_starts_with($currentRoute, 'pos.reports') || $currentRoute === 'pos.expenses',
            'class' => 'nav-link'
        ],
        [
            'label' => 'Paramètres',
            'route' => 'pos.settings',
            'active' => str_starts_with($currentRoute, 'pos.settings'),
            'class' => 'nav-link'
        ],
    ];
@endphp

<nav class="top-nav flex items-center gap-2 p-4 bg-white border-b border-slate-200 shadow-sm shrink-0 z-20">
    <a href="{{ route('pos.dashboard') }}" style="display: flex; align-items: center; gap: 0px; text-decoration: none; margin-right: 15px;">
        <img src="{{ asset('images/logo.png') }}" onerror="this.src='{{ asset('logo.svg') }}'" alt="TechShope" style="width: 150px; height: auto; margin-right: -10px;">
        <div style="font-family: 'Montserrat', sans-serif; font-size: 22px; font-weight: 900; line-height: 1;">
            <span style="color: #F27036;">Tech</span><span style="color: #1A476F;">Shope</span>
        </div>
    </a>

    @foreach($navItems as $item)
        <a class="{{ $item['class'] }} {{ $item['active'] ? 'active bg-blue-600 text-white shadow-md border-blue-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent' }} px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200" 
           href="{{ route($item['route']) }}">
            {{ $item['label'] }}
            @if(isset($item['badge']) && $item['badge'] > 0)
                <span class="badge-count absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] font-bold border-2 border-white shadow-sm">{{ $item['badge'] }}</span>
            @endif
        </a>
    @endforeach

    @include('partials.license-badge')

    <form method="POST" action="{{ route('logout') }}" class="ml-auto">
        @csrf
        <button type="submit" class="nav-link px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 cursor-pointer flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
            Déconnexion
        </button>
    </form>
</nav>
