<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Builder;

class Place extends Node
{
    protected $visible = [
        'id', 'title', 'content', 'points', 'author', 'meta',
        'created_at', 'ballot', 'space', 'supporter'
    ];
    protected $fillable = [
        'title', 'content',
    ];
    protected $with = [
        'space',
    ];
    protected $attributes = [
        'type' => 'Place',
        'unlisted' => false,
    ];

    public function relations()
    {
        return $this->belongsToMany('App\Model\Subject', 'node_subject', 'node_id')->withPivot(['relation', 'value']);
    }

    public function space()
    {
        return $this->belongsTo('App\Model\Space');
    }

    public function ballot()
    {
        return $this->hasOne('App\Model\Ballot', 'node_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('place', function (Builder $builder) {
            $builder->where('type', 'Place');
        });
    }
}
