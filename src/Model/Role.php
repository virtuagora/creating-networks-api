<?php

namespace App\Model;

class Role extends ResourceModel
{
    public $incrementing = false;
    protected $table = 'roles';
    protected $visible = [
        'id', 'name', 'show_badge', 'icon', 'data',
    ];
    protected $casts = [
        'data' => 'array',
        'show_badge' => 'boolean',
    ];
}
