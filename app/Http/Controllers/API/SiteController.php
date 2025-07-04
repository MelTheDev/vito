<?php

namespace App\Http\Controllers\API;

use App\Actions\Site\CreateSite;
use App\Actions\Site\Deploy;
use App\Actions\Site\UpdateAliases;
use App\Actions\Site\UpdateDeploymentScript;
use App\Actions\Site\UpdateEnv;
use App\Actions\Site\UpdateLoadBalancer;
use App\Enums\LoadBalancerMethod;
use App\Exceptions\DeploymentScriptIsEmptyException;
use App\Http\Controllers\Controller;
use App\Http\Resources\SiteResource;
use App\Models\Project;
use App\Models\Server;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;

#[Prefix('api/projects/{project}/servers/{server}/sites')]
#[Middleware(['auth:sanctum', 'can-see-project'])]
#[Group(name: 'sites')]
class SiteController extends Controller
{
    #[Get('/', name: 'api.projects.servers.sites', middleware: 'ability:read')]
    #[Endpoint(title: 'list', description: 'Get all sites.')]
    #[ResponseFromApiResource(SiteResource::class, Site::class, collection: true, paginate: 25)]
    public function index(Project $project, Server $server): ResourceCollection
    {
        $this->authorize('viewAny', [Site::class, $server]);

        $this->validateRoute($project, $server);

        return SiteResource::collection($server->sites()->simplePaginate(25));
    }

    #[Post('/', name: 'api.projects.servers.sites.create', middleware: 'ability:write')]
    #[Endpoint(title: 'create', description: 'Create a new site.')]
    #[BodyParam(name: 'type', required: true)]
    #[BodyParam(name: 'domain', required: true)]
    #[BodyParam(name: 'aliases', type: 'array')]
    #[BodyParam(name: 'php_version', description: 'One of the installed PHP Versions', required: true, example: '7.4')]
    #[BodyParam(name: 'web_directory', description: 'Required for PHP and Laravel sites', example: 'public')]
    #[BodyParam(name: 'source_control', description: 'Source control ID, Required for Sites which support source control')]
    #[BodyParam(name: 'repository', description: 'Repository, Required for Sites which support source control', example: 'organization/repository')]
    #[BodyParam(name: 'branch', description: 'Branch, Required for Sites which support source control', example: 'main')]
    #[BodyParam(name: 'composer', type: 'boolean', description: 'Run composer if site supports composer', example: true)]
    #[BodyParam(name: 'version', description: 'Version, if the site type requires a version like PHPMyAdmin', example: '5.2.1')]
    #[BodyParam(name: 'user', description: 'user, to isolate the website under a new user')]
    #[BodyParam(name: 'method', description: 'Load balancer method, Required if the site type is Load balancer', enum: [LoadBalancerMethod::ROUND_ROBIN, LoadBalancerMethod::LEAST_CONNECTIONS, LoadBalancerMethod::IP_HASH])]
    #[ResponseFromApiResource(SiteResource::class, Site::class)]
    public function create(Request $request, Project $project, Server $server): SiteResource
    {
        $this->authorize('create', [Site::class, $server]);

        $this->validateRoute($project, $server);

        $site = app(CreateSite::class)->create($server, $request->all());

        return new SiteResource($site);
    }

    #[Get('{site}', name: 'api.projects.servers.sites.show', middleware: 'ability:read')]
    #[Endpoint(title: 'show', description: 'Get a site by ID.')]
    #[ResponseFromApiResource(SiteResource::class, Site::class)]
    public function show(Project $project, Server $server, Site $site): SiteResource
    {
        $this->authorize('view', [$site, $server]);

        $this->validateRoute($project, $server, $site);

        return new SiteResource($site);
    }

    #[Delete('{site}', name: 'api.projects.servers.sites.delete', middleware: 'ability:write')]
    #[Endpoint(title: 'delete', description: 'Delete site.')]
    #[Response(status: 204)]
    public function delete(Project $project, Server $server, Site $site): \Illuminate\Http\Response
    {
        $this->authorize('delete', [$site, $server]);

        $this->validateRoute($project, $server, $site);

        $site->delete();

        return response()->noContent();
    }

