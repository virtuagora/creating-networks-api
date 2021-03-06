<?php

namespace App\Model;

class City extends ResourceModel
{
    protected $table = 'cities';
    protected $visible = [
        'id', 'name', 'data', 'country', 'space', 'localization',
        'initiatives_count', 'initiatives',
    ];
    protected $fillable = [
        'name', 'data', 'country_id', 'space_id', 'localization',
    ];
    protected $with = [
        'space',
    ];
    protected $casts = [
        'data' => 'array',
        'localization' => 'array',
    ];

    public function country()
    {
        return $this->belongsTo('App\Model\Country');
    }

    public function space()
    {
        return $this->belongsTo('App\Model\Space');
    }

    public function initiatives()
    {
        return $this->hasMany('App\Model\Initiative');
    }

    public function registered_city()
    {
        return $this->hasOne('App\Model\RegisteredCity');
    }
}
