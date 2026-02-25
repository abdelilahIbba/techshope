<?php

use App\Models\License;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

// Ensure an active license exists for all RBAC tests
// (so the CheckLicense middleware passes and we test CheckPermission in isolation)
beforeEach(function () {
    License::updateOrCreate(['id' => 1], [
        'key'          => 'DRG-TEST',
        'period'       => '1_year',
        'activated_at' => now(),
        'expires_at'   => now()->addYear(),
    ]);
});

function createRoleWithPermission(string $roleName, string $permissionKey): void
{
    $role = Role::query()->create([
        'name' => $roleName,
        'description' => 'Test role',
    ]);

    $permission = Permission::query()->create([
        'key' => $permissionKey,
        'label' => 'Test permission',
        'page' => 'Settings',
        'action' => 'View',
    ]);

    $role->permissions()->sync([$permission->id]);
}

test('user without role sees permission denied modal page with countdown content', function () {
    $user = User::factory()->withoutTwoFactor()->create([
        'role' => null,
    ]);

    $response = $this->actingAs($user)->get(route('pos.settings'));

    $response->assertStatus(403);
    $response->assertSee('Accès refusé', false);
    $response->assertSee('Redirection vers POS dans', false);
    $response->assertSee('settings.view', false);
    $response->assertSee('Aller maintenant', false);
});

test('permission denied returns json 403 for ajax requests', function () {
    $user = User::factory()->withoutTwoFactor()->create([
        'role' => null,
    ]);

    $response = $this->actingAs($user)->getJson(route('pos.settings'));

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'Accès refusé.',
            'permission' => 'settings.view',
        ]);
});

test('user with role permission can access protected settings page', function () {
    createRoleWithPermission('Editor', 'settings.view');

    $user = User::factory()->withoutTwoFactor()->create([
        'role' => 'Editor',
    ]);

    $this->actingAs($user)
        ->get(route('pos.settings'))
        ->assertOk();
});

test('user without action permission sees denied modal for restricted action', function () {
    createRoleWithPermission('Editor', 'settings.view');

    $user = User::factory()->withoutTwoFactor()->create([
        'role' => 'Editor',
    ]);

    $response = $this->actingAs($user)->post(route('pos.settings.system.update'), [
        'currency' => 'DZD',
    ]);

    $response->assertStatus(403);
    $response->assertSee('settings.update', false);
});
