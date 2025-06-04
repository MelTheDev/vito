<?php

namespace Tests\Feature;

use App\Enums\PHPIniType;
use App\Enums\ServiceStatus;
use App\Facades\SSH;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PHPTest extends TestCase
{
    use RefreshDatabase;

    public function test_change_default_php_cli(): void
    {
        SSH::fake();

        $this->actingAs($this->user);

        $php = Service::factory()->create([
            'server_id' => $this->server->id,
            'type' => 'php',
            'type_data' => [
                'extensions' => [],
            ],
            'name' => 'php',
            'version' => '8.1',
            'status' => ServiceStatus::READY,
            'is_default' => false,
        ]);

        $this->post(route('php.default-cli', [
            'server' => $this->server,
            'service' => $php->id,
        ]), [
            'version' => '8.1',
        ])
            ->assertSessionDoesntHaveErrors();

        $php->refresh();

        $this->assertTrue($php->is_default);
    }

    public function test_install_extension(): void
    {
        SSH::fake('output... [PHP Modules] gmp');

        $this->actingAs($this->user);

        $php = $this->server->php('8.2');

        $this->post(route('php.install-extension', [
            'server' => $this->server,
            'service' => $php->id,
        ]), [
            'version' => '8.2',
            'extension' => 'gmp',
        ])
            ->assertSessionDoesntHaveErrors();

        $this->assertContains('gmp', $php->refresh()->type_data['extensions']);
    }

    #[DataProvider('php_ini_data')]
    public function test_get_php_ini(string $version, string $type): void
    {
        SSH::fake('[PHP ini]');

        $this->actingAs($this->user);

        $php = $this->server->php('8.2');

        $this->get(route('php.ini', [
            'server' => $this->server,
            'service' => $php->id,
            'version' => '8.2',
            'type' => $type,
        ]))
            ->assertSessionDoesntHaveErrors();
    }

    /**
     * @return array<array<int, string>>
     */
    public static function php_ini_data(): array
    {
        return [
            ['8.2', PHPIniType::FPM],
            ['8.2', PHPIniType::CLI],
        ];
    }
}
