<div class="filament-taskboard" wire:id="{{ $this->getId() }}">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <style>
        .task-card .edit-btn {
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .task-card:hover .edit-btn {
            visibility: visible;
            opacity: 1;
        }
    </style>

    <div class="flex gap-4 overflow-x-auto pb-4 h-full">
        @foreach($this->statuses as $status)
            <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-4 shadow h-full flex flex-col flex-shrink-0"
                 style="width: 320px; min-width: 320px;"
                 wire:key="status-wrapper-{{ $status->id }}">
                <h3 class="text-lg font-semibold mb-4">{{ $status->name }}</h3>

                {{-- Added visual border and flex-grow to ensure drop target is large --}}
                <div class="space-y-4 task-column flex-grow min-h-[100px]"
                     id="status-col-{{ $status->id }}"
                     data-status-id="{{ $status->id }}"
                     style="border: 2px dashed transparent;">
                    @foreach($status->tasks as $task)
                        <div @class([
                            'rounded-lg p-3 shadow-sm task-card cursor-move group',
                            'bg-red-200 dark:bg-red-700' => $task->isOverdue(),
                            'bg-yellow-200 dark:bg-yellow-700' => $task->isNearlyDue() && !$task->isOverdue(),
                            'bg-white dark:bg-gray-700' => !$task->isOverdue() && !$task->isNearlyDue(),
                        ])
                             wire:key="task-{{ $task->id }}"
                             data-task-id="{{ $task->id }}">

                            <div class="flex justify-between items-start">
                                <div class="font-medium">{{ $task->title }}</div>
                                <button wire:click="mountAction('editTask', { id: {{ $task->id }} })"
                                        class="edit-btn text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1"
                                        onclick="event.stopPropagation()">
                                    <x-heroicon-m-pencil-square class="w-4 h-4" />
                                </button>
                            </div>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ Str::limit($task->description, 150) }}</p>

                            <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                @if($task->priority)
                                    <span class="px-2 py-1 rounded"
                                          style="background-color: {{ $task->priority->color ?? '#e5e7eb' }}; color: {{ $task->priority->color ? 'white' : 'black' }};">
                                        {{ $task->priority->name }}
                                    </span>
                                @endif
                                @if($task->complexity)

                                    <span class="px-2 py-1 rounded"
                                          style="background-color: {{ $task->complexity->color ?? 'white' }}; color: {{ $task->complexity->color ? 'white' : 'black' }}; border: 1px solid #d1d5db;">
                                        {{ $task->complexity->name }}
                                    </span>
                                @endif
                                @if($task->assigned_to)
                                    <span class="px-2 py-1 rounded bg-blue-200 dark:bg-blue-700 text-blue-800 dark:text-blue-200">
                                        {{ $task->assignedTo->full_name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <x-filament-actions::modals />

    <script>
        function initTaskboard() {
            if (typeof Sortable === 'undefined') return;

            document.querySelectorAll('.task-column').forEach(el => {
                // Destroy existing to be safe
                if (el._sortable) {
                    el._sortable.destroy();
                }

                el._sortable = new Sortable(el, {
                    group: 'shared-taskboard',
                    animation: 150,
                    draggable: '.task-card',
                    delay: 0, // Instant drag
                    onEnd: function (evt) {
                        const taskId = evt.item.dataset.taskId;
                        const newStatusId = evt.to.dataset.statusId;
                        const oldStatusId = evt.from.dataset.statusId;

                        console.log('Moved:', taskId, 'From:', oldStatusId, 'To:', newStatusId);

                        if (newStatusId && taskId && newStatusId !== oldStatusId) {
                            @this.call('updateTaskStatus', taskId, newStatusId);
                        }
                    }
                });
            });
        }

        initTaskboard();

        document.addEventListener('livewire:navigated', initTaskboard);
        document.addEventListener('livewire:initialized', () => {
            Livewire.hook('morph.updated', () => {
                initTaskboard();
            });
        });
    </script>
</div>
