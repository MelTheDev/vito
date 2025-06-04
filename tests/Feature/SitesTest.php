<?php

namespace Tests\Feature;

use App\Enums\LoadBalancerMethod;
use App\Enums\SiteStatus;
use App\Enums\SiteType;
use App\Enums\SourceControl;
use App\Facades\SSH;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class SitesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $inputs
     *
     * @dataProvider create_data
     */
    public function test_create_site(array $inputs): void
    {
        SSH::fake();

        Http::fake([
            'https://api.github.com/repos/*' => Http::response([
            ], 201),
        ]);

        $this->actingAs($this->user);

        /** @var \App\Models\SourceControl $sourceControl */
        $sourceControl = \App\Models\SourceControl::factory()->create([
            'provider' => SourceControl::GITHUB,
        ]);

        $inputs['source_control'] = $sourceControl->id;

        $this->post(route('sites.store', ['server' => $this->server]), $inputs)
            ->assertSessionDoesntHaveErrors();

        $expectedUser = empty($inputs['user']) ? $this->server->getSshUser() : $inputs['user'];
        $this->assertDatabaseHas('sites', [
            'domain' => $inputs['domain'],
            'aliases' => json_encode($inputs['aliases'] ?? []),
            'status' => SiteStatus::READY,
            'user' => $expectedUser,
            'path' => '/home/'.$expectedUser.'/'.$inputs['domain'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $inputs
     *
     * @dataProvider failure_create_data
     */
    public function test_isolated_user_failure(array $inputs): void
    {
        SSH::fake();
        $this->actingAs($this->user);

        $this->post(route('sites.store', ['server' => $this->server]), $inputs)
            ->assertSessionHasErrors();
    }

    /**
     * @dataProvider create_failure_data
     */
    public function test_create_site_failed_due_to_source_control(int $status): void
    {
        $inputs = [
            'type' => SiteType::LARAVEL,
            'domain' => 'example.com',
            'aliases' => ['www.example.com'],
            'php_version' => '8.2',
            'web_directory' => 'public',
            'repository' => 'test/test',
            'branch' => 'main',
            'composer' => true,
        ];

        SSH::fake();

        Http::fake([
            'https://api.github.com/repos/*' => Http::response([
            ], $status),
        ]);

        $this->actingAs($this->user);

        /** @var \App\Models\SourceControl $sourceControl */
        $sourceControl = \App\Models\SourceControl::factory()->create([
            'provider' => SourceControl::GITHUB,
        ]);

        $inputs['source_control'] = $sourceControl->id;

        $this->post(route('sites.store', ['server' => $this->server]), $inputs)
            ->assertSessionHasErrors();

        $this->assertDatabaseMissing('sites', [
            'domain' => 'example.com',
            'status' => SiteStatus::READY,
        ]);
    }

    public function test_see_sites_list(): void
    {
        $this->actingAs($this->user);

        Site::factory()->create([
            'server_id' => $this->server->id,
        ]);

        $this->get(route('sites', [
            'server' => $this->server,
        ]))
            ->assertSuccessful()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('sites/index'));
    }

    public function test_delete_site(): void
    {
        SSH::fake();

        $this->actingAs($this->user);

        $site = Site::factory()->create([
            'server_id' => $this->server->id,
        ]);

        $this->delete(route('site-settings.destroy', [
            'server' => $this->server->id,
            'site' => $site->id,
        ]), [
            'domain' => $site->domain,
        ])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseMissing('sites', [
            'id' => $site->id,
        ]);
    }

    public function test_change_php_version(): void
    {
        SSH::fake();

        $this->actingAs($this->user);

        $site = Site::factory()->create([
            'server_id' => $this->server->id,
        ]);

        $this->delete(route('site-settings.update-php-version', [
            'server' => $this->server->id,
            'site' => $site->id,
        ]), [
            'version' => '8.2',
        ])
            ->assertSessionDoesntHaveErrors();

        $site->refresh();

        $this->assertEquals('8.2', $site->php_version);
    }

    public function test_update_source_control(): void
    {
        SSH::fake();

        $this->actingAs($this->user);

        Http::fake([
            'https://api.github.com/repos/*' => Http::response([
            ], 201),
        ]);

        /** @var \App\Models\SourceControl $sourceControl */
        $sourceControl = \App\Models\SourceControl::factory()->create([
            'provider' => SourceControl::GITHUB,
        ]);

        $this->patch(route('site-settings.update-source-control', [
            'server' => $this->server->id,
            'site' => $this->site,
        ]), [
            'source_control' => $sourceControl->id,
        ])
            ->assertSessionDoesntHaveErrors();

        $this->site->refresh();

        $this->assertEquals($sourceControl->id, $this->site->source_control_id);
    }

    public function test_failed_to_update_source_control(): void
    {
        SSH::fake();

        $this->actingAs($this->user);

        Http::fake([
            'https://api.github.com/repos/*' => Http::response([
            ], 404),
        ]);

        /** @var \App\Models\SourceControl $sourceControl */
        $sourceControl = \App\Models\SourceControl::factory()->create([
            'provider' => SourceControl::GITHUB,
        ]);

        $this->patch(route('site-settings.update-source-control', [
            'server' => $this->server->id,
            'site' => $this->site,
        ]), [
            'source_control' => $sourceControl->id,
        ])
            ->assertSessionHasErrors();
    }

    public function test_update_v_host(): void
    {
        SSH::fake();

        $this->actingAs($this->user);

        Site::factory()->create([
            'server_id' => $this->server->id,
        ]);

        $this->patch(route('site-settings.update-vhost', [
            'server' => $this->server->id,
            'site' => $this->site,
        ]), [
            'vhost' => 'test',
        ])
            ->assertSessionDoesntHaveErrors();
    }

    public function test_see_logs(): void
    {
        $this->actingAs($this->user);

        $this->get(route('sites.logs', [
            'server' => $this->server,
            'site' => $this->site,
        ]))
            ->assertSuccessful()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('sites/logs'));
    }

    public function test_change_branch(): void
    {
        SSH::fake();

        $this->actingAs($this->user);

        $this->patch(route('site-settings.update-branch', [
            'server' => $this->server->id,
            'site' => $this->site,
        ]), [
            'branch' => 'master',
        ])
            ->assertSessionDoesntHaveErrors();

        $this->site->refresh();
        $this->assertEquals('master', $this->site->branch);

        SSH::assertExecutedContains('git checkout -f master');
    }

    /**
     * @return array<array<string, mixed>>
     */
    public static function failure_create_data(): array
    {
        return [
            [
                [
                    'type' => SiteType::PHP_BLANK,
                    'domain' => 'example.com',
                    'aliases' => ['www.example.com'],
                    'php_version' => '8.2',
                    'web_directory' => 'public',
                    'user' => 'a',
                ],
            ],
            [
                [
                    'type' => SiteType::PHP_BLANK,
                    'domain' => 'example.com',
                    'aliases' => ['www.example.com'],
                    'php_version' => '8.2',
                    'web_directory' => 'public',
                    'user' => 'root',
                ],
            ],
            [
                [
                    'type' => SiteType::PHP_BLANK,
                    'domain' => 'example.com',
                    'aliases' => ['www.example.com'],
                    'php_version' => '8.2',
                    'web_directory' => 'public',
                    'user' => 'vito',
                ],
            ],
            [
                [
                    'type' => SiteType::PHP_BLANK,
                    'domain' => 'example.com',
                    'aliases' => ['www.example.com'],
                    'php_version' => '8.2',
                    'web_directory' => 'public',
                    'user' => '123',
                ],
            ],
            [
                [
                    'type' => SiteType::PHP_BLANK,
                    'domain' => 'example.com',
                    'aliases' => ['www.example.com'],
                    'php_version' => '8.2',
                    'web_directory' => 'public',
                    'user' => 'qwertyuiopasdfghjklzxcvbnmqwertyu',
                ],
            ],
        ];
    }

    /**
     * @return array<array<array<string, mixed>>>
     */
    public static function create_data(): array
    {
        return [
            [
                [
                    'type' => SiteType::LARAVEL,
                    'domain' => 'example.com',
                    'aliases' => ['www.example.com', 'www2.example.com'],
                    'php_version' => '8.2',
                    'web_directory' => 'public',
                    'repository' => 'test/test',
                    'branch' => 'main',
                    'composer' => true,
                ],
            ],
            [
                [
                    'type' => SiteType::LARAVEL,
                    'domain' => 'example.com',
                    'aliases' => ['www.example.com', 'www2.example.com'],
                    'php_version' => '8.2',
                    'web_directory' => 'public',
                    'repository' => 'test/test',
                    'branch' => 'main',
                    'composer' => true,
                    'user' => 'example',
                ],
            ],
            [
                [
                    'type' => SiteType::WORDPRESS,
                    'domain' => 'example.com',
                    'aliases' => ['www.example.com'],
                    'php_version' => '8.2',
                    'title' => 'Example',
                    'username' => 'example',
                    'email' => 'email@example.com',
                    'password' => 'password',
                    'database' => 'example',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'database_user' => 'example',
                    'database_password' => 'password',
                ],
            ],
            [
                [
                    'type' => SiteType::WORDPRESS,
                    'domain' => 'example.com',
                    'aliases' => ['www.example.com'],
                    'php_version' => '8.2',
                    'title' => 'Example',
                    'username' => 'example',
                    'email' => 'email@example.com',
                    'password' => 'password',
                    'database' => 'example',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'database_user' => 'example',
                    'database_password' => 'password',
                    'user' => 'example',
                ],
            ],
            [
                [
                    'type' => SiteType::PHP_BLANK,
                    'domain' => 'example.com',
                    'aliases' => ['www.example.com'],
                    'php_version' => '8.2',
                    'web_directory' => 'public',
                ],
            ],
            [
                [
                    'type' => SiteType::PHP_BLANK,
                    'domain' => 'example.com',
                    'aliases' => ['www.example.com'],
                    'php_version' => '8.2',
                    'web_directory' => 'public',
                    'user' => 'example',
                ],
            ],
            [
                [
                    'type' => SiteType::PHPMYADMIN,
                    'domain' => 'example.com',
                    'aliases' => ['www.example.com'],
                    'php_version' => '8.2',
                    'version' => '5.1.2',
                ],
            ],
            [
                [
                    'type' => SiteType::PHPMYADMIN,
                    'domain' => 'example.com',
                    'aliases' => ['www.example.com'],
                    'php_version' => '8.2',
                    'version' => '5.1.2',
                    'user' => 'example',
                ],
            ],
            [
                [
                    'type' => SiteType::LOAD_BALANCER,
                    'domain' => 'example.com',
                    'aliases' => ['www.example.com'],
                    'user' => 'example',
                    'method' => LoadBalancerMethod::ROUND_ROBIN,
                ],
            ],
        ];
    }

    /**
     * @return array<array<int>>
     */
    public static function create_failure_data(): array
    {
        return [
            [401],
            [403],
            [404],
        ];
    }
}
