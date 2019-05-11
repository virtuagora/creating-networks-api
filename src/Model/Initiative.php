<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Builder;

class Initiative extends Group
{
    protected $attributes = [
        'group_type_id' => 'Initiative',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('initiative', function (Builder $b) {
            $b->where('group_type_id', 'Initiative');
        });
    }
}
