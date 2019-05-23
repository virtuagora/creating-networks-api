<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;

class RegisteredCity extends Model
{
    use SpatialTrait;

    public $timestamps = false;
    protected $table = 'registered_cities';
    protected $visible = [
        'id', 'name', 'point', 'localization', 'trace', 'country',
    ];
    protected $fillable = [
        'name', 'trace', 'point', 'localization', 'country_id',
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

    public function City()
    {
        return $this->belongsTo('App\Model\City');
    }
}
