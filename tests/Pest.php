<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

// ─── Shared test helpers ──────────────────────────────────────────────────────

use App\Models\License;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

/**
 * Insert/update a valid active license so CheckLicense middleware passes.
 * Uses DB::table to bypass Eloquent mass-assignment and guarantee id = 1.
 */
function activeLicense(): void
{
    License::flushCache();
    \DB::table('licenses')->updateOrInsert(
        ['id' => 1],
        [
            'key'          => 'DRG-TEST',
            'period'       => '1_year',
            'activated_at' => now(),
            'last_seen_at' => now(),
            'expires_at'   => now()->addYear(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]
    );
    License::flushCache();
}

/**
 * Create a User with the given role string (no 2FA).
 */
function userWithRole(string $role): User
{
    return User::factory()->withoutTwoFactor()->create(['role' => $role]);
}

/**
 * Upsert a Role with exactly the given permission keys.
 * Safe to call multiple times; uses firstOrCreate to avoid duplicate-name errors.
 */
function roleWith(string $roleName, array $permissionKeys): Role
{
    $role = Role::query()->firstOrCreate(
        ['name' => $roleName],
        ['description' => "Test: {$roleName}"]
    );
    $ids = Permission::query()->whereIn('key', $permissionKeys)->pluck('id');
    $role->permissions()->sync($ids);
    return $role;
}

/**
 * Ensure a single permission row exists.
 */
function ensurePermission(string $key, string $page = 'Settings', string $action = 'Edit'): Permission
{
    return Permission::query()->firstOrCreate(
        ['key' => $key],
        ['label' => $key, 'page' => $page, 'action' => $action]
    );
}
