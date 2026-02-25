<?php

use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Support\Carbon;

// ─── LicenseService unit-style tests ─────────────────────────────────────────

test('generate returns a drg-prefixed key', function () {
    $service = new LicenseService();
    $key = $service->generate('1_year');

    expect($key)->toStartWith('DRG-');
});

test('generated key contains a dot separating payload and signature', function () {
    $service = new LicenseService();
    $key = $service->generate('1_year');
    $body = substr($key, 4); // strip 'DRG-'

    expect($body)->toContain('.');
});

test('generated key passes validation', function () {
    $service = new LicenseService();
    $result = $service->validate($service->generate('3_months'));

    expect($result['valid'])->toBeTrue();
    expect($result['period'])->toBe('3_months');
});

test('validation is case-insensitive for uppercase key', function () {
    $service = new LicenseService();
    $key = strtoupper($service->generate('6_months'));
    $result = $service->validate($key);

    expect($result['valid'])->toBeTrue();
});

test('validation is case-insensitive for lowercase key', function () {
    $service = new LicenseService();
    $key = strtolower($service->generate('2_months'));
    $result = $service->validate($key);

    expect($result['valid'])->toBeTrue();
});

test('tampered signature is rejected', function () {
    $service = new LicenseService();
    $key = $service->generate('1_year');
    $tampered = substr($key, 0, -4) . 'XXXX'; // corrupt last 4 chars of signature

    $result = $service->validate($tampered);

    expect($result['valid'])->toBeFalse();
    expect($result['error'])->toContain('Signature invalide');
});

test('wrong prefix is rejected', function () {
    $service = new LicenseService();
    $key = $service->generate('1_year');
    $bad = 'BAD-' . substr($key, 4);

    $result = $service->validate($bad);

    expect($result['valid'])->toBeFalse();
    expect($result['error'])->toContain('Format de clé invalide');
});

test('key without dot separator is rejected', function () {
    $result = (new LicenseService())->validate('DRG-NODOTHERE');

    expect($result['valid'])->toBeFalse();
    expect($result['error'])->toContain('signature manquante');
});

test('expired key is rejected', function () {
    $service = new LicenseService();

    // Generate a key that appears valid but expires in the past
    Carbon::setTestNow(now()->subYear());   // set "now" to 1 year ago
    $key = $service->generate('1_week');
    Carbon::setTestNow();                   // restore real now

    $result = $service->validate($key);

    expect($result['valid'])->toBeFalse();
    expect($result['error'])->toContain('expirée');
});

test('invalid period throws InvalidArgumentException', function () {
    expect(fn () => (new LicenseService())->generate('99_years'))
        ->toThrow(\InvalidArgumentException::class);
});

test('all defined periods generate valid keys', function () {
    $service = new LicenseService();

    foreach (array_keys(LicenseService::PERIODS) as $period) {
        $result = $service->validate($service->generate($period));
        expect($result['valid'])->toBeTrue("Period {$period} should produce a valid key");
    }
});

test('activate stores the license in the database', function () {
    $service = new LicenseService();
    $key = $service->generate('1_year');

    $result = $service->activate($key);

    expect($result['valid'])->toBeTrue();
    expect(License::query()->where('id', 1)->exists())->toBeTrue();

    $stored = License::query()->find(1);
    expect(strtolower($stored->key))->toBe(strtolower($key));
    expect($stored->period)->toBe('1_year');
});

test('activate returns error for tampered key without touching database', function () {
    $service  = new LicenseService();
    $tampered = 'DRG-DEADBEEF.BADSIG00000000000000000000000000000000';

    $result = $service->activate($tampered);

    expect($result['valid'])->toBeFalse();
    expect(License::query()->where('id', 1)->exists())->toBeFalse();
});

test('active license is blocked when system clock is rolled back too far', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-01 12:00:00'));

    $service = new LicenseService();
    $service->activate($service->generate('1_year'));

    expect(License::isActive())->toBeTrue();

    Carbon::setTestNow(Carbon::parse('2026-03-01 11:40:00'));

    expect(License::isActive())->toBeFalse();

    Carbon::setTestNow();
});

test('small backward clock drift within tolerance does not block license', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-01 12:00:00'));

    $service = new LicenseService();
    $service->activate($service->generate('1_year'));

    expect(License::isActive())->toBeTrue();

    Carbon::setTestNow(Carbon::parse('2026-03-01 11:56:00'));

    expect(License::isActive())->toBeTrue();

    Carbon::setTestNow();
});
