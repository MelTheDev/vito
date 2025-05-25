<?php

namespace App\Http\Controllers;

use App\Actions\Site\CreateSite;
use App\Actions\Site\DeleteSite;
use App\Exceptions\SSHError;
use App\Http\Resources\ServerLogResource;
use App\Http\Resources\SiteResource;
use App\Models\Server;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\RouteAttributes\Attributes\Delete;
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

    #[Get('/servers/{server}/sites/{site}', name: 'sites.show')]
    public function show(Server $server, Site $site): Response
    {
        $this->authorize('view', [$site, $server]);

        return Inertia::render('sites/show', [
            'site' => SiteResource::make($site),
            'logs' => ServerLogResource::collection($site->logs()->latest()->simplePaginate(config('web.pagination_size'), pageName: 'logsPage')),
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

        return redirect()->route('sites.show', ['server' => $server, 'site' => $site])
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
                return redirect()->route('sites.show', ['server' => $server->id, 'site' => $site->id]);
            }

            return redirect()->route($previousRoute->getName(), ['server' => $server, 'site' => $site->id]);
        }

        return redirect()->route('sites.show', ['server' => $server->id, 'site' => $site->id]);
    }

    /**
     * @throws SSHError
     */
    #[Delete('/servers/{server}/sites/{site}', name: 'sites.destroy')]
    public function destroy(Server $server, Site $site): RedirectResponse
    {
        $this->authorize('delete', [$site, $server]);

        app(DeleteSite::class)->delete($site);

        return redirect()->route('sites', ['server' => $server])
            ->with('success', 'Site deleted successfully.');
    }
}