    #[Post('{site}/load-balancer', name: 'api.projects.servers.sites.load-balancer', middleware: 'ability:write')]
    #[Endpoint(title: 'load-balancer', description: 'Update load balancer.')]
    #[BodyParam(name: 'method', description: 'Load balancer method, Required if the site type is Load balancer', enum: [LoadBalancerMethod::ROUND_ROBIN, LoadBalancerMethod::LEAST_CONNECTIONS, LoadBalancerMethod::IP_HASH])]
    #[BodyParam(name: 'servers', type: 'array', description: 'Array of servers including server, port, weight, backup. (server is the local IP of the server)')]
    #[Response(status: 200)]
    public function updateLoadBalancer(Request $request, Project $project, Server $server, Site $site): SiteResource
    {
        $this->authorize('update', [$site, $server]);

        $this->validateRoute($project, $server, $site);

        $this->validate($request, UpdateLoadBalancer::rules($site));

        app(UpdateLoadBalancer::class)->update($site, $request->all());

        return new SiteResource($site);
    }

    #[Put('{site}/aliases', name: 'api.projects.servers.sites.aliases', middleware: 'ability:write')]
    #[Endpoint(title: 'aliases', description: 'Update aliases.')]
    #[BodyParam(name: 'aliases', type: 'array', description: 'Array of aliases')]
    #[Response(status: 200)]
    public function updateAliases(Request $request, Project $project, Server $server, Site $site): SiteResource
    {
        $this->authorize('update', [$site, $server]);

        $this->validateRoute($project, $server, $site);

        $this->validate($request, UpdateAliases::rules());

        app(UpdateAliases::class)->update($site, $request->all());

        return new SiteResource($site);
    }

    #[Post('{site}/deploy', name: 'api.projects.servers.sites.deploy', middleware: 'ability:write')]
    #[Endpoint(title: 'deploy', description: 'Run site deployment script')]
    #[Response(status: 200)]
    public function deploy(Request $request, Project $project, Server $server, Site $site): SiteResource
    {
        $this->authorize('update', [$site, $server]);

        $this->validateRoute($project, $server, $site);

        try {
            app(Deploy::class)->run($site);

            return new SiteResource($site);
        } catch (DeploymentScriptIsEmptyException) {
            abort(422, 'Deployment script is empty');
        }
    }

    #[Put('{site}/deployment-script', name: 'api.projects.servers.sites.deployment-script', middleware: 'ability:write')]
    #[Endpoint(title: 'deployment-script', description: 'Update site deployment script')]
    #[BodyParam(name: 'script', type: 'string', description: 'Content of the deployment script')]
    #[Response(status: 204)]
    public function updateDeploymentScript(Request $request, Project $project, Server $server, Site $site): \Illuminate\Http\Response
    {
        $this->authorize('update', [$site, $server]);

        $this->validateRoute($project, $server, $site);

        $this->validate($request, UpdateDeploymentScript::rules());

        app(UpdateDeploymentScript::class)->update($site, $request->all());

        return response()->noContent();
    }

    #[Get('{site}/deployment-script', name: 'api.projects.servers.sites.deployment-script.show', middleware: 'ability:read')]
    #[Endpoint(title: 'deployment-script', description: 'Get site deployment script content')]
    #[Response(status: 200)]
    public function showDeploymentScript(Project $project, Server $server, Site $site): JsonResponse
    {
        $this->authorize('view', [$site, $server]);

        $this->validateRoute($project, $server, $site);

        return response()->json([
            'script' => $site->deploymentScript?->content,
        ]);
    }

    #[Get('{site}/env', name: 'api.projects.servers.sites.env.show', middleware: 'ability:read')]
    #[Endpoint(title: 'env', description: 'Get site .env file content')]
    #[Response(content: [
        'data' => [
            'env' => 'APP_NAME=Laravel\nAPP_ENV=production',
        ],
    ], status: 200)]
    public function showEnv(Project $project, Server $server, Site $site): JsonResponse
    {
        $this->authorize('view', [$site, $server]);

        $this->validateRoute($project, $server, $site);

        return response()->json([
            'data' => [
                'env' => $site->getEnv(),
            ],
        ]);
    }

    #[Put('{site}/env', name: 'api.projects.servers.sites.env', middleware: 'ability:write')]
    #[Endpoint(title: 'env', description: 'Update site .env file')]
    #[BodyParam(name: 'env', type: 'string', description: 'Content of the .env file')]
    #[Response(status: 200)]
    public function updateEnv(Request $request, Project $project, Server $server, Site $site): SiteResource
    {
        $this->authorize('update', [$site, $server]);

        $this->validateRoute($project, $server, $site);

        $this->validate($request, [
            'env' => ['required', 'string'],
        ]);

        app(UpdateEnv::class)->update($site, $request->all());

        return new SiteResource($site);
    }

    private function validateRoute(Project $project, Server $server, ?Site $site = null): void
    {
        if ($project->id !== $server->project_id) {
            abort(404, 'Server not found in project');
        }

        if ($site && $site->server_id !== $server->id) {
            abort(404, 'Site not found in server');
        }
    }
}
