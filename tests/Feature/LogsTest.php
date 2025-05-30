<?php

namespace Tests\Feature;

use App\Models\ServerLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class LogsTest extends TestCase
{
    use RefreshDatabase;

    public function test_see_logs(): void
    {
        $this->actingAs($this->user);

        ServerLog::factory()->create([
            'server_id' => $this->server->id,
        ]);

        $this->get(route('logs', $this->server))
            ->assertSuccessful()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('server-logs/index'));
    }

    public function test_see_logs_remote(): void
    {
        $this->actingAs($this->user);

        ServerLog::factory()->create([
            'server_id' => $this->server->id,
            'is_remote' => true,
            'type' => 'remote',
            'name' => 'see-remote-log',
        ]);

        $this->get(route('logs.remote', $this->server))
            ->assertSuccessful()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('server-logs/index'));
    }

    public function test_create_remote_log(): void
    {
        $this->actingAs($this->user);

        $this->post(route('logs.store', $this->server), [
            'path' => 'test-path',
        ])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('server_logs', [
            'is_remote' => true,
            'name' => 'test-path',
        ]);
    }
}
