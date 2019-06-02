<?php

namespace App\Resource;

use App\Util\Exception\AppException;
use App\Util\Paginator;
use App\Util\Utils;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use GeoJson\GeoJson;

class CityResource extends Resource
{
    // public function retrieveSchema($options = [])
    // {
    //     $type = $this->db->query('App:GroupType')->findOrFail('Initiative');
    //     $schema = [
    //         'type' => 'object',
    //         'properties' => [
    //             'name' => [
    //                 'type' => 'string',
    //                 'minLength' => 1,
    //                 'maxLength' => 50,
    //             ],
    //             'description' => [
    //                 'type' => 'string',
    //                 'minLength' => 1,
    //                 'maxLength' => 500,
    //             ],
    //             'data' => $type->schema,
    //         ],
    //         'required' => ['name', 'description', 'data'],
    //         'additionalProperties' => false,
    //     ];
    //     return $schema;
    // }

    public function retrieveCity($subject, $id, $options = [])
    {
        return $this->db->query('App:City', ['initiatives'])
            ->findOrFail($id);
    }

    public function retrieveCities($subject, $options = [])
    {
        $pagSch = $this->helper->getPaginatedQuerySchema([
            'distance' => [
                'type' => 'integer',
            ],
            'from' => [
                'type' => 'string',
            ],
            'country_id' => [
                'type' => 'integer',
                'minimum' => 1,
            ],
        ]);
        $v = $this->validation->fromSchema($pagSch);
        $options = $this->validation->prepareData($pagSch, $options, true);
        $v->assert($options);
        $query = $this->db->query('App:City');
        if (isset($options['distance'])) {
            $query->whereHas('space', function ($q) use ($options) {
                list($a, $b) = explode(',', $options['from']);
                $q->distance('point', new Point($a, $b), $options['distance']);
            });
        }
        if (isset($options['country_id'])) {
            $query->where('country_id', $options['country_id']);
        }
        return new Paginator($query, $options);
    }

    public function retrieveRegisteredCities($subject, $options = [])
    {
        $pagSch = $this->helper->getPaginatedQuerySchema([
            'distance' => [
                'type' => 'integer',
            ],
            'from' => [
                'type' => 'string',
            ],
            'country_id' => [
                'type' => 'integer',
                'minimum' => 1,
            ],
            's' => [
                'type' => 'string',
            ],
        ]);
        $v = $this->validation->fromSchema($pagSch);
        $options = $this->validation->prepareData($pagSch, $options, true);
        $v->assert($options);
        $query = $this->db->query('App:RegisteredCity');
        if (isset($options['distance'])) {
            list($a, $b) = explode(',', $options['from']);
            $query->distance('point', new Point($a, $b), $options['distance']);
        }
        if (isset($options['s'])) {
            $filter = Utils::traceStr($options['s']);
            $query->where('trace', 'LIKE', "%$filter%");
        }
        if (isset($options['country_id'])) {
            $query->where('country_id', $options['country_id']);
        }
        return new Paginator($query, $options);
    }

    public function createRegisteredCity($subject, $data, $options = [], $flags = 3)
    {
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail($subject, 'createRegisteredCity');
        }
        $schema = [
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                ],
                'country_id' => [
                    'type' => 'integer',
                    'minimum' => 1,
                ],
                'point' => [
                    'type' => 'object',
                    'properties' => [
                        'type' => [
                            'type' => 'string',
                            'const' => 'Point',
                        ],
                        'coordinates' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'number',
                            ],
                            'minItems' => 2,
                            'maxItems' => 2,
                        ],
                    ],
                    'required' => ['type', 'coordinates'],
                    'additionalProperties' => false,
                ],
            ],
            'required' => ['name', 'contry_id', 'point'],
            'additionalProperties' => false,
        ];
        $v = $this->validation->fromSchema($schema);
        $data = $this->validation->prepareData($schema, $data, true);
        $v->assert($data);
        // TODO check if country_id exists
        $regCity = $this->db->create('App:RegisteredCity', $data);
        $regCity->point = Point::fromJson(
            GeoJson::jsonUnserialize($data['point'])
        );
        $regCity->trace = Utils::traceStr($data['name']);
        $regCity->save();
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'createRegisteredCity',
                'object' => $regCity,
            ]);
        }
        return $regCity;
    }
}
