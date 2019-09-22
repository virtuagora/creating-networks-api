<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Builder;

class Video extends Node
{
    protected $attributes = [
        'node_type_id' => 'Video',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('video', function (Builder $b) {
            $b->where('node_type_id', 'Video');
        });
    }
}
