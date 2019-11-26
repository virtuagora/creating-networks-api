<?php

namespace App\Model;

class Region extends ResourceModel
{
    protected $table = 'regions';
    protected $visible = [
        'id', 'name', 'data', 'space', 'localization',
    ];
    protected $fillable = [
        'id', 'name', 'data', 'localization', 'space_id',
    ];
    protected $with = [
        'space',
    ];
    protected $casts = [
        'data' => 'array',
        'localization' => 'array',
    ];

    public function Space()
    {
        return $this->belongsTo('App\Model\Space');
    }

    public function countries()
    {
        return $this->hasMany('App\Model\Country');
    }
}
