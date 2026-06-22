<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_root_redirects_to_projects_index(): void
    {
        // / now redirects to projects.index per blueprint §8
        $this->get('/')->assertRedirect(route('projects.index'));
    }
}
