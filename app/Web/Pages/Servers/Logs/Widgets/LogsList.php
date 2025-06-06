<?php

namespace App\Web\Pages\Servers\Logs\Widgets;

use App\Models\Server;
use App\Models\ServerLog;
use App\Models\Site;
use App\Models\User;
use Exception;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\ComponentAttributeBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogsList extends Widget
{
    public Server $server;

    public ?Site $site = null;

    public bool $remote = false;

    /**
     * @var array<string>
     */
    protected $listeners = ['$refresh'];

    /**
     * @return Builder<ServerLog>
     */
    protected function getTableQuery(): Builder
    {
        return ServerLog::query()
            ->where('server_id', $this->server->id)
            ->where(function (Builder $query): void {
                if ($this->site instanceof Site) {
                    $query->where('site_id', $this->site->id);
                }
            })
            ->where('is_remote', $this->remote);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Event')
                ->searchable()
                ->sortable(),
            TextColumn::make('created_at')
                ->label('Created At')
                ->formatStateUsing(fn ($record) => $record->created_at_by_timezone)
                ->sortable(),
        ];
    }

    /**
     * @param  Builder<ServerLog>  $query
     * @return Builder<ServerLog>
     */
    protected function applyDefaultSortingToTableQuery(Builder $query): Builder
    {
        return $query->latest('created_at');
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        /** @var User $user */
        $user = auth()->user();

        return $table
            ->heading(null)
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        )),
            ])
            ->actions([
                Action::make('view')
                    ->hiddenLabel()
                    ->tooltip('View')
                    ->icon('heroicon-o-eye')
                    ->authorize(fn ($record) => $user->can('view', $record))
                    ->modalHeading('View Log')
                    ->modalContent(fn (ServerLog $record) => view('components.console-view', [
                        'slot' => $record->getContent(),
                        'attributes' => new ComponentAttributeBag,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Action::make('download')
                    ->hiddenLabel()
                    ->tooltip('Download')
                    ->color('gray')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->authorize(fn ($record) => $user->can('view', $record))
                    ->action(fn (ServerLog $record): StreamedResponse => $record->download()),
                DeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->authorize(fn ($record) => $user->can('delete', $record)),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->requiresConfirmation()
                    ->authorize($user->can('deleteMany', [ServerLog::class, $this->server])),
            ]);
    }
}
