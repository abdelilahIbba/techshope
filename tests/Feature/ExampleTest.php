<?php

it('returns a successful response', function () {
    // Root / redirects to login for guests.
    $response = $this->get('/');

    $response->assertRedirect();
});
