<?php

namespace App\Resource;

use App\Util\Exception\AppException;
use App\Util\Paginator;
use App\Util\Utils;
use Grimzy\LaravelMysqlSpatial\Types\Point;

class CountryResource extends Resource
{
    public function retrieveCountry($subject, $id, $options = [])
    {
        return $this->db->query('App:Country')
            ->findOrFail($id);
    }

    public function retrieveCountries($subject, $options = [])
    {
        $pagSch = $this->helper->getPaginatedQuerySchema([
            'distance' => [
                'type' => 'integer',
            ],
            'from' => [
                'type' => 'array',
                'minItems' => 2,
                'maxItems' => 2,
                'items' => [
                    'type' => 'number',
                ],
            ],
            'having' => [
                'type' => 'array',
                'items' => [
                    'type' => 'string',
                    'enum' => ['cities', 'initiatives'],
                ],
            ],
            'region_id' => [
                'type' => 'integer',
                'minimum' => 1,
            ],
        ], 210);
        $v = $this->validation->fromSchema($pagSch);
        $options = Utils::prepareData($pagSch, $options);
        $v->assert($options);
        $query = $this->db->query('App:Country');
        if (isset($options['distance'])) {
            $query->whereHas('space', function ($q) use ($options) {
                list($a, $b) = $options['from'];
                $q->distance('point', new Point($a, $b), $options['distance']);
            });
        }
        if (isset($options['region_id'])) {
            $query->where('region_id', $options['region_id']);
        }
        if (isset($options['having'])) {
            foreach ($options['having'] as $rel) {
                $query->has($rel);
            }
        }
        return new Paginator($query, $options);
    }
}
