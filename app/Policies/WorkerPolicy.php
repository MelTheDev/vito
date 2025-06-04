<?php

namespace App\Policies;

use App\Models\Server;
use App\Models\Site;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user, Server $server, ?Site $site = null): bool
    {
        return ($user->isAdmin() || $server->project->users->contains($user)) &&
            $server->isReady() &&
            $server->processManager();
    }

    public function view(User $user, Worker $worker, Server $server, ?Site $site = null): bool
    {
        return ($user->isAdmin() || $server->project->users->contains($user)) &&
            $server->isReady() &&
            $worker->server_id === $server->id &&
            $server->processManager();
    }

    public function create(User $user, Server $server, ?Site $site = null): bool
    {
        return ($user->isAdmin() || $server->project->users->contains($user)) &&
            $server->isReady() &&
            $server->processManager();
    }

    public function update(User $user, Worker $worker, Server $server, ?Site $site = null): bool
    {
        return ($user->isAdmin() || $server->project->users->contains($user)) &&
            $server->isReady() &&
            $worker->server_id === $server->id &&
            $server->processManager();
    }

    public function delete(User $user, Worker $worker, Server $server, ?Site $site = null): bool
    {
        return ($user->isAdmin() || $server->project->users->contains($user)) &&
            $server->isReady() &&
            $worker->server_id === $server->id &&
            $server->processManager();
    }
}
