@props([
    'items' => [],
])
<nav class="text-sm text-slate-500" aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center gap-2">
        @foreach($items as $item)
            <li class="flex items-center gap-2">
                @if(!empty($item['href']))
                    <a href="{{ $item['href'] }}" class="font-medium text-slate-500 hover:text-slate-900 transition-colors">{{ $item['label'] }}</a>
                @else
                    <span class="font-medium text-slate-900">{{ $item['label'] }}</span>
                @endif
                @if(!$loop->last)
                    <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 18l6-6-6-6" />
                    </svg>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
