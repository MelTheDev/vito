<?php

namespace App\Http\Controllers\API;

use App\Actions\Site\Deploy;
use App\Exceptions\SourceControlIsNotConnected;
use App\Facades\Notifier;
use App\Http\Controllers\Controller;
use App\Models\GitHook;
use App\Models\ServerLog;
use App\Models\SourceControl;
use App\Notifications\SourceControlDisconnected;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\RouteAttributes\Attributes\Any;
use Throwable;

class GitHookController extends Controller
{
    #[Any('api/git-hooks', name: 'api.git-hooks')]
    public function __invoke(Request $request): JsonResponse
    {
        if (! $request->input('secret')) {
            abort(404);
        }

        /** @var GitHook $gitHook */
        $gitHook = GitHook::query()
            ->where('secret', $request->input('secret'))
            ->firstOrFail();

        foreach ($gitHook->actions as $action) {
            /** @var SourceControl $sourceControl */
            $sourceControl = $gitHook->site->sourceControl;
            $webhookBranch = $sourceControl->provider()->getWebhookBranch($request->array());
            if ($action == 'deploy' && $gitHook->site->branch === $webhookBranch) {
                try {
                    app(Deploy::class)->run($gitHook->site);
                } catch (SourceControlIsNotConnected) {
                    Notifier::send($gitHook->sourceControl, new SourceControlDisconnected($gitHook->sourceControl));
                } catch (Throwable $e) {
                    ServerLog::log(
                        $gitHook->site->server,
                        'deploy-failed',
                        $e->getMessage(),
                        $gitHook->site
                    );
                    Log::error('git-hook-exception', (array) $e);
                }
            }
        }

        return response()->json([
            'success' => true,
        ]);
    }
}
