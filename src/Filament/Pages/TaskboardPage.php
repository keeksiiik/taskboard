<?php

namespace dillarionov\Taskboard\Filament\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use dillarionov\Taskboard\Models\Complexity;
use dillarionov\Taskboard\Models\Priority;
use dillarionov\Taskboard\Models\Status;
use dillarionov\Taskboard\Models\Task;
use App\Models\User\User;

class TaskboardPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'taskboard::filament.pages.taskboard-page';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('taskboard::taskboard.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('taskboard::taskboard.navigation_group');
    }

    public function getTitle(): \Illuminate\Contracts\Support\Htmlable|string
    {
        return __('taskboard::taskboard.title');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('taskboard::taskboard.actions.create_task'))
                ->model(Task::class)
                ->form([
                    Forms\Components\TextInput::make('title')
                        ->label(__('taskboard::taskboard.fields.task_title'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->label(__('taskboard::taskboard.fields.description'))
                        ->rows(3),
                    Forms\Components\Grid::make()
                        ->schema([
                            Forms\Components\Select::make('status_id')
                                ->label(__('taskboard::taskboard.fields.status'))
                                ->options(Status::orderBy('sort_order')->pluck('name', 'id'))
                                ->required()
                                ->default(fn () => Status::orderBy('sort_order')->first()?->id),
                            Forms\Components\Select::make('priority_id')
                                ->label(__('taskboard::taskboard.fields.priority'))
                                ->options(Priority::orderBy('sort_order')->pluck('name', 'id'))
                                ->required()
                                ->default(fn () => Priority::orderBy('sort_order')->first()?->id),
                            Forms\Components\Select::make('complexity_id')
                                ->label(__('taskboard::taskboard.fields.complexity'))
                                ->options(Complexity::orderBy('sort_order')->pluck('name', 'id'))
                                ->required()
                                ->default(fn () => Complexity::orderBy('sort_order')->first()?->id),
                            Forms\Components\Select::make('assigned_to')
                                ->label(__('taskboard::taskboard.fields.assigned_to'))
                                ->options(User::pluck('name', 'id'))
                                ->searchable(),
                            Forms\Components\DateTimePicker::make('started_at')
                                ->label(__('taskboard::taskboard.fields.started_at')),
                        ]),
                    Forms\Components\DatePicker::make('due_date')
                        ->label(__('taskboard::taskboard.fields.due_date')),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    $data['created_by'] = auth()->id();
                    return $data;
                })
                ->after(function () {
                    // Force refresh of the Livewire component if needed,
                    // or rely on page reload. Filament CreateAction stays on page by default unless configured.
                    // If we want to refresh the taskboard, we might need an event.
                    // But for now, let's keep it simple.
                    $this->dispatch('task-created');
                }),
            Action::make('settings')
                ->label(__('taskboard::taskboard.settings'))
                ->icon('heroicon-o-cog-6-tooth')
                ->fillForm(fn (): array => [
                    'statuses' => Status::orderBy('sort_order')->get()->toArray(),
                    'priorities' => Priority::orderBy('sort_order')->get()->toArray(),
                    'complexities' => Complexity::orderBy('sort_order')->get()->toArray(),
                ])
                ->form([
                    Forms\Components\Tabs::make('Settings')
                        ->tabs([
                            Forms\Components\Tabs\Tab::make('Statuses')
                                ->label(__('taskboard::taskboard.tabs.statuses'))
                                ->schema([
                                    Forms\Components\Repeater::make('statuses')
                                        ->label(__('taskboard::taskboard.tabs.statuses'))
                                        ->collapsed(true)
                                        ->schema([
                                            Forms\Components\Hidden::make('id'),
                                            Forms\Components\TextInput::make('name')
                                                ->label(__('taskboard::taskboard.fields.name'))
                                                ->required()
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),
                                            Forms\Components\TextInput::make('slug')
                                                ->label(__('taskboard::taskboard.fields.slug'))
                                                ->required(),
                                            Forms\Components\ColorPicker::make('color')
                                                ->label(__('taskboard::taskboard.fields.color')),
                                            Forms\Components\TextInput::make('sort_order')
                                                ->label(__('taskboard::taskboard.fields.sort_order'))
                                                ->numeric()
                                                ->default(0),
                                        ])
                                        ->orderColumn('sort_order')
                                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                        ->collapsible()
                                        ->reorderableWithButtons(),
                                ]),
                            Forms\Components\Tabs\Tab::make('Priorities')
                                ->label(__('taskboard::taskboard.tabs.priorities'))
                                ->schema([
                                    Forms\Components\Repeater::make('priorities')
                                        ->label(__('taskboard::taskboard.tabs.priorities'))
                                        ->collapsed(true)
                                        ->schema([
                                            Forms\Components\Hidden::make('id'),
                                            Forms\Components\TextInput::make('name')
                                                ->label(__('taskboard::taskboard.fields.name'))
                                                ->required()
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),
                                            Forms\Components\TextInput::make('slug')
                                                ->label(__('taskboard::taskboard.fields.slug'))
                                                ->required(),
                                            Forms\Components\ColorPicker::make('color')
                                                ->label(__('taskboard::taskboard.fields.color')),
                                            Forms\Components\TextInput::make('sort_order')
                                                ->label(__('taskboard::taskboard.fields.sort_order'))
                                                ->numeric()
                                                ->default(0),
                                        ])
                                        ->orderColumn('sort_order')
                                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                        ->collapsible()
                                        ->reorderableWithButtons(),
                                ]),
                            Forms\Components\Tabs\Tab::make('Complexities')
                                ->label(__('taskboard::taskboard.tabs.complexities'))
                                ->schema([
                                    Forms\Components\Repeater::make('complexities')
                                        ->label(__('taskboard::taskboard.tabs.complexities'))
                                        ->collapsed(true)
                                        ->schema([
                                            Forms\Components\Hidden::make('id'),
                                            Forms\Components\TextInput::make('name')
                                                ->label(__('taskboard::taskboard.fields.name'))
                                                ->required()
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),
                                            Forms\Components\TextInput::make('slug')
                                                ->label(__('taskboard::taskboard.fields.slug'))
                                                ->required(),
                                            Forms\Components\ColorPicker::make('color')
                                                ->label(__('taskboard::taskboard.fields.color')),
                                            Forms\Components\TextInput::make('sort_order')
                                                ->label(__('taskboard::taskboard.fields.sort_order'))
                                                ->numeric()
                                                ->default(0),
                                        ])
                                        ->orderColumn('sort_order')
                                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                        ->collapsible()
                                        ->reorderableWithButtons(),
                                ]),
                        ]),
                ])
                ->action(function (array $data, TaskboardPage $livewire): void {
                    $livewire->syncModels(Status::class, $data['statuses']);
                    $livewire->syncModels(Priority::class, $data['priorities']);
                    $livewire->syncModels(Complexity::class, $data['complexities']);

                    Notification::make()
                        ->title(__('taskboard::taskboard.settings_saved'))
                        ->success()
                        ->send();

                    $livewire->redirect(static::getUrl());
                }),
        ];
    }

    public function syncModels(string $modelClass, array $items): void
    {
        $existingIds = $modelClass::pluck('id')->toArray();
        $submittedIds = Arr::pluck($items, 'id');
        $submittedIds = array_filter($submittedIds);

        $idsToDelete = array_diff($existingIds, $submittedIds);
        if (! empty($idsToDelete)) {
            $modelClass::destroy($idsToDelete);
        }

        foreach (array_values($items) as $index => $item) {
            $id = $item['id'] ?? null;
            $data = Arr::except($item, ['id', 'created_at', 'updated_at']);

            $data['sort_order'] = $index + 1;

            $modelClass::updateOrCreate(['id' => $id], $data);
        }
    }
}
