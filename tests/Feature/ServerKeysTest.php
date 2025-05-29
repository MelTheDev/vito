<?php

namespace Tests\Feature;

use App\Enums\SshKeyStatus;
use App\Facades\SSH;
use App\Models\SshKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ServerKeysTest extends TestCase
{
    use RefreshDatabase;

    public function test_see_server_keys(): void
    {
        $this->actingAs($this->user);

        $sshKey = SshKey::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'My first key',
            'public_key' => 'public-key-content',
        ]);

        $this->server->sshKeys()->attach($sshKey, [
            'status' => SshKeyStatus::ADDED,
        ]);

        $this->get(route('server-ssh-keys', ['server' => $this->server->id]))
            ->assertSuccessful()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('server-ssh-keys/index'));
    }

    public function test_delete_ssh_key(): void
    {
        SSH::fake();

        $this->actingAs($this->user);

        $sshKey = SshKey::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'My first key',
            'public_key' => 'public-key-content',
        ]);

        $this->server->sshKeys()->attach($sshKey, [
            'status' => SshKeyStatus::ADDED,
        ]);

        $this->delete(route('server-ssh-keys.destroy', ['server' => $this->server->id, 'sshKey' => $sshKey->id]))
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseMissing('server_ssh_keys', [
            'server_id' => $this->server->id,
            'ssh_key_id' => $sshKey->id,
        ]);
    }

    public function test_add_existing_key(): void
    {
        SSH::fake();

        $this->actingAs($this->user);

        $sshKey = SshKey::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'My first key',
            'public_key' => 'public-key-content',
        ]);

        $this->post(route('server-ssh-keys.store', ['server' => $this->server->id]), [
            'key' => $sshKey->id,
        ])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('server_ssh_keys', [
            'server_id' => $this->server->id,
            'status' => SshKeyStatus::ADDED,
        ]);
    }
}
