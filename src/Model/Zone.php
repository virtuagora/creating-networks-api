<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Builder;

class Zone extends Node
{
    protected $visible = [
        'id', 'title', 'text', 'points',
        'created_at', 'point',
    ];
    protected $attributes = [
        'type' => 'Zone',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('zone', function (Builder $builder) {
            $builder->where('type', 'Zone');
        });
    }
}
