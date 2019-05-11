<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';
    protected $visible = [
        'id', 'name', 'code', 'data', 'region', 'space', 'localization',
    ];
    protected $fillable = [
        'id', 'name', 'data', 'code', 'region_id', 'localization',
    ];
    protected $with = [
        'space',
    ];
    protected $casts = [
        'data' => 'array',
        'localization' => 'array',
    ];

    public function region()
    {
        return $this->belongsTo('App\Model\Country');
    }

    public function Space()
    {
        return $this->belongsTo('App\Model\Space');
    }

    public function cities()
    {
        return $this->hasMany('App\Model\City');
    }
}
