<?php

namespace App\Resource;

use App\Util\Exception\AppException;
use App\Util\Paginator;
use App\Util\Utils;
use Grimzy\LaravelMysqlSpatial\Types\Point;

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
        return $this->db->query('App:City')
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
        return new Paginator($query, $options);
    }
}
