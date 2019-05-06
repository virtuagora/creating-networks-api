<?php

namespace App\Resource;

use App\Util\ContainerClient;
use App\Util\Paginator;
use App\Util\Exception\AppException;
use App\Util\Exception\UnauthorizedException;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use GeoJson\GeoJson;

class PlaceResource extends ContainerClient
{
    public function getSchema()
    {
        $schema = [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 250,
                ],
                'content' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 1000,
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
                            'contains' => [
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
            'required' => ['title', 'point'],
            'additionalProperties' => false,
        ];
        return $schema;
    }

    public function createOne($subject, $data, $options = [])
    {
        $options = $this->helper->initializeOptions($options);
        if ($options['auth']) {
            $this->authorization->checkOrFail($subject, 'createPlace');
        }
        $schema = $this->getSchema();
        $v = $this->validation->fromSchema($schema);
        $data = $this->validation->prepareData($schema, $data, true);
        $v->assert($data);
        $place = $this->db->newInstance('App:Place', $data);
        $place->author_id = $subject->getId();
        $place->meta = [
            'score' => 0,
        ];
        $space = $this->db->newInstance('App:Space');
        $space->point = Point::fromJson(GeoJson::jsonUnserialize($data['point']));
        $space->save();
        $place->space()->associate($space);
        $place->save();

        $ballot = $this->db->newInstance('App:Ballot');
        $ballot->options = [
            'secure' => [
                'name' => 'Lugar seguro',
                'relation' => 'rater',
                'value' => 1,
                'votes' => 0,
            ],
            'insecure' => [
                'name' => 'Lugar inseguro',
                'relation' => 'rater',
                'value' => -1,
                'votes' => 0,
            ],
        ];
        $ballot->node()->associate($place);
        $ballot->save();

        if ($options['log']) {
            $this->resources['log']->createOne($subject, [
                'action' => 'createPlace',
                'object' => $place,
            ]);
        }

        return $place;
    }

    public function retrieveOne($subject, $pla, $options = [])
    {
        return $this->db->query('App:Place')->findOrFail($pla);
    }

    public function retrieveMulti($subject, $options)
    {
        $schema = $this->helper->getPaginatedQuerySchema([
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
        $v = $this->validation->fromSchema($schema);
        $options = $this->validation->prepareData($schema, $options);
        $v->assert($options);
        $query = $this->db->query('App:Place');
        if (isset($options['distance'])) {
            $query->whereHas('space', function ($q) use ($options) {
                list($a, $b) = explode(',', $options['from']);
                $q->distance('point', new Point($a, $b), $options['distance']);
            });
        }
        if (isset($options['s'])) {
            $filter = $this->helper->generateTrace($options['s']);
            $query->where('trace', 'LIKE', "%$filter%");
        }
        return new Paginator($query, $options);
    }

    public function createOneVote($subject, $pla, $data, $options = [])
    {
        $options = $this->helper->initializeOptions($options);
        $place = $this->db->query('App:Place')->findOrFail($pla);
        if ($options['auth']) {
            $this->authorization->checkOrFail($subject, 'votePlace', $place);
        }
        $ballot = $place->ballot;
        //$voted = $this->resources['ballot']->createOneVote($subject, $bal, $data);
        $schema = [
            'type' => 'object',
            'properties' => [
                'vote' => [
                    'type' => 'string',
                    'enum' => array_keys($ballot->options),
                ],
            ],
            'required' => ['vote'],
            'additionalProperties' => false,
        ];
        $v = $this->validation->fromSchema($schema);
        $data = $this->validation->prepareData($schema, $data, true);
        $v->assert($data);

        $subId = $subject->getId();
        if ($place->relations()->where('subject_id', $subId)->exists()) {
            $place->relations()->updateExistingPivot($subId, [
                'relation' => $data['vote'],
                'value' => $ballot->options[$data['vote']]['value'],
            ]);
        } else {
            $place->relations()->attach($subId, [
                'relation' => $data['vote'],
                'value' => $ballot->options[$data['vote']]['value'],
            ]);
            $ballot->total_votes = $place->relations()->count();
        }
        $place->points = $place->relations()->sum('value');
        $place->setMeta('score', $place->relations()->avg('value'));
        $ballot->save();
        $place->save();
        return $place;
    }
}
