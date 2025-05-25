<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('servers/{server}/services')]
#[Middleware(['auth', 'has-project'])]
class ServiceController extends Controller
{
    #[Get('{service}/versions', name: 'services.versions')]
    public function versions(Server $server, string $service): JsonResponse
    {
        $this->authorize('viewAny', [Service::class, $server]);

        $versions = [];
        $services = $server->services()->where('type', $service)->get(['version']);
        /** @var Service $service */
        foreach ($services as $service) {
            $versions[] = $service->version;
        }

        return response()->json($versions);
    }
}
