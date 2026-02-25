<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// ─── User Management tests ────────────────────────────────────────────────────

beforeEach(function () {
    activeLicense();

    // Ensure the roles needed by validation exist
    ensurePermission('settings.view');
    ensurePermission('users.manage');
    roleWith('Admin',  []);   // Admin bypasses permission check in User::hasPermissionTo
    roleWith('Editor', ['settings.view']);
    roleWith('Viewer', []);
});

// ─── Admin: add user ──────────────────────────────────────────────────────────

test('admin can add a new user', function () {
    $admin = userWithRole('Admin');

    $response = $this->actingAs($admin)->post(route('pos.settings.user.add'), [
        'name'      => 'New Staff',
        'password'  => '1234',
        'role_name' => 'Editor',
    ]);

    $response->assertRedirect(route('pos.settings'));
    $response->assertSessionHas('success');
    expect(User::query()->where('name', 'New Staff')->exists())->toBeTrue();
});

test('newly added user can login with their pin', function () {
    $admin = userWithRole('Admin');

    $this->actingAs($admin)->post(route('pos.settings.user.add'), [
        'name'      => 'PinUser',
        'password'  => '9876',
        'role_name' => 'Editor',
    ]);

    $newUser = User::query()->where('name', 'PinUser')->firstOrFail();

    $this->post(route('login'), [
        'user_id'  => (string) $newUser->id,
        'password' => '9876',
    ]);

    $this->assertAuthenticated();
});

test('add user requires name, password and valid role', function () {
    $admin = userWithRole('Admin');

    $this->actingAs($admin)->post(route('pos.settings.user.add'), [
        'name' => '',
    ])->assertSessionHasErrors(['name', 'password', 'role_name']);
});

test('add user with non-existing role is rejected', function () {
    $admin    = userWithRole('Admin');
    $response = $this->actingAs($admin)->post(route('pos.settings.user.add'), [
        'name'      => 'Ghost',
        'password'  => '1234',
        'role_name' => 'NonExistingRole',
    ]);

    $response->assertSessionHasErrors('role_name');
});

// ─── Admin: edit user ─────────────────────────────────────────────────────────

test('admin can edit a user name', function () {
    $admin  = userWithRole('Admin');
    $target = userWithRole('Editor');

    $this->actingAs($admin)->post(route('pos.settings.user.edit', $target), [
        'name'      => 'Updated Name',
        'role_name' => 'Editor',
    ])->assertRedirect(route('pos.settings'))
      ->assertSessionHas('success');

    expect($target->fresh()->name)->toBe('Updated Name');
});

test('admin can change a user pin', function () {
    $admin  = userWithRole('Admin');
    $target = User::factory()->withoutTwoFactor()->create([
        'role'     => 'Editor',
        'password' => Hash::make('oldpin'),
    ]);

    $this->actingAs($admin)->post(route('pos.settings.user.edit', $target), [
        'name'      => $target->name,
        'password'  => 'newpin88',
        'role_name' => 'Editor',
    ]);

    expect(Hash::check('newpin88', $target->fresh()->password))->toBeTrue();
    expect(Hash::check('oldpin', $target->fresh()->password))->toBeFalse();
});

test('admin can change user role via edit', function () {
    $admin  = userWithRole('Admin');
    $target = userWithRole('Editor');

    $this->actingAs($admin)->post(route('pos.settings.user.edit', $target), [
        'name'      => $target->name,
        'role_name' => 'Viewer',
    ]);

    expect($target->fresh()->role)->toBe('Viewer');
});

test('pin is unchanged when not provided during edit', function () {
    $admin          = userWithRole('Admin');
    $originalHashed = Hash::make('mypin');
    $target         = User::factory()->withoutTwoFactor()->create([
        'role'     => 'Editor',
        'password' => $originalHashed,
    ]);

    $this->actingAs($admin)->post(route('pos.settings.user.edit', $target), [
        'name'      => 'Same Name',
        'role_name' => 'Editor',
        // password omitted
    ]);

    // Password hash must remain identical (not rehashed to empty string)
    expect($target->fresh()->password)->toBe($originalHashed);
});

// ─── Admin: delete user ───────────────────────────────────────────────────────

test('admin can delete another user', function () {
    $admin  = userWithRole('Admin');
    $target = userWithRole('Editor');

    $this->actingAs($admin)->delete(route('pos.settings.user.delete', $target))
         ->assertRedirect(route('pos.settings'))
         ->assertSessionHas('success');

    expect(User::query()->find($target->id))->toBeNull();
});

test('admin cannot delete their own account', function () {
    $admin = userWithRole('Admin');

    $this->actingAs($admin)->delete(route('pos.settings.user.delete', $admin))
         ->assertRedirect(route('pos.settings'))
         ->assertSessionHas('error');

    expect(User::query()->find($admin->id))->not->toBeNull();
});

// ─── Non-admin: permission enforcement ───────────────────────────────────────

test('editor without users.manage cannot add a user', function () {
    $editor = userWithRole('Editor'); // Editor role has no users.manage

    $response = $this->actingAs($editor)->post(route('pos.settings.user.add'), [
        'name'      => 'Hacker',
        'password'  => '1234',
        'role_name' => 'Editor',
    ]);

    $response->assertStatus(403);
    expect(User::query()->where('name', 'Hacker')->exists())->toBeFalse();
});

test('editor without users.manage cannot edit another user', function () {
    $editor = userWithRole('Editor');
    $target = userWithRole('Viewer');

    $response = $this->actingAs($editor)->post(route('pos.settings.user.edit', $target), [
        'name'      => 'ChangedName',
        'role_name' => 'Viewer',
    ]);

    $response->assertStatus(403);
    expect($target->fresh()->name)->not->toBe('ChangedName');
});

test('editor without users.manage cannot delete a user', function () {
    $editor = userWithRole('Editor');
    $target = userWithRole('Viewer');

    $response = $this->actingAs($editor)->delete(route('pos.settings.user.delete', $target));

    $response->assertStatus(403);
    expect(User::query()->find($target->id))->not->toBeNull();
});
