<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

// ─── Role Management tests ────────────────────────────────────────────────────

beforeEach(function () {
    activeLicense();

    // Core permissions needed by route middleware / UI
    ensurePermission('settings.view');
    ensurePermission('roles.manage');
    ensurePermission('dashboard.view', 'Dashboard', 'View');
});

// ─── Admin: create role ───────────────────────────────────────────────────────

test('admin can create a new role', function () {
    $admin = userWithRole('Admin');

    $this->actingAs($admin)->post(route('pos.settings.roles.create'), [
        'name'        => 'Cashier',
        'description' => 'Caisse uniquement',
    ])->assertRedirect(route('pos.settings'))
      ->assertSessionHas('success');

    expect(Role::query()->where('name', 'Cashier')->exists())->toBeTrue();
});

test('admin can create a role with permissions', function () {
    $admin = userWithRole('Admin');

    $this->actingAs($admin)->post(route('pos.settings.roles.create'), [
        'name'            => 'Scanner',
        'permission_keys' => ['settings.view', 'dashboard.view'],
    ])->assertRedirect(route('pos.settings'));

    $role = Role::query()->where('name', 'Scanner')->with('permissions')->first();
    expect($role->permissions->pluck('key')->all())->toContain('settings.view');
    expect($role->permissions->pluck('key')->all())->toContain('dashboard.view');
});

test('creating a role with a duplicate name fails', function () {
    $admin = userWithRole('Admin');
    Role::query()->create(['name' => 'Duplicate', 'description' => '']);

    $this->actingAs($admin)->post(route('pos.settings.roles.create'), [
        'name' => 'Duplicate',
    ])->assertSessionHasErrors('name');
});

test('creating a role requires a name', function () {
    $admin = userWithRole('Admin');

    $this->actingAs($admin)->post(route('pos.settings.roles.create'), [
        'description' => 'No name',
    ])->assertSessionHasErrors('name');
});

// ─── Admin: update role ───────────────────────────────────────────────────────

test('admin can update a non-admin role', function () {
    $admin  = userWithRole('Admin');
    $editor = Role::query()->firstOrCreate(['name' => 'Editor'], ['description' => 'ops']);

    $this->actingAs($admin)->put(route('pos.settings.roles.update', $editor), [
        'name'        => 'Editor',
        'description' => 'Updated description',
    ])->assertRedirect(route('pos.settings'))
      ->assertSessionHas('success');

    expect($editor->fresh()->description)->toBe('Updated description');
});

test('updating the admin role is blocked', function () {
    $admin     = userWithRole('Admin');
    $adminRole = Role::query()->firstOrCreate(['name' => 'Admin'], ['description' => 'Full']);

    $this->actingAs($admin)->put(route('pos.settings.roles.update', $adminRole), [
        'name'        => 'AdminRenamed',
        'description' => 'Hacked',
    ])->assertRedirect(route('pos.settings'))
      ->assertSessionHas('error');

    // Name should be unchanged
    expect($adminRole->fresh()->name)->toBe('Admin');
});

test('admin can update role permissions', function () {
    $admin      = userWithRole('Admin');
    $targetRole = Role::query()->firstOrCreate(['name' => 'Tester'], ['description' => '']);

    $this->actingAs($admin)->put(route('pos.settings.roles.update', $targetRole), [
        'name'            => 'Tester',
        'permission_keys' => ['settings.view'],
    ])->assertRedirect(route('pos.settings'));

    expect($targetRole->fresh()->permissions->pluck('key')->all())->toContain('settings.view');
});

// ─── Admin: delete role ───────────────────────────────────────────────────────

test('admin can delete an empty role', function () {
    $admin      = userWithRole('Admin');
    $targetRole = Role::query()->create(['name' => 'Temporary', 'description' => '']);

    $this->actingAs($admin)->delete(route('pos.settings.roles.delete', $targetRole))
         ->assertRedirect(route('pos.settings'))
         ->assertSessionHas('success');

    expect(Role::query()->find($targetRole->id))->toBeNull();
});

test('deleting the admin role is blocked', function () {
    $admin     = userWithRole('Admin');
    $adminRole = Role::query()->firstOrCreate(['name' => 'Admin'], ['description' => 'Full']);

    $this->actingAs($admin)->delete(route('pos.settings.roles.delete', $adminRole))
         ->assertRedirect(route('pos.settings'))
         ->assertSessionHas('error');

    expect(Role::query()->find($adminRole->id))->not->toBeNull();
});

test('deleting a role assigned to users is blocked', function () {
    $admin     = userWithRole('Admin');
    Role::query()->firstOrCreate(['name' => 'Editor'], ['description' => 'ops']);
    $staffUser = userWithRole('Editor');
    $editorRole = Role::query()->where('name', 'Editor')->first();

    $this->actingAs($admin)->delete(route('pos.settings.roles.delete', $editorRole))
         ->assertRedirect(route('pos.settings'))
         ->assertSessionHas('error');

    expect(Role::query()->find($editorRole->id))->not->toBeNull();
});

// ─── SuperUser: RBAC access ───────────────────────────────────────────────────

test('superuser with roles.manage can create a role', function () {
    roleWith('SuperUser', ['settings.view', 'roles.manage', 'dashboard.view']);

    $superUser = userWithRole('SuperUser');

    $this->actingAs($superUser)->post(route('pos.settings.roles.create'), [
        'name' => 'NewBySuper',
    ])->assertRedirect(route('pos.settings'))
      ->assertSessionHas('success');

    expect(Role::query()->where('name', 'NewBySuper')->exists())->toBeTrue();
});

test('superuser cannot update the admin role', function () {
    roleWith('SuperUser', ['settings.view', 'roles.manage', 'dashboard.view']);

    $superUser = userWithRole('SuperUser');
    $adminRole = Role::query()->firstOrCreate(['name' => 'Admin'], ['description' => 'Full']);

    $this->actingAs($superUser)->put(route('pos.settings.roles.update', $adminRole), [
        'name'        => 'AdminHacked',
        'description' => 'Compromised',
    ])->assertRedirect(route('pos.settings'))
      ->assertSessionHas('error');

    expect($adminRole->fresh()->name)->toBe('Admin');
});

test('superuser can delete a non-admin empty role', function () {
    roleWith('SuperUser', ['settings.view', 'roles.manage', 'dashboard.view']);

    $superUser  = userWithRole('SuperUser');
    $targetRole = Role::query()->create(['name' => 'ToDelete', 'description' => '']);

    $this->actingAs($superUser)->delete(route('pos.settings.roles.delete', $targetRole))
         ->assertRedirect(route('pos.settings'))
         ->assertSessionHas('success');

    expect(Role::query()->find($targetRole->id))->toBeNull();
});

// ─── Editor: blocked from role management ────────────────────────────────────

test('editor without roles.manage cannot create a role', function () {
    roleWith('Editor', ['settings.view']); // no roles.manage

    $editor = userWithRole('Editor');

    $this->actingAs($editor)->post(route('pos.settings.roles.create'), [
        'name' => 'HackedRole',
    ])->assertStatus(403);

    expect(Role::query()->where('name', 'HackedRole')->exists())->toBeFalse();
});

test('editor without roles.manage cannot delete a role', function () {
    roleWith('Editor', ['settings.view']);

    $editor     = userWithRole('Editor');
    $targetRole = Role::query()->create(['name' => 'Protected', 'description' => '']);

    $this->actingAs($editor)->delete(route('pos.settings.roles.delete', $targetRole))
         ->assertStatus(403);

    expect(Role::query()->find($targetRole->id))->not->toBeNull();
});
