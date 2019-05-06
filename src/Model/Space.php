<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;

class Space extends Model
{
    use SpatialTrait;

    protected $table = 'spaces';
    protected $visible = [
        'id', 'point', 'polygon', 'line',
    ];
    protected $spatialFields = [
        'point', 'polygon', 'line',
    ];
}
