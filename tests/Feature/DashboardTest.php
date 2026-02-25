<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('authenticated users are redirected to pos.dashboard', function () {
    // /dashboard is just a redirect to /pos/dashboard.
    // Admin role bypasses the license check.
    $this->actingAs(User::factory()->create(['role' => 'Admin']));

    $this->get(route('dashboard'))->assertRedirect(route('pos.dashboard'));
});