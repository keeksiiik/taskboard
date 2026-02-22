<?php

namespace dillarionov\Taskboard\Http\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use dillarionov\Taskboard\Models\Status;
use dillarionov\Taskboard\Models\Task;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;
use Filament\Forms;
use Carbon\Carbon;
use App\Models\User\User;
use dillarionov\Taskboard\Models\Priority;
use dillarionov\Taskboard\Models\Complexity;

class Taskboard extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    #[On('task-created')]
    public function refresh() {}

    #[Computed]
    public function statuses()
    {
        return Status::with(['tasks' => function ($query) {
            $query->with(['priority', 'complexity', 'assignedTo', 'createdBy'])
                  ->join('taskboard_priorities', 'taskboard_tasks.priority_id', '=', 'taskboard_priorities.id')
                  ->join('taskboard_complexities', 'taskboard_tasks.complexity_id', '=', 'taskboard_complexities.id')
                  ->select('taskboard_tasks.*')
                  ->orderByDesc('taskboard_priorities.sort_order')
                  ->orderBy('taskboard_complexities.sort_order');
        }])
        ->orderBy('sort_order')
        ->get();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('taskboard::livewire.taskboard');
    }

    public function updateTaskStatus($taskId, $newStatusId)
    {
        $task = Task::find($taskId);
        if ($task) {
            $task->status_id = $newStatusId;
            $task->save();
        }
    }

    protected function getTaskFormSchema(): array
    {
        return [
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
                        ->relationship('status', 'name')
                        ->required()
                        ->default(fn () => Status::orderBy('sort_order')->first()?->id),
                    Forms\Components\Select::make('priority_id')
                        ->label(__('taskboard::taskboard.fields.priority'))
                        ->relationship('priority', 'name')
                        ->required()
                        ->default(fn () => Priority::orderBy('sort_order')->first()?->id),
                    Forms\Components\Select::make('complexity_id')
                        ->label(__('taskboard::taskboard.fields.complexity'))
                        ->relationship('complexity', 'name')
                        ->required()
                        ->default(fn () => Complexity::orderBy('sort_order')->first()?->id),
                    Forms\Components\Select::make('assigned_to')
                        ->label(__('taskboard::taskboard.fields.assigned_to'))
                        ->relationship('assignedTo', 'name')
                        ->searchable(),
                    Forms\Components\DateTimePicker::make('started_at')
                        ->label(__('taskboard::taskboard.fields.started_at')),
                ]),
            Forms\Components\DatePicker::make('due_date')
                ->label(__('taskboard::taskboard.fields.due_date')),
        ];
    }

    public function createTaskAction(): Action
    {
        return Action::make('createTask')
            ->label(__('taskboard::taskboard.actions.create_task'))
            ->form($this->getTaskFormSchema())
            ->action(function (array $data): void {
                $data['created_by'] = auth()->id();
                Task::create($data);
            })
            ->modalWidth('lg');
    }

    public function deleteTaskAction(): Action
    {
        return Action::make('deleteTask')
            ->label(__('taskboard::taskboard.actions.delete_task'))
            ->requiresConfirmation()
            ->color('danger')
            ->action(function (array $arguments) {
                $task = Task::find($arguments['id']);
                if ($task) {
                    $task->delete();
                }
            });
    }

    public function editTaskAction(): Action
    {
        return Action::make('editTask')
            ->label(__('taskboard::taskboard.actions.edit_task'))
            ->model(Task::class)
            ->record(fn (array $arguments) => Task::find($arguments['id']))
            ->fillForm(fn (Task $record) => $record->toArray())
            ->form(array_merge($this->getTaskFormSchema(), [
                Forms\Components\Placeholder::make('created_by')
                    ->label(__('taskboard::taskboard.fields.created_by'))
                    ->content(fn (Task $record) => $record->createdBy->name ?? '-'),
            ]))
            ->extraModalFooterActions(fn (Task $record): array => [
                Action::make('delete')
                    ->label(__('taskboard::taskboard.actions.delete_task'))
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function () use ($record) {
                        $record->delete();
                    })
                    ->cancelParentActions(),
            ])
            ->action(function (Task $record, array $data): void {
                $record->update($data);
            })
            ->modalWidth('lg');
    }
}
