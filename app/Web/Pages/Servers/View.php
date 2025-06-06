<?php

namespace App\Web\Pages\Servers;

use App\Models\ServerLog;
use App\Models\User;
use App\Web\Pages\Servers\Logs\Widgets\LogsList;
use App\Web\Pages\Servers\Widgets\Installing;
use App\Web\Pages\Servers\Widgets\ServerStats;
use Livewire\Attributes\On;

class View extends Page
{
    protected static ?string $slug = 'servers/{server}';

    protected static ?string $title = 'Overview';

    public string $previousStatus;

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();

        $this->authorize('view', [$this->server, $user->currentProject]);
        $this->previousStatus = $this->server->status;
    }

    #[On('$refresh')]
    public function refresh(): void
    {
        $currentStatus = $this->server->refresh()->status;

        if ($this->previousStatus !== $currentStatus) {
            $this->redirect(static::getUrl(parameters: ['server' => $this->server]));
        }

        $this->previousStatus = $currentStatus;
    }

    public function getWidgets(): array
    {
        /** @var User $user */
        $user = auth()->user();

        $widgets = [];

        if ($this->server->isInstalling()) {
            $widgets[] = [Installing::class, ['server' => $this->server]];
        } else {
            $widgets[] = [ServerStats::class, ['server' => $this->server]];
        }

        if ($user->can('viewAny', [ServerLog::class, $this->server])) {
            $widgets[] = [
                LogsList::class, [
                    'server' => $this->server,
                    'label' => 'Logs',
                ],
            ];
        }

        return $widgets;
    }
}
