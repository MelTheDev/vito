<?php

namespace Tests\Feature;

use App\Enums\FirewallRuleStatus;
use App\Facades\SSH;
use App\Models\FirewallRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class FirewallTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_firewall_rule(): void
    {
        SSH::fake();

        $this->actingAs($this->user);

        $this->post(route('firewall.store', ['server' => $this->server]), [
            'name' => 'Test',
            'type' => 'allow',
            'protocol' => 'tcp',
            'port' => '1234',
            'source' => '0.0.0.0',
            'mask' => '1',
        ])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('firewall_rules', [
            'port' => '1234',
            'status' => FirewallRuleStatus::READY,
        ]);
    }

    public function test_see_firewall_rules(): void
    {
        $this->actingAs($this->user);

        FirewallRule::factory()->create([
            'server_id' => $this->server->id,
        ]);

        $this->get(route('firewall', $this->server))
            ->assertSuccessful()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('firewall/index'));
    }

    public function test_delete_firewall_rule(): void
    {
        SSH::fake();

        $this->actingAs($this->user);

        $rule = FirewallRule::factory()->create([
            'server_id' => $this->server->id,
        ]);

        $this->delete(route('firewall.destroy', [
            'server' => $this->server,
            'firewallRule' => $rule,
        ]))->assertSessionDoesntHaveErrors();

        $this->assertDatabaseMissing('firewall_rules', [
            'id' => $rule->id,
        ]);
    }
}
