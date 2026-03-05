<x-settings-layout title="License Management" subtitle="Activation status, periods, and key generation" active="license">
    <x-settings-breadcrumbs :items="[
        ['label' => 'Dashboard', 'href' => route('pos.dashboard')],
        ['label' => 'Settings', 'href' => route('pos.settings')],
        ['label' => 'License Management']
    ]" />

    <div class="mt-6 space-y-6">
        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="4" width="16" height="16" rx="3" />
                        <path d="M12 8v8" />
                        <path d="M8 12h8" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold">Current license</h3>
                    <p class="text-sm text-slate-500">Track activation and expiry.</p>
                </div>
            </div>
            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div class="rounded-xl bg-slate-100/80 p-4">
                    <div class="text-xs uppercase tracking-wide text-slate-400">Status</div>
                    <div class="mt-1 font-semibold text-slate-900">{{ $license && $license->expires_at && $license->expires_at->isFuture() ? 'Active' : 'Inactive' }}</div>
                </div>
                <div class="rounded-xl bg-slate-100/80 p-4">
                    <div class="text-xs uppercase tracking-wide text-slate-400">Period</div>
                    <div class="mt-1 font-semibold text-slate-900">{{ $license->period ?? 'Not set' }}</div>
                </div>
                <div class="rounded-xl bg-slate-100/80 p-4">
                    <div class="text-xs uppercase tracking-wide text-slate-400">Activated</div>
                    <div class="mt-1 font-semibold text-slate-900">{{ optional($license?->activated_at)->format('Y-m-d H:i') ?? 'Not activated' }}</div>
                </div>
                <div class="rounded-xl bg-slate-100/80 p-4">
                    <div class="text-xs uppercase tracking-wide text-slate-400">Expires</div>
                    <div class="mt-1 font-semibold text-slate-900">{{ optional($license?->expires_at)->format('Y-m-d H:i') ?? 'Not set' }}</div>
                </div>
            </div>
            <div class="mt-5">
                <a href="{{ route('license.activate') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition duration-200 hover:bg-slate-800">
                    Manage license
                </a>
            </div>
        </section>

        @if(auth()->user()->role === 'Admin')
            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Generate license key</h3>
                        <p class="text-sm text-slate-500">Create a new key for client activation.</p>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-2" for="license_period">Period</label>
                        <select id="license_period" class="w-full rounded-xl bg-slate-100/80 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            @foreach($licensePeriods as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" onclick="generateLicenseKey()" id="generateLicenseBtn" class="h-12 rounded-xl bg-purple-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-purple-700">
                        <span id="generateLicenseBtnText">Generate key</span>
                    </button>
                </div>

                <div id="generatedKeyBox" class="hidden mt-5">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-slate-600">Generated key</label>
                        <button type="button" onclick="copyLicenseKey()" id="copyBtn" class="text-xs font-semibold text-purple-600 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-lg transition-colors">Copy</button>
                    </div>
                    <textarea id="generatedKeyText" readonly rows="4" class="w-full rounded-xl bg-purple-50 p-3 text-xs font-mono text-purple-900 focus:outline-none" onclick="this.select()"></textarea>
                    <p class="text-xs text-slate-500 mt-2">Send this key to the client for activation.</p>
                </div>
            </section>
        @else
            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">License activation</h3>
                        @if ($license && $license->expires_at && $license->expires_at->isFuture())
                            <p class="text-sm text-emerald-600 font-medium mt-1">Active until {{ $license->expires_at->format('Y-m-d') }}</p>
                        @else
                            <p class="text-sm text-rose-600 font-medium mt-1">No active license.</p>
                        @endif
                    </div>
                    <a href="{{ route('license.activate') }}" class="rounded-xl bg-amber-100 px-4 py-2.5 text-sm font-semibold text-amber-700 hover:bg-amber-200">
                        Activate
                    </a>
                </div>
            </section>
        @endif

        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
            <h4 class="text-sm font-semibold text-slate-900">Available periods</h4>
            <p class="text-sm text-slate-500 mt-1">Reference for upcoming plan options.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($licensePeriods as $period => $label)
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $label }}</span>
                @endforeach
            </div>
        </section>
    </div>

    <script>
        async function generateLicenseKey() {
            const period = document.getElementById('license_period')?.value;
            const btn = document.getElementById('generateLicenseBtn');
            const btnText = document.getElementById('generateLicenseBtnText');
            const box = document.getElementById('generatedKeyBox');
            const textarea = document.getElementById('generatedKeyText');

            if (!period) return;

            btnText.textContent = 'Generating...';
            btn.disabled = true;

            try {
                const resp = await fetch(@json(route('license.generate')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                            || @json(csrf_token()),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ period }),
                });

                const data = await resp.json();

                if (resp.ok && data.key) {
                    textarea.value = data.key;
                    box.classList.remove('hidden');
                    textarea.select();
                } else {
                    alert('Error: ' + (data.message || 'Unable to generate license.'));
                }
            } catch (e) {
                alert('Network error: ' + e.message);
            } finally {
                btnText.textContent = 'Generate key';
                btn.disabled = false;
            }
        }

        function copyLicenseKey() {
            const ta = document.getElementById('generatedKeyText');
            ta.select();
            document.execCommand('copy');

            const copyBtn = document.getElementById('copyBtn');
            copyBtn.textContent = 'Copied';
            setTimeout(() => { copyBtn.textContent = 'Copy'; }, 2000);
        }
    </script>
</x-settings-layout>
