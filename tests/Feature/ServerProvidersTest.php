<?php

namespace Tests\Feature;

use App\Enums\ServerProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ServerProvidersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $input
     *
     * @throws JsonException
     */
    #[DataProvider('data')]
    public function test_connect_provider(string $provider, array $input): void
    {
        $this->actingAs($this->user);

        Http::fake();

        $data = array_merge(
            [
                'provider' => $provider,
                'name' => 'profile',
            ],
            $input
        );
        $this->post(route('server-providers.store'), $data)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('server_providers', [
            'provider' => $provider,
            'profile' => 'profile',
            'project_id' => isset($input['global']) ? null : $this->user->current_project_id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $input
     */
    #[DataProvider('data')]
    public function test_cannot_connect_to_provider(string $provider, array $input): void
    {
        $this->actingAs($this->user);

        Http::fake([
            '*' => Http::response([], 401),
        ]);

        $data = array_merge(
            [
                'provider' => $provider,
                'name' => 'profile',
            ],
            $input
        );
        $this->post(route('server-providers.store'), $data)
            ->assertSessionHasErrors('provider');

        $this->assertDatabaseMissing('server_providers', [
            'provider' => $provider,
            'profile' => 'profile',
        ]);
    }

    public function test_see_providers_list(): void
    {
        $this->actingAs($this->user);

        \App\Models\ServerProvider::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->get(route('server-providers'))
            ->assertSuccessful()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('server-providers/index'));
    }

    /**
     * @throws JsonException
     */
    #[DataProvider('data')]
    public function test_delete_provider(string $provider): void
    {
        $this->actingAs($this->user);

        $provider = \App\Models\ServerProvider::factory()->create([
            'user_id' => $this->user->id,
            'provider' => $provider,
        ]);

        $this->delete(route('server-providers.destroy', $provider))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('server-providers'));

        $this->assertDatabaseMissing('server_providers', [
            'id' => $provider->id,
        ]);
    }

    #[DataProvider('data')]
    public function test_cannot_delete_provider(string $provider): void
    {
        $this->actingAs($this->user);

        $provider = \App\Models\ServerProvider::factory()->create([
            'user_id' => $this->user->id,
            'provider' => $provider,
        ]);

        $this->server->update([
            'provider_id' => $provider->id,
        ]);

        $this->delete(route('server-providers.destroy', $provider))
            ->assertSessionHasErrors([
                'provider' => 'This server provider is being used by a server.',
            ]);

        $this->assertDatabaseHas('server_providers', [
            'id' => $provider->id,
        ]);
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function data(): array
    {
        return [
            // [
            //     ServerProvider::AWS,
            //     [
            //         'key' => 'key',
            //         'secret' => 'secret',
            //     ],
            // ],
            [
                ServerProvider::LINODE,
                [
                    'token' => 'token',
                ],
            ],
            [
                ServerProvider::LINODE,
                [
                    'token' => 'token',
                    'global' => 1,
                ],
            ],
            [
                ServerProvider::DIGITALOCEAN,
                [
                    'token' => 'token',
                ],
            ],
            [
                ServerProvider::VULTR,
                [
                    'token' => 'token',
                ],
            ],
            [
                ServerProvider::HETZNER,
                [
                    'token' => 'token',
                ],
            ],
        ];
    }
}
