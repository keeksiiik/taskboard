<?php

namespace dillarionov\Taskboard\Models;

use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'taskboard_tasks';

    protected $fillable = [
        'title',
        'description',
        'status_id',
        'priority_id',
        'complexity_id',
        'assigned_to',
        'created_by',
        'due_date',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->completed_at;
    }

    public function isNearlyDue(): bool
    {
        return $this->due_date && $this->due_date->isFuture() && $this->due_date->diffInHours(Carbon::now()) <= 24 && !$this->completed_at;
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function complexity(): BelongsTo
    {
        return $this->belongsTo(Complexity::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
