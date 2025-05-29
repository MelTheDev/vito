<?php

namespace App\Actions\SshKey;

use App\Enums\SshKeyStatus;
use App\Exceptions\SSHError;
use App\Models\Server;
use App\Models\SshKey;

class DeployKeyToServer
{
    /**
     * @throws SSHError
     */
    public function deploy(Server $server, SshKey $sshKey): void
    {
        $server->sshKeys()->attach($sshKey, [
            'status' => SshKeyStatus::ADDING,
        ]);
        $server->os()->deploySSHKey($sshKey->public_key);
        $sshKey->servers()->updateExistingPivot($server->id, [
            'status' => SshKeyStatus::ADDED,
        ]);
    }
}
