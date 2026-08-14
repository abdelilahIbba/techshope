@props(['active' => false, 'icon' => null, 'href' => '#'])

@php
$classes = $active
            ? 'flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold bg-blue-600/10 text-blue-400 transition-colors mb-1 group shadow-[inset_2px_0_0_0_rgba(96,165,250,1)]'
            : 'flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-slate-400 hover:text-slate-100 hover:bg-slate-800 transition-colors mb-1 group';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}" />
    </svg>
    @else
    {{ $iconSlot ?? '' }}
    @endif
    <span class="sidebar-text truncate ml-1">{{ $slot }}</span>
</a>