<?php

namespace Tests\Feature;

use App\Enums\Database;
use App\Enums\OperatingSystem;
use App\Enums\ServerProvider;
use App\Enums\ServerStatus;
use App\Enums\ServiceStatus;
use App\Enums\Webserver;
use App\Facades\SSH;
use App\NotificationChannels\Email\NotificationMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ServerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_regular_server(): void
    {
        $this->actingAs($this->user);

        SSH::fake('Active: active'); // fake output for service installations

        $this->post(route('servers.store', [
            'provider' => ServerProvider::CUSTOM,
            'name' => 'test',
            'ip' => '1.1.1.1',
            'port' => '22',
            'os' => OperatingSystem::UBUNTU22,
            'webserver' => Webserver::NGINX,
            'database' => Database::MYSQL80,
            'php' => '8.2',
        ]))
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('servers', [
            'name' => 'test',
            'ip' => '1.1.1.1',
            'status' => ServerStatus::READY,
        ]);

        $this->assertDatabaseHas('services', [
            'server_id' => 1,
            'type' => 'php',
            'version' => '8.2',
            'status' => ServiceStatus::READY,
        ]);

        $this->assertDatabaseHas('services', [
            'server_id' => 1,
            'type' => 'webserver',
            'name' => 'nginx',
            'version' => 'latest',
            'status' => ServiceStatus::READY,
        ]);

        $this->assertDatabaseHas('services', [
            'server_id' => 1,
            'type' => 'database',
            'name' => 'mysql',
            'version' => '8.0',
            'status' => ServiceStatus::READY,
        ]);

        $this->assertDatabaseHas('services', [
            'server_id' => 1,
            'type' => 'firewall',
            'name' => 'ufw',
            'version' => 'latest',
            'status' => ServiceStatus::READY,
        ]);
    }

    public function test_create_regular_server_with_caddy(): void
    {
        $this->actingAs($this->user);

        SSH::fake('Active: active'); // fake output for service installations

        $this->post(route('servers.store', [
            'provider' => ServerProvider::CUSTOM,
            'name' => 'caddy-test',
            'ip' => '2.2.2.2',
            'port' => '22',
            'os' => OperatingSystem::UBUNTU22,
            'webserver' => Webserver::CADDY,
            'database' => Database::MYSQL80,
            'php' => '8.2',
        ]))
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('servers', [
            'name' => 'caddy-test',
            'ip' => '2.2.2.2',
            'status' => ServerStatus::READY,
        ]);

        $this->assertDatabaseHas('services', [
            'server_id' => 2,
            'type' => 'php',
            'version' => '8.2',
            'status' => ServiceStatus::READY,
        ]);

        $this->assertDatabaseHas('services', [
            'server_id' => 2,
            'type' => 'webserver',
            'name' => 'caddy',
            'version' => 'latest',
            'status' => ServiceStatus::READY,
        ]);

        $this->assertDatabaseHas('services', [
            'server_id' => 2,
            'type' => 'database',
            'name' => 'mysql',
            'version' => '8.0',
            'status' => ServiceStatus::READY,
        ]);

        $this->assertDatabaseHas('services', [
            'server_id' => 2,
            'type' => 'firewall',
            'name' => 'ufw',
            'version' => 'latest',
            'status' => ServiceStatus::READY,
        ]);
    }

    public function test_delete_server(): void
    {
        $this->actingAs($this->user);

        SSH::fake();

        $this->delete(route('servers.destroy', $this->server), [
            'name' => $this->server->name,
        ])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseMissing('servers', [
            'id' => $this->server->id,
        ]);
    }

    public function test_cannot_delete_on_provider(): void
    {
        Mail::fake();
        Http::fake([
            '*' => Http::response([], 401),
        ]);

        $this->actingAs($this->user);

        $provider = \App\Models\ServerProvider::factory()->create([
            'user_id' => $this->user->id,
            'provider' => ServerProvider::HETZNER,
            'credentials' => [
                'token' => 'token',
            ],
        ]);

        $this->server->update([
            'provider' => ServerProvider::HETZNER,
            'provider_id' => $provider->id,
            'provider_data' => [
                'hetzner_id' => 1,
                'ssh_key_id' => 1,
            ],
        ]);

        $this->delete(route('servers.destroy', $this->server), [
            'name' => $this->server->name,
        ])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseMissing('servers', [
            'id' => $this->server->id,
        ]);

        Mail::assertSent(NotificationMail::class);
    }

    public function test_check_connection_is_ready(): void
    {
        SSH::fake();

        $this->actingAs($this->user);

        $this->server->update(['status' => ServerStatus::DISCONNECTED]);

        $this->patch(route('servers.status', $this->server))
            ->assertSessionHas('success', 'Server status is '.ServerStatus::READY);

        $this->assertDatabaseHas('servers', [
            'id' => $this->server->id,
            'status' => ServerStatus::READY,
        ]);
    }

    public function test_connection_failed(): void
    {
        SSH::fake()->connectionWillFail();

        $this->actingAs($this->user);

        $this->server->update(['status' => ServerStatus::READY]);

        $this->patch(route('servers.status', $this->server))
            ->assertSessionHas('gray', 'Server status is '.ServerStatus::DISCONNECTED);

        $this->assertDatabaseHas('servers', [
            'id' => $this->server->id,
            'status' => ServerStatus::DISCONNECTED,
        ]);
    }

    public function test_reboot_server(): void
    {
        SSH::fake();

        $this->actingAs($this->user);

        $this->post(route('servers.reboot', $this->server))
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('servers', [
            'id' => $this->server->id,
            'status' => ServerStatus::DISCONNECTED,
        ]);
    }

    public function test_edit_server(): void
    {
        SSH::fake();

        $this->actingAs($this->user);

        $this->patch(route('server-settings.update', $this->server), [
            'name' => 'new-name',
            'ip' => $this->server->ip,
            'port' => $this->server->port,
        ])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('servers', [
            'id' => $this->server->id,
            'name' => 'new-name',
        ]);
    }

    public function test_edit_server_ip_address(): void
    {
        SSH::fake();

        $this->actingAs($this->user);

        $this->patch(route('server-settings.update', $this->server), [
            'name' => $this->server->name,
            'ip' => '2.2.2.2',
            'port' => $this->server->port,
        ])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('servers', [
            'id' => $this->server->id,
            'ip' => '2.2.2.2',
            'status' => ServerStatus::READY,
        ]);
    }

    public function test_edit_server_ip_address_and_disconnect(): void
    {
        SSH::fake()->connectionWillFail();

        $this->actingAs($this->user);

        $this->patch(route('server-settings.update', $this->server), [
            'name' => $this->server->name,
            'ip' => '2.2.2.2',
            'port' => 2222,
        ])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('servers', [
            'id' => $this->server->id,
            'ip' => '2.2.2.2',
            'port' => 2222,
            'status' => ServerStatus::DISCONNECTED,
        ]);
    }

    public function test_check_updates(): void
    {
        SSH::fake('Available updates:10');

        $this->actingAs($this->user);

        $this->post(route('servers.check-for-updates', $this->server))
            ->assertSessionDoesntHaveErrors();

        $this->server->refresh();
        $this->assertEquals(9, $this->server->updates);
    }

    public function test_update_server(): void
    {
        SSH::fake('Available updates:0');

        $this->actingAs($this->user);

        $this->post(route('servers.update', $this->server))
            ->assertSessionDoesntHaveErrors();

        $this->server->refresh();

        $this->assertEquals(ServerStatus::READY, $this->server->status);
        $this->assertEquals(0, $this->server->updates);
    }
}
