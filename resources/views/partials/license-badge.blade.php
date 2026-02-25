{{--
    License badge — included in every POS nav bar.
    $navLicense is shared globally from AppServiceProvider.
--}}
@php
    $lic = $navLicense ?? null;
    $expiresAt = $lic?->expires_at;
    $isAdmin = auth()->check() && auth()->user()->role === 'Admin';

    if ($lic && $expiresAt && $expiresAt->isFuture()) {
        $diffDays   = (int) now()->diffInDays($expiresAt, false);
        $diffHours  = (int) now()->diffInHours($expiresAt, false);
        $diffSecs   = (int) now()->diffInSeconds($expiresAt, false);

        if ($diffDays >= 30) {
            $badgeColor  = 'bg-emerald-50 border-emerald-200 text-emerald-700';
            $dotColor    = 'bg-emerald-500';
            $pulse       = false;
            $labelText   = $diffDays . 'j restants';
        } elseif ($diffDays >= 7) {
            $badgeColor  = 'bg-amber-50 border-amber-200 text-amber-700';
            $dotColor    = 'bg-amber-500';
            $pulse       = false;
            $labelText   = $diffDays . 'j restants';
        } elseif ($diffDays >= 1) {
            $badgeColor  = 'bg-red-50 border-red-200 text-red-700';
            $dotColor    = 'bg-red-500';
            $pulse       = true;
            $labelText   = $diffDays . 'j ' . ($diffHours % 24) . 'h restants';
        } else {
            $badgeColor  = 'bg-red-50 border-red-300 text-red-700';
            $dotColor    = 'bg-red-600';
            $pulse       = true;
            $labelText   = null; // use live JS countdown
        }
        $licenseActive = true;
    } else {
        $licenseActive = false;
        $diffSecs      = 0;
        $labelText     = $isAdmin ? 'Aucune licence' : 'Système verrouillé';
        $badgeColor    = 'bg-red-50 border-red-200 text-red-600';
        $dotColor      = 'bg-red-500';
        $pulse         = true;
    }
@endphp

<div class="flex items-center ml-auto mr-3">
    {{-- License badge --}}
    @if ($isAdmin || true)
    @php $wrapHref = (! $licenseActive) ? route('license.activate') : ($isAdmin ? route('pos.settings') . '#license' : route('license.activate')); @endphp
    <a href="{{ $wrapHref }}"
        title="{{ $licenseActive ? 'Expire le ' . $expiresAt->format('d/m/Y H:i') : 'Aucune licence active' }}"
        class="flex items-center gap-1.5 border rounded-lg px-2.5 py-1.5 text-xs font-semibold transition-colors hover:opacity-80 {{ $badgeColor }}">

        {{-- Indicator dot --}}
        <span class="relative flex h-2 w-2 flex-shrink-0">
            @if ($pulse)
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $dotColor }} opacity-60"></span>
            @endif
            <span class="relative inline-flex rounded-full h-2 w-2 {{ $dotColor }}"></span>
        </span>

        {{-- Key icon --}}
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
        </svg>

        {{-- Label --}}
        @if ($labelText !== null)
            <span>{{ $labelText }}</span>
        @else
            {{-- Live countdown (< 24 h) --}}
            <span id="lic-countdown">{{ gmdate('H:i:s', max(0, $diffSecs)) }}</span>
        @endif
    </a>
    @endif
</div>

@if ($labelText === null && $licenseActive)
{{-- Live countdown script — only injected when < 24 h remain --}}
<script>
(function () {
    var remaining = {{ (int) $diffSecs }};
    var el = document.getElementById('lic-countdown');
    if (!el) return;
    var t = setInterval(function () {
        remaining = Math.max(0, remaining - 1);
        var h = Math.floor(remaining / 3600);
        var m = Math.floor((remaining % 3600) / 60);
        var s = remaining % 60;
        el.textContent = ('0' + h).slice(-2) + ':' + ('0' + m).slice(-2) + ':' + ('0' + s).slice(-2);
        if (remaining <= 0) {
            clearInterval(t);
            el.textContent = 'Expirée';
            window.location.reload();
        }
    }, 1000);
})();
</script>
@endif
