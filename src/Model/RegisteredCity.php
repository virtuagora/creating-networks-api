<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class RegisteredCity extends Model
{
    protected $table = 'registered_cities';
    protected $visible = [
        'id', 'name', 'point', 'localization', 'trace',
    ];
    protected $fillable = [
        'name', 'point', 'localization', 'country_id',
    ];
    protected $casts = [
        'localization' => 'array',
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
