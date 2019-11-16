<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';
    protected $visible = [
        'id', 'name', 'code_2', 'code_3', 'data', 'region', 'space',
        'localization', 'initiatives_count',
    ];
    protected $fillable = [
        'id', 'name', 'code_2', 'code_3', 'region_id', 'space_id', 'data',
        'localization',
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
        return $this->belongsTo('App\Model\Region');
    }

    public function Space()
    {
        return $this->belongsTo('App\Model\Space');
    }

    public function cities()
    {
        return $this->hasMany('App\Model\City');
    }

    public function initiatives()
    {
        return $this->belongsToMany(
            'App\Model\Group', 'group_country', 'country_id', 'group_id'
        );
    }
}
