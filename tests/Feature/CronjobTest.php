<?php

namespace Tests\Feature;

use App\Enums\CronjobStatus;
use App\Facades\SSH;
use App\Models\CronJob;
use App\Models\Server;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class CronjobTest extends TestCase
{
    use RefreshDatabase;

    public function test_see_cronjobs_list(): void
    {
        $this->actingAs($this->user);

        CronJob::factory()->create([
            'server_id' => $this->server->id,
        ]);

        $this->get(route('cronjobs', $this->server))
            ->assertSuccessful()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('cronjobs/index'));

    }

    public function test_delete_cronjob(): void
    {
        SSH::fake();

        $this->actingAs($this->user);

        /** @var CronJob $cronjob */
        $cronjob = CronJob::factory()->create([
            'server_id' => $this->server->id,
            'user' => 'vito',
        ]);

        $this->delete(route('cronjobs.destroy', [
            'server' => $this->server,
            'cronJob' => $cronjob,
        ]));

        $this->assertDatabaseMissing('cron_jobs', [
            'id' => $cronjob->id,
        ]);

        SSH::assertExecutedContains("echo '' | sudo -u vito crontab -");
        SSH::assertExecutedContains('sudo -u vito crontab -l');
    }

    public function test_create_cronjob(): void
    {
        SSH::fake();

        $this->actingAs($this->user);

        $this->post(route('cronjobs.store', ['server' => $this->server]), [
            'command' => 'ls -la',
            'user' => 'vito',
            'frequency' => '* * * * *',
        ])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('cron_jobs', [
            'server_id' => $this->server->id,
            'command' => 'ls -la',
            'user' => 'vito',
            'frequency' => '* * * * *',
            'status' => CronjobStatus::READY,
        ]);

        SSH::assertExecutedContains("echo '* * * * * ls -la' | sudo -u vito crontab -");
        SSH::assertExecutedContains('sudo -u vito crontab -l');
    }

    public function test_create_cronjob_for_isolated_user(): void
    {
        SSH::fake();
        $this->actingAs($this->user);

        $this->site->user = 'example';
        $this->site->save();

        $this->post(route('cronjobs.store', ['server' => $this->server]), [
            'command' => 'ls -la',
            'user' => 'example',
            'frequency' => '* * * * *',
        ])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('cron_jobs', [
            'server_id' => $this->server->id,
            'user' => 'example',
        ]);

        SSH::assertExecutedContains("echo '* * * * * ls -la' | sudo -u example crontab -");
        SSH::assertExecutedContains('sudo -u example crontab -l');
    }

    public function test_cannot_create_cronjob_for_non_existing_user(): void
    {
        SSH::fake();
        $this->actingAs($this->user);

        $this->post(route('cronjobs.store', ['server' => $this->server]), [
            'command' => 'ls -la',
            'user' => 'example',
            'frequency' => '* * * * *',
        ])
            ->assertSessionHasErrors();

        $this->assertDatabaseMissing('cron_jobs', [
            'server_id' => $this->server->id,
            'user' => 'example',
        ]);
    }

    public function test_cannot_create_cronjob_for_user_on_another_server(): void
    {
        SSH::fake();
        $this->actingAs($this->user);

        Site::factory()->create([
            'server_id' => Server::factory()->create(['user_id' => 1])->id,
            'user' => 'example',
        ]);

        $this->post(route('cronjobs.store', ['server' => $this->server]), [
            'command' => 'ls -la',
            'user' => 'example',
            'frequency' => '* * * * *',
        ])
            ->assertSessionHasErrors();

        $this->assertDatabaseMissing('cron_jobs', [
            'user' => 'example',
        ]);
    }

    public function test_create_custom_cronjob()
    {
        SSH::fake();

        $this->actingAs($this->user);

        $this->post(route('cronjobs.store', ['server' => $this->server]), [
            'command' => 'ls -la',
            'user' => 'vito',
            'frequency' => 'custom',
            'custom' => '* * * 1 1',
        ])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('cron_jobs', [
            'server_id' => $this->server->id,
            'command' => 'ls -la',
            'user' => 'vito',
            'frequency' => '* * * 1 1',
            'status' => CronjobStatus::READY,
        ]);

        SSH::assertExecutedContains("echo '* * * 1 1 ls -la' | sudo -u vito crontab -");
        SSH::assertExecutedContains('sudo -u vito crontab -l');
    }

    public function test_enable_cronjob()
    {
        SSH::fake();

        $this->actingAs($this->user);

        /** @var CronJob $cronjob */
        $cronjob = CronJob::factory()->create([
            'server_id' => $this->server->id,
            'user' => 'vito',
            'command' => 'ls -la',
            'frequency' => '* * * 1 1',
            'status' => CronjobStatus::DISABLED,
        ]);

        $this->post(route('cronjobs.enable', [
            'server' => $this->server,
            'cronJob' => $cronjob,
        ]))
            ->assertSessionDoesntHaveErrors();

        $cronjob->refresh();

        $this->assertEquals(CronjobStatus::READY, $cronjob->status);

        SSH::assertExecutedContains("echo '* * * 1 1 ls -la' | sudo -u vito crontab -");
        SSH::assertExecutedContains('sudo -u vito crontab -l');
    }

    public function test_disable_cronjob(): void
    {
        SSH::fake();

        $this->actingAs($this->user);

        /** @var CronJob $cronjob */
        $cronjob = CronJob::factory()->create([
            'server_id' => $this->server->id,
            'user' => 'vito',
            'command' => 'ls -la',
            'frequency' => '* * * 1 1',
            'status' => CronjobStatus::READY,
        ]);

        $this->post(route('cronjobs.disable', [
            'server' => $this->server,
            'cronJob' => $cronjob,
        ]))
            ->assertSessionDoesntHaveErrors();

        $cronjob->refresh();

        $this->assertEquals(CronjobStatus::DISABLED, $cronjob->status);

        SSH::assertExecutedContains("echo '' | sudo -u vito crontab -");
        SSH::assertExecutedContains('sudo -u vito crontab -l');
    }
}
