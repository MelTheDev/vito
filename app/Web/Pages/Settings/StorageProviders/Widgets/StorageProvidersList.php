<?php

namespace App\Web\Pages\Settings\StorageProviders\Widgets;

use App\Actions\StorageProvider\DeleteStorageProvider;
use App\Models\StorageProvider;
use App\Models\User;
use App\Web\Pages\Settings\StorageProviders\Actions\Edit;
use Exception;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as Widget;
use Illuminate\Database\Eloquent\Builder;

class StorageProvidersList extends Widget
{
    /**
     * @var array<string>
     */
    protected $listeners = ['$refresh'];

    /**
     * @return Builder<StorageProvider>
     */
    protected function getTableQuery(): Builder
    {
        /** @var User $user */
        $user = auth()->user();

        return StorageProvider::getByProjectId($user->current_project_id);
    }

    protected function getTableColumns(): array
    {
        return [
            IconColumn::make('provider')
                ->icon(fn (StorageProvider $record): string => 'icon-'.$record->provider)
                ->tooltip(fn (StorageProvider $record) => $record->provider)
                ->width(24),
            TextColumn::make('name')
                ->default(fn (StorageProvider $record) => $record->profile)
                ->label('Name')
                ->searchable()
                ->sortable(),
            TextColumn::make('id')
                ->label('Global')
                ->badge()
                ->color(fn ($record): string => $record->project_id ? 'gray' : 'success')
                ->formatStateUsing(fn (StorageProvider $record): string => $record->project_id ? 'No' : 'Yes'),
            TextColumn::make('created_at')
                ->label('Created At')
                ->formatStateUsing(fn ($record) => $record->created_at_by_timezone)
                ->searchable()
                ->sortable(),
        ];
    }

    public function table(Table $table): Table
    {
        /** @var User $user */
        $user = auth()->user();

        return $table
            ->heading(null)
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->actions([
                EditAction::make('edit')
                    ->label('Edit')
                    ->modalHeading('Edit Storage Provider')
                    ->mutateRecordDataUsing(fn (array $data, StorageProvider $record): array => [
                        'name' => $record->profile,
                        'global' => $record->project_id === null,
                    ])
                    ->form(Edit::form())
                    ->authorize(fn (StorageProvider $record) => $user->can('update', $record))
                    ->using(fn (array $data, StorageProvider $record) => Edit::action($record, $data))
                    ->modalWidth(MaxWidth::Medium),
                DeleteAction::make('delete')
                    ->label('Delete')
                    ->modalHeading('Delete Storage Provider')
                    ->authorize(fn (StorageProvider $record) => $user->can('delete', $record))
                    ->using(function (array $data, StorageProvider $record): void {
                        try {
                            app(DeleteStorageProvider::class)->delete($record);
                        } catch (Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title($e->getMessage())
                                ->send();
                        }
                    }),
            ]);
    }
}
