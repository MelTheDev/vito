<?php

namespace App\Actions\CronJob;

use App\Enums\CronjobStatus;
use App\Exceptions\SSHError;
use App\Models\CronJob;
use App\Models\Server;

class EnableCronJob
{
    /**
     * @throws SSHError
     */
    public function enable(Server $server, CronJob $cronJob): void
    {
        $cronJob->status = CronjobStatus::ENABLING;
        $cronJob->save();

        $server->cron()->update($cronJob->user, CronJob::crontab($server, $cronJob->user));
        $cronJob->status = CronjobStatus::READY;
        $cronJob->save();
    }
}
