<?php

namespace App\Console\Commands;

use App\Enums\ServerStatus;
use App\Models\Server;
use Illuminate\Console\Command;

class CheckServersConnectionCommand extends Command
{
    protected $signature = 'servers:check';

    protected $description = 'Check servers connection status';

    public function handle(): void
    {
        Server::query()->whereIn('status', [
            ServerStatus::READY,
            ServerStatus::DISCONNECTED,
        ])->chunk(50, function ($servers) {
            /** @var Server $server */
            foreach ($servers as $server) {
                dispatch(function () use ($server) {
                    $server->checkConnection();
                })->onConnection('ssh');
            }
        });
    }
}
