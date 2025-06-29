<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Prefix('servers/{server}/console')]
#[Middleware(['auth', 'has-project'])]
class ConsoleController extends Controller
{
    #[Get('/', name: 'console')]
    public function index(Server $server): Response
    {
        $this->authorize('update', $server);

        return Inertia::render('console/index');
    }

    #[Post('/run', name: 'console.run')]
    public function run(Server $server, Request $request): StreamedResponse
    {
        $this->authorize('update', $server);

        $this->validate($request, [
            'user' => [
                'required',
                Rule::in($server->getSshUsers()),
            ],
            'command' => 'required|string',
        ]);

        $ssh = $server->ssh($request->user);
        $log = 'console-'.time();

        $user = $request->input('user');
        $currentDir = $user == 'root' ? '/root' : '/home/'.$user;
        if (Cache::has('console.'.$server->id.'.dir')) {
            $currentDir = Cache::get('console.'.$server->id.'.dir');
        }

        return response()->stream(
            function () use ($server, $request, $ssh, $log, $currentDir): void {
                $command = 'cd '.$currentDir.' && '.$request->command.' && echo -n "VITO_WORKING_DIR: " && pwd';
                $output = '';
                $ssh->exec(command: $command, log: $log, stream: true, streamCallback: function (string $out) use (&$output): void {
                    echo preg_replace('/^VITO_WORKING_DIR:.*(\r?\n)?/m', '', $out);
                    $output .= $out;
                    ob_flush();
                    flush();
                });
                // extract the working dir and put it in the session
                if (preg_match('/VITO_WORKING_DIR: (.*)/', $output, $matches)) {
                    Cache::put('console.'.$server->id.'.dir', $matches[1]);
                }
            },
            200,
            [
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
                'Content-Type' => 'text/event-stream',
            ]
        );
    }

    #[Get('/working-dir', name: 'console.working-dir')]
    public function workingDir(Server $server): JsonResponse
    {
        return response()->json([
            'dir' => Cache::get('console.'.$server->id.'.dir', '~'),
        ]);
    }
}
