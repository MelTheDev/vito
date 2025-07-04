<?php

namespace App\Actions\Site;

use App\Enums\DeploymentStatus;
use App\Exceptions\DeploymentScriptIsEmptyException;
use App\Facades\Notifier;
use App\Models\Deployment;
use App\Models\ServerLog;
use App\Models\Site;
use App\Notifications\DeploymentCompleted;

class Deploy
{
    /**
     * @throws DeploymentScriptIsEmptyException
     */
    public function run(Site $site): Deployment
    {
        if ($site->sourceControl) {
            $site->sourceControl->getRepo($site->repository);
        }

        if (! $site->deploymentScript?->content) {
            throw new DeploymentScriptIsEmptyException;
        }

        $deployment = new Deployment([
            'site_id' => $site->id,
            'deployment_script_id' => $site->deploymentScript->id,
            'status' => DeploymentStatus::DEPLOYING,
        ]);
        $log = ServerLog::newLog($site->server, 'deploy-'.strtotime('now'))
            ->forSite($site);
        $log->save();
        $deployment->log_id = $log->id;
        $deployment->save();
        $lastCommit = $site->sourceControl?->provider()?->getLastCommit($site->repository, $site->branch);
        if ($lastCommit) {
            $deployment->commit_id = $lastCommit['commit_id'];
            $deployment->commit_data = $lastCommit['commit_data'];
        }
        $deployment->save();

        dispatch(function () use ($site, $deployment, $log): void {
            $site->server->os()->runScript(
                path: $site->path,
                script: $site->deploymentScript->content,
                serverLog: $log,
                user: $site->user,
                variables: $site->environmentVariables($deployment),
            );
            $deployment->status = DeploymentStatus::FINISHED;
            $deployment->save();
            Notifier::send($site, new DeploymentCompleted($deployment, $site));
        })->catch(function () use ($deployment, $site): void {
            $deployment->status = DeploymentStatus::FAILED;
            $deployment->save();
            Notifier::send($site, new DeploymentCompleted($deployment, $site));
        })->onQueue('ssh-unique');

        return $deployment;
    }
}
