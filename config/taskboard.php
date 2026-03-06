<?php

return [
    /*
     |--------------------------------------------------------------------------
     | User Model
     |--------------------------------------------------------------------------
     |
     | Define the User model class that the taskboard should interact with.
     | By default, it will use the App\Models\User\User class or the default Laravel User model if missing.
     |
     */

    'user_model' => class_exists(\App\Models\User\User::class)
    ?\App\Models\User\User::class
    : \App\Models\User::class ,

    /*
     |--------------------------------------------------------------------------
     | Assignee Filters
     |--------------------------------------------------------------------------
     |
     | Configure how the users are filtered when selecting an assignee for a task.
     |
     | 'assignee_conditions': Array of simple where conditions to apply to the User model query.
     |                        Example: ['is_active' => true, 'role' => 'admin']
     |
     | 'assignee_scope':      Name of a scope method defined on your User model.
     |                        Example: 'assignableToTasks' (for scopeAssignableToTasks method).
     |
     */

    'assignee_conditions' => [],

    'assignee_scope' => null,

    'title' => __('taskboard::taskboard.title'),

    'navigation_group' => 'Администратор',

    'navigation_sort' => 13,
];
