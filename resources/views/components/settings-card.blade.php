@props([
    'title',
    'description',
    'href',
])
<a href="{{ $href }}" class="group relative flex h-full flex-col justify-between rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-900/5 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
    <div class="flex items-start gap-4">
        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-700 transition-colors duration-200 group-hover:bg-slate-900 group-hover:text-white">
            {{ $icon ?? '' }}
        </div>
        <div class="flex-1">
            <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
        </div>
        <svg class="h-4 w-4 text-slate-400 transition-colors duration-200 group-hover:text-slate-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 18l6-6-6-6" />
        </svg>
    </div>
</a>
