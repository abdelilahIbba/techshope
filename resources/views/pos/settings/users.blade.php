<x-settings-layout title="Utilisateurs & Accès" subtitle="Gérer l'accès du personnel, les rôles et les permissions" active="users">
    <x-settings-breadcrumbs :items="[
        ['label' => 'Tableau de bord', 'href' => route('pos.dashboard')],
        ['label' => 'Paramètres', 'href' => route('pos.settings')],
        ['label' => 'Utilisateurs']
    ]" />

    <style>
        .modal { display: none; }
        .modal.active { display: flex; }
    </style>

    <div class="mt-6 space-y-6">
        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Droits d'Accès</h3>
                    <p class="text-sm text-slate-500 mt-1">Créez des rôles, attribuez des permissions et organisez les accès.</p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <form method="POST" action="{{ route('pos.settings.roles.create') }}" class="rounded-2xl bg-slate-50 p-5">
                    @csrf
                    <h4 class="font-semibold text-slate-900 mb-4">Créer un nouveau rôle</h4>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-600 mb-2">Nom du rôle</label>
                        <input type="text" name="name" required class="w-full rounded-xl bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-600 mb-2">Description</label>
                        <input type="text" name="description" class="w-full rounded-xl bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    </div>
                    <div class="space-y-3 max-h-64 overflow-y-auto rounded-xl bg-white p-4">
                        @foreach($permissionsByPage as $page => $pagePermissions)
                            <div>
                                <p class="text-xs uppercase tracking-wide font-semibold text-slate-400 mb-2">{{ $page }}</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($pagePermissions as $permission)
                                        <label class="flex items-center gap-2 text-sm text-slate-600">
                                            <input type="checkbox" name="permission_keys[]" value="{{ $permission->key }}" class="rounded border-slate-300">
                                            <span>{{ $permission->label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="submit" class="mt-4 w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Créer le rôle</button>
                </form>

                <div class="space-y-4 max-h-[520px] overflow-y-auto pr-1">
                    @forelse($roles as $role)
                        @php $rolePermissionKeys = $role->permissions->pluck('key')->all(); @endphp
                        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
                            @if($role->name === 'Admin')
                                <div class="flex items-start justify-between gap-3 mb-4">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $role->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $role->description }}</p>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 text-amber-700 px-3 py-1 text-xs font-semibold">Protégé</span>
                                </div>
                                <div class="space-y-1 max-h-40 overflow-y-auto rounded-xl bg-slate-50 p-3 text-xs text-slate-500">
                                    @foreach($role->permissions->groupBy('page') as $page => $perms)
                                        <p><span class="font-semibold">{{ $page }}:</span> {{ $perms->pluck('label')->implode(', ') }}</p>
                                    @endforeach
                                </div>
                            @else
                                <form method="POST" action="{{ route('pos.settings.roles.update', $role) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-400 mb-2">Nom</label>
                                            <input type="text" name="name" value="{{ $role->name }}" class="w-full rounded-xl bg-slate-50 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20" required>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-400 mb-2">Description</label>
                                            <input type="text" name="description" value="{{ $role->description }}" class="w-full rounded-xl bg-slate-50 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                        </div>
                                    </div>

                                    <div class="space-y-3 max-h-52 overflow-y-auto rounded-xl bg-slate-50 p-3">
                                        @foreach($permissionsByPage as $page => $pagePermissions)
                                            <div>
                                                <p class="text-xs uppercase tracking-wide font-semibold text-slate-400 mb-2">{{ $page }}</p>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                    @foreach($pagePermissions as $permission)
                                                        <label class="flex items-center gap-2 text-sm text-slate-600">
                                                            <input type="checkbox" name="permission_keys[]" value="{{ $permission->key }}" {{ in_array($permission->key, $rolePermissionKeys, true) ? 'checked' : '' }} class="rounded border-slate-300">
                                                            <span>{{ $permission->label }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="flex gap-2 mt-4">
                                        <button type="submit" class="flex-1 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Enregistrer</button>
                                    </div>
                                </form>

                                <form method="POST" action="{{ route('pos.settings.roles.delete', $role) }}" class="mt-3" onsubmit="return confirm('Supprimer ce rôle ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full rounded-xl bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-100">Supprimer le rôle</button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Aucun rôle défini.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Utilisateurs</h3>
                    <p class="text-sm text-slate-500">Gérer le personnel et attribuer des rôles.</p>
                </div>
                <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-slate-800" onclick="openAddUserModal()">
                    Ajouter un utilisateur
                </button>
            </div>

            <div class="overflow-x-auto rounded-2xl bg-slate-50">
                <table class="w-full text-sm text-left text-slate-600">
                    <thead class="text-xs uppercase text-slate-400">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Nom</th>
                            <th class="px-5 py-4 font-semibold">Rôle</th>
                            <th class="px-5 py-4 font-semibold">Dernière mise à jour</th>
                            <th class="px-5 py-4 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr class="border-t border-white/70">
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ $user->name }}</td>
                                <td class="px-5 py-4">
                                    <form method="POST" action="{{ route('pos.settings.user.assign-role', $user) }}" class="flex items-center gap-2">
                                        @csrf
                                        <select name="role_name" class="rounded-xl bg-white px-3 py-2 text-xs text-slate-700">
                                            <option value="">Aucun</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}" {{ $user->role === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="rounded-lg bg-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-300">Mettre à jour</button>
                                    </form>
                                </td>
                                <td class="px-5 py-4 text-slate-400">{{ $user->updated_at->diffForHumans() }}</td>
                                <td class="px-5 py-4">
                                    @php
                                        $editUserPayload = [
                                            'id' => $user->id,
                                            'name' => $user->name,
                                            'role_name' => $user->role,
                                        ];
                                    @endphp
                                    <div class="flex justify-end gap-2">
                                        <button class="rounded-lg bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-600 hover:bg-sky-100" onclick='editUser(@json($editUserPayload))'>Modifier</button>
                                        <form method="POST" action="{{ route('pos.settings.user.delete', $user) }}" class="inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-100">Supprimer</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-6 text-center text-slate-500">Aucun utilisateur trouvé.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
            <h3 class="text-lg font-semibold text-slate-900">Journal d'audit</h3>
            <p class="text-sm text-slate-500 mt-1">Changements récents de rôles et de permissions.</p>
            <div class="mt-4 overflow-x-auto rounded-2xl bg-slate-50">
                <table class="w-full text-sm text-left text-slate-600">
                    <thead class="text-xs uppercase text-slate-400">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Date</th>
                            <th class="px-4 py-3 font-semibold">Event</th>
                            <th class="px-4 py-3 font-semibold">Actor</th>
                            <th class="px-4 py-3 font-semibold">Target</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($auditLogs as $log)
                            <tr class="border-t border-white/70">
                                <td class="px-4 py-3">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $log->event }}</td>
                                <td class="px-4 py-3">{{ optional($log->actor)->name ?? 'System' }}</td>
                                <td class="px-4 py-3">{{ $log->target_type ? class_basename($log->target_type) . ' #' . $log->target_id : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-center text-slate-500">Aucun événement d'audit pour le moment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div id="addUserModal" class="modal fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-lg font-semibold text-slate-900">Ajouter un utilisateur</h3>
                <button class="text-slate-400 hover:text-slate-700" onclick="closeAddUserModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('pos.settings.user.add') }}" class="flex flex-col gap-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">Nom complet</label>
                    <input type="text" name="name" class="w-full rounded-xl bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">code PIN (min 4 chiffres)</label>
                    <input type="password" name="password" class="w-full rounded-xl bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20" required minlength="4">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">Rôle</label>
                    <select name="role_name" class="w-full rounded-xl bg-slate-50 px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/20" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-3">
                    <button type="button" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200" onclick="closeAddUserModal()">Annuler</button>
                    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editUserModal" class="modal fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-lg font-semibold text-slate-900">Modifier l'utilisateur</h3>
                <button class="text-slate-400 hover:text-slate-700" onclick="closeEditUserModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form method="POST" id="editUserForm" class="flex flex-col gap-4">
                @csrf
                <input type="hidden" id="edit_user_id">
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">Nom complet</label>
                    <input type="text" name="name" id="edit_name" class="w-full rounded-xl bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">Nouveau code PIN (optionnel)</label>
                    <input type="password" name="password" class="w-full rounded-xl bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20" minlength="4" placeholder="****">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">Rôle</label>
                    <select name="role_name" id="edit_role_name" class="w-full rounded-xl bg-slate-50 px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/20" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-3">
                    <button type="button" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200" onclick="closeEditUserModal()">Annuler</button>
                    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddUserModal() {
            document.getElementById('addUserModal').classList.add('active');
        }

        function closeAddUserModal() {
            document.getElementById('addUserModal').classList.remove('active');
        }

        function editUser(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_name').value = user.name;
            document.getElementById('edit_role_name').value = user.role_name || '';
            document.getElementById('editUserForm').action = "{{ url('/pos/settings/user/edit') }}/" + user.id;
            document.getElementById('editUserModal').classList.add('active');
        }

        function closeEditUserModal() {
            document.getElementById('editUserModal').classList.remove('active');
        }

        document.querySelectorAll('.modal').forEach((modal) => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });
    </script>
</x-settings-layout>
