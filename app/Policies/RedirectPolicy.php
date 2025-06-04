<?php

namespace App\Policies;

use App\Models\Redirect;
use App\Models\Server;
use App\Models\Site;
use App\Models\User;

class RedirectPolicy
{
    public function viewAny(User $user, Site $site, Server $server): bool
    {
        return ($user->isAdmin() || $server->project->users->contains($user)) &&
            $server->isReady() &&
            $site->isReady();
    }

    public function view(User $user, Redirect $redirect, Site $site, Server $server): bool
    {
        return ($user->isAdmin() || $site->server->project->users->contains($user))
            && $site->server_id === $server->id
            && $site->server->isReady()
            && $redirect->site_id === $site->id;
    }

    public function create(User $user, Site $site, Server $server): bool
    {
        return ($user->isAdmin() || $site->server->project->users->contains($user))
            && $site->server_id === $server->id
            && $site->server->isReady();
    }

    public function delete(User $user, Redirect $redirect, Site $site, Server $server): bool
    {
        return ($user->isAdmin() || $site->server->project->users->contains($user))
            && $site->server_id === $server->id
            && $server->isReady()
            && $redirect->site_id === $site->id;
    }
}
