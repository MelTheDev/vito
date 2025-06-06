<?php

namespace App\Web\Pages\Settings\Projects\Widgets;

use App\Models\Project;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as Widget;
use Illuminate\Database\Eloquent\Builder;

class ProjectUsersList extends Widget
{
    /**
     * @var array<string, string>
     */
    protected $listeners = ['userAdded' => '$refresh'];

    public Project $project;

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    /**
     * @return Builder<User>
     */
    protected function getTableQuery(): Builder
    {
        return User::query()->whereHas('projects', function (Builder $query): void {
            $query->where('project_id', $this->project->id);
        });
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('id')->width('20%'),
            Tables\Columns\TextColumn::make('name')->width('20%'),
            Tables\Columns\TextColumn::make('email')->width('20%'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label('Remove')
                    ->modalHeading('Remove user from project')
                    ->visible(fn ($record): bool => $this->authorize('update', $this->project)->allowed() && $record->id !== auth()->id())
                    ->using(function ($record): void {
                        $this->project->users()->detach($record);
                    }),
            ])
            ->paginated(false);
    }
}
