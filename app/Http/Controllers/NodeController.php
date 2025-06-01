<?php

namespace App\Http\Controllers;

use App\Actions\NodeJS\ChangeDefaultCli;
use App\Exceptions\SSHError;
use App\Http\Resources\ServiceResource;
use App\Models\Server;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('servers/{server}/node')]
#[Middleware(['auth', 'has-project'])]
class NodeController extends Controller
{
    #[Get('/', name: 'node')]
    public function index(Server $server): Response
    {
        $this->authorize('viewAny', [Service::class, $server]);

        $installedVersions = Service::query()
            ->where('type', 'nodejs')
            ->where('server_id', $server->id)
            ->simplePaginate(config('web.pagination_size'));

        return Inertia::render('node/index', [
            'installedVersions' => ServiceResource::collection($installedVersions),
        ]);
    }

    /**
     * @throws SSHError
     */
    #[Post('/{service}/default-cli', name: 'node.default-cli')]
    public function defaultCli(Request $request, Server $server, Service $service): RedirectResponse
    {
        $this->authorize('update', $service);

        app(ChangeDefaultCli::class)->change($server, $request->input());

        return back()->with('success', 'Default Node CLI changed to '.$service->version.'.');
    }
}
