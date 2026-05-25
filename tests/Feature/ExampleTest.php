<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_root_redirects_to_login(): void
    {
        $response = $this->get('/');

        // Root route redirects unauthenticated visitors to /admin/login
        $response->assertRedirect('/admin/login');
    }
}
