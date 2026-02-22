<?php

namespace dillarionov\Taskboard\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complexity extends Model
{
    protected $table = 'taskboard_complexities';

    protected $fillable = [
        'name',
        'slug',
        'color',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
