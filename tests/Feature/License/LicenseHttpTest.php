<?php

use App\Services\LicenseService;

// ─── License HTTP tests ───────────────────────────────────────────────────────

test('admin can generate a license key via ajax', function () {
    $admin = userWithRole('Admin');

    $response = $this->actingAs($admin)->postJson(route('license.generate'), [
        'period' => '1_year',
    ]);

    $response->assertOk()
             ->assertJsonStructure(['key']);

    expect($response->json('key'))->toStartWith('DRG-');
});

test('non-admin cannot generate a license key', function () {
    activeLicense();

    ensurePermission('settings.view');
    roleWith('Editor', ['settings.view']);

    $editor = userWithRole('Editor');

    $response = $this->actingAs($editor)->postJson(route('license.generate'), [
        'period' => '1_year',
    ]);

    $response->assertStatus(403);
});

test('superuser cannot generate a license key', function () {
    activeLicense();

    ensurePermission('settings.view');
    ensurePermission('roles.manage');
    roleWith('SuperUser', ['settings.view', 'roles.manage']);

    $superUser = userWithRole('SuperUser');

    $response = $this->actingAs($superUser)->postJson(route('license.generate'), [
        'period' => '1_year',
    ]);

    $response->assertStatus(403);
});

test('guest cannot generate a license key', function () {
    $response = $this->postJson(route('license.generate'), ['period' => '1_year']);

    $response->assertStatus(401);
});

test('license generate validates the period field', function () {
    $admin = userWithRole('Admin');

    $response = $this->actingAs($admin)->postJson(route('license.generate'), [
        'period' => 'invalid_period',
    ]);

    $response->assertStatus(422);
});

test('valid license key can be activated via post', function () {
    $service = new LicenseService();
    $key     = $service->generate('1_year');

    $user     = userWithRole('Editor');
    $response = $this->actingAs($user)->post(route('license.store'), [
        'license_key' => $key,
    ]);

    $response->assertRedirect(route('pos.index'));
    $response->assertSessionHas('success');
});

test('invalid license key shows error on activation page', function () {
    $user     = userWithRole('Editor');
    $response = $this->actingAs($user)->post(route('license.store'), [
        'license_key' => 'DRG-BADBADKEY.0000000000000000000000000000000000',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('license_key');
});

test('too-short license key fails validation', function () {
    $user     = userWithRole('Editor');
    $response = $this->actingAs($user)->post(route('license.store'), [
        'license_key' => 'DRG-X',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('license_key');
});

test('non-admin without license is redirected to activation page', function () {
    // No license record → CheckLicense middleware redirects non-Admin away from POS routes
    ensurePermission('dashboard.view', 'Dashboard', 'View');
    roleWith('Editor', ['dashboard.view']);

    $editor = userWithRole('Editor');

    $response = $this->actingAs($editor)->get(route('pos.dashboard'));

    $response->assertRedirect(route('license.activate'));
});

test('admin bypasses license check even without an active license', function () {
    // Do NOT call activeLicense() — Admin must bypass regardless
    $admin = userWithRole('Admin');

    $response = $this->actingAs($admin)->get(route('pos.index'));

    // Admin sees the page, not a redirect to activation
    $response->assertOk();
});
