<?php

namespace App\Http\Controllers;

use App\Http\Resources\ServerLogResource;
use App\Models\Server;
use App\Models\ServerLog;
use App\Models\Site;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('servers/{server}/logs')]
#[Middleware(['auth', 'has-project'])]
class ServerLogController extends Controller
{
    #[Get('/json/{site?}', name: 'logs.json')]
    public function json(Server $server, ?Site $site = null): ResourceCollection
    {
        $this->authorize('viewAny', [ServerLog::class, $server]);

        $logs = $server->logs()
            ->when($site, fn ($query) => $query->where('site_id', $site->id))
            ->latest()
            ->simplePaginate(config('web.pagination_size'));

        return ServerLogResource::collection($logs);
    }

    #[Get('/{log}', name: 'logs.show')]
    public function show(Server $server, ServerLog $log): string
    {
        $this->authorize('view', $log);

        return $log->getContent();
    }
}
