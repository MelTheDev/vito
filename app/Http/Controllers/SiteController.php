<?php

namespace App\Http\Controllers;

use App\Actions\Site\CreateSite;
use App\Http\Resources\ServerLogResource;
use App\Http\Resources\SiteResource;
use App\Models\Server;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Throwable;

#[Middleware(['auth', 'has-project'])]
class SiteController extends Controller
{
    #[Get('/sites', name: 'sites.all')]
    public function index(): Response
    {
        $sites = user()->currentProject->sites()->with('server')->latest()->simplePaginate(config('web.pagination_size'));

        return Inertia::render('sites/index', [
            'sites' => SiteResource::collection($sites),
        ]);
    }

    #[Get('/servers/{server}/sites', name: 'sites')]
    public function server(Server $server): Response
    {
        $this->authorize('viewAny', [Site::class, $server]);

        return Inertia::render('sites/index', [
            'sites' => SiteResource::collection($server->sites()->latest()->simplePaginate(config('web.pagination_size'))),
        ]);
    }

    /**
     * @throws Throwable
     */
    #[Post('/servers/{server}/sites/', name: 'sites.store')]
    public function store(Request $request, Server $server): RedirectResponse
    {
        $this->authorize('create', [Site::class, $server]);

        $site = app(CreateSite::class)->create($server, $request->all());

        return redirect()->route('application', ['server' => $server, 'site' => $site])
            ->with('info', 'Installing site, please wait...');
    }

    #[Post('/servers/{server}/sites/{site}/switch', name: 'sites.switch')]
    public function switch(Server $server, Site $site): RedirectResponse
    {
        $this->authorize('view', [$site, $server]);

        $previousUrl = URL::previous();
        $previousRequest = Request::create($previousUrl);
        $previousRoute = app('router')->getRoutes()->match($previousRequest);

        if ($previousRoute->hasParameter('site')) {
            if (count($previousRoute->parameters()) > 2) {
                return redirect()->route('application', ['server' => $server->id, 'site' => $site->id]);
            }

            return redirect()->route($previousRoute->getName(), ['server' => $server, 'site' => $site->id]);
        }

        return redirect()->route('application', ['server' => $server->id, 'site' => $site->id]);
    }

    #[Get('/servers/{server}/sites/{site}/logs', name: 'sites.logs')]
    public function logs(Server $server, Site $site): Response
    {
        $this->authorize('view', [$site, $server]);

        return Inertia::render('sites/logs', [
            'logs' => ServerLogResource::collection($site->logs()->latest()->simplePaginate(config('web.pagination_size'))),
        ]);
    }
}
