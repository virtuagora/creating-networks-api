<?php

namespace App\Model;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;

class RegisteredCity extends ResourceModel
{
    use SpatialTrait;

    public $timestamps = false;
    protected $table = 'registered_cities';
    protected $visible = [
        'id', 'name', 'point', 'localization', 'trace', 'country',
    ];
    protected $fillable = [
        'name', 'trace', 'localization', 'country_id',
    ];
    protected $with = ['country'];
    protected $casts = [
        'localization' => 'array',
    ];
    protected $spatialFields = [
        'point',
    ];

    public function country()
    {
        return $this->belongsTo('App\Model\Country');
    }

    public function city()
    {
        return $this->belongsTo('App\Model\City');
    }
}
