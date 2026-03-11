<?php

namespace dillarionov\Taskboard\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    use HasUuids;
    protected $table = 'taskboard_statuses';

    protected $fillable = [
        'name',
        'slug',
        'color',
        'sort_order',
        'show_in_navigation_badge',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'show_in_navigation_badge' => 'boolean',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}