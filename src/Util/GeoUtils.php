<?php

namespace App\Util;

use Grimzy\LaravelMysqlSpatial\Types\MultiPoint;
use Grimzy\LaravelMysqlSpatial\Types\MultiPolygon;
use Grimzy\LaravelMysqlSpatial\Types\MultiLineString;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Grimzy\LaravelMysqlSpatial\Types\PointCollection;
use Grimzy\LaravelMysqlSpatial\Types\Polygon;

class GeoUtils
{
    static public function getCentroid($geometry)
    {
        if ($geometry instanceof Point) {
            return $geometry;
        } elseif ($geometry instanceof PointCollection) {
            $points = $geometry->getPoints();
            $sumLat = 0;
            $sumLng = 0;
            $countP = 0;
            foreach ($points as $p) {
                $sumLat += $p->getLat();
                $sumLng += $p->getLng();
                $countP++;
            }
            return new Point($sumLat / $countP, $sumLng / $countP);
        } elseif ($geometry instanceof Polygon) {
            $border = $geometry->getLineStrings()[0];
            return self::getCentroid($border);
        } elseif ($geometry instanceof MultiLineString) {
            $shapes = $geometry->getLineStrings();
            $points = [];
            foreach ($shapes as $s) {
                $points[] = self::getCentroid($s);
            }
            return self::getCentroid(new MultiPoint($points));
        } elseif ($geometry instanceof MultiPolygon) {
            $shapes = $geometry->getPolygons();
            $points = [];
            foreach ($shapes as $s) {
                $points[] = self::getCentroid($s);
            }
            return self::getCentroid(new MultiPoint($points));
        }
        return new Point(0, 0);
    }
}