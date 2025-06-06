<?php

namespace App\Web\Pages\Servers\Databases\Widgets;

use App\Actions\Database\DeleteDatabaseUser;
use App\Actions\Database\LinkUser;
use App\Models\DatabaseUser;
use App\Models\Server;
use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as Widget;
use Illuminate\Database\Eloquent\Builder;

class DatabaseUsersList extends Widget
{
    public Server $server;

    /**
     * @var array<string>
     */
    protected $listeners = ['$refresh'];

    /**
     * @return Builder<DatabaseUser>
     */
    protected function getTableQuery(): Builder
    {
        return DatabaseUser::query()->where('server_id', $this->server->id);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('username')
                ->searchable(),
            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn (DatabaseUser $databaseUser) => DatabaseUser::$statusColors[$databaseUser->status])
                ->sortable(),
            TextColumn::make('created_at')
                ->label('Created At')
                ->formatStateUsing(fn ($record) => $record->created_at_by_timezone)
                ->sortable(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->actions([
                $this->passwordAction(),
                $this->linkAction(),
                $this->deleteAction(),
            ]);
    }

    private function passwordAction(): Action
    {
        /** @var User $user */
        $user = auth()->user();

        return Action::make('password')
            ->hiddenLabel()
            ->icon('heroicon-o-key')
            ->color('gray')
            ->modalHeading('Database user\'s password')
            ->modalWidth(MaxWidth::Large)
            ->tooltip('Show the password')
            ->authorize(fn ($record) => $user->can('view', $record))
            ->form([
                TextInput::make('password')
                    ->label('Password')
                    ->default(fn (DatabaseUser $record) => $record->password)
                    ->disabled(),
            ])
            ->action(function (DatabaseUser $record, array $data): void {
                //
            })
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close');
    }

    private function linkAction(): Action
    {
        /** @var User $user */
        $user = auth()->user();

        return Action::make('link')
            ->hiddenLabel()
            ->icon('heroicon-o-link')
            ->modalHeading('Link user to databases')
            ->modalWidth(MaxWidth::Large)
            ->tooltip('Link user')
            ->modalSubmitActionLabel('Save')
            ->authorize(fn ($record) => $user->can('update', $record))
            ->form([
                CheckboxList::make('databases')
                    ->label('Databases')
                    ->options($this->server->databases()->pluck('name', 'name')->toArray())
                    ->rules(fn (callable $get): array => LinkUser::rules($this->server, $get()))
                    ->default(fn (DatabaseUser $record) => $record->databases),
            ])
            ->action(function (DatabaseUser $record, array $data): void {
                run_action($this, function () use ($record, $data): void {
                    app(LinkUser::class)->link($record, $data);

                    Notification::make()
                        ->success()
                        ->title('User linked to databases!')
                        ->send();
                });
            });
    }

    private function deleteAction(): Action
    {
        /** @var User $user */
        $user = auth()->user();

        return Action::make('delete')
            ->hiddenLabel()
            ->icon('heroicon-o-trash')
            ->modalHeading('Delete Database User')
            ->color('danger')
            ->tooltip('Delete')
            ->authorize(fn ($record) => $user->can('delete', $record))
            ->requiresConfirmation()
            ->action(function (DatabaseUser $record): void {
                run_action($this, function () use ($record): void {
                    app(DeleteDatabaseUser::class)->delete($this->server, $record);
                    $this->dispatch('$refresh');
                });
            });
    }
}
