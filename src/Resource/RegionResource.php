<?php

namespace App\Resource;

use App\Util\Exception\AppException;
use App\Util\Paginator;
use App\Util\Utils;
use Grimzy\LaravelMysqlSpatial\Types\Point;

class RegionResource extends Resource
{
    public function retrieveRegion($subject, $id, $options = [])
    {
        return $this->db->query('App:Region')
            ->findOrFail($id);
    }

    public function retrieveRegions($subject, $options = [])
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
        $query = $this->db->query('App:Region');
        if (isset($options['distance'])) {
            $query->whereHas('space', function ($q) use ($options) {
                list($a, $b) = explode(',', $options['from']);
                $q->distance('point', new Point($a, $b), $options['distance']);
            });
        }
        return new Paginator($query, $options);
    }
}
