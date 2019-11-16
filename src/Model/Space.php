<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Grimzy\LaravelMysqlSpatial\Types\Geometry;
use Illuminate\Support\Str;
use App\Util\GeoUtils;
use ReflectionClass;
use Exception;

class Space extends Model
{
    use SpatialTrait;

    protected $table = 'spaces';
    protected $appends = ['geometry'];
    protected $visible = [
        'id', 'type', 'point', 'line_string', 'polygon',
        'multi_point', 'multi_line_string', 'multi_polygon',
    ];
    protected $fillable = [
        'point', 'geometry',
    ];
    protected $spatialFields = [
        'point', 'line_string', 'polygon',
        'multi_point', 'multi_line_string', 'multi_polygon',
    ];

    public function getGeometryAttribute()
    {
        switch ($this->type) {
            case 'LineString':
                return $this->line_string;
            case 'Polygon':
                return $this->attributes['polygon']; //->polygon;
            case 'MultiPoint':
                return $this->multi_point;
            case 'MultiLineString':
                return $this->multi_line_string;
            case 'MultiPolygon':
                return $this->multi_polygon;
            default:
                return $this->point;
        }
    }

    public function setGeometryAttribute($value)
    {
        if (is_string($value)) {
            $geometry = Geometry::fromJson($value);
        } elseif ($value instanceof Geometry) {
            $geometry = $value;
        } else {
            throw new Exception('Invalid geometry');
        }
        $reflect = new ReflectionClass($geometry);
        $this->attributes[Str::snake($reflect->getShortName())] = $geometry;
        $this->attributes['type'] = $reflect->getShortName();
        $this->attributes['point'] = GeoUtils::getCentroid($geometry);
    }
}
