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
                'type' => 'string',
            ],
            'region_id' => [
                'type' => 'integer',
                'minimum' => 1,
            ],
        ]);
        $v = $this->validation->fromSchema($pagSch);
        $options = $this->validation->prepareData($pagSch, $options, true);
        $v->assert($options);
        $query = $this->db->query('App:Country');
        if (isset($options['distance'])) {
            $query->whereHas('space', function ($q) use ($options) {
                list($a, $b) = explode(',', $options['from']);
                $q->distance('point', new Point($a, $b), $options['distance']);
            });
        }
        if (isset($options['region_id'])) {
            $query->where('region_id', $options['region_id']);
        }
        return new Paginator($query, $options);
    }
}
