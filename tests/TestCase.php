<?php

namespace Tests;

use App\Models\License;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Set up each test with a clean state.
     *
     * 1. Disable CSRF token verification for feature tests.
     *    Feature tests exercise business logic via HTTP, not via a browser.
     *    They don't submit _token fields, which would cause every POST/PUT/DELETE
     *    request to return 419 (Page Expired).  CSRF protection remains fully
     *    active in the real application — only the test runtime bypasses it.
     *
     * 2. Flush the License in-process static cache.
     *    License::$fetched / $sharedInstance survive across tests (same PHP
     *    process), but RefreshDatabase rolls back the DB between tests.
     *    Without flushing, a subsequent test's CheckLicense middleware gets a
     *    stale cached License that no longer exists in the DB, causing spurious
     *    302 redirects to the license activation page.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
        License::flushCache();
    }
}
