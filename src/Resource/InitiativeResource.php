<?php

namespace App\Resource;

use App\Util\Exception\AppException;
use App\Util\Paginator;
use App\Util\Utils;

class InitiativeResource extends Resource
{
    public function retrieveSchema($options = [])
    {
        $type = $this->db->query('App:GroupType')->findOrFail('Initiative');
        $schema = [
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 50,
                ],
                'description' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 500,
                ],
                'public_data' => $type->public_schema,
                'private_data' => $type->private_schema,
            ],
            'required' => [
                'name', 'description', 'public_data', 'private_data'
            ],
            'additionalProperties' => false,
        ];
        return $schema;
    }

    public function retrieveInitiative($subject, $id, $options = [])
    {
        return $this->db->query('App:Initiative')
            ->findOrFail($id);
    }

    public function createInitiative($subject, $data, $options = [], $flags = 3)
    {
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail($subject, 'createInitiative');
        }
        $schema = $this->retrieveSchema();
        $v = $this->validation->fromSchema($schema);
        $data = $this->validation->prepareData($schema, $data, true);
        $v->assert($data);
        $optSchema = [
            'type' => 'object',
            'properties' => [
                'registered_city_id' => [
                    'type' => 'integer',
                    'minimum' => 1,
                ],
                'set_owner' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
            ],
            'additionalProperties' => false,
        ];
        $v = $this->validation->fromSchema($optSchema);
        $options = $this->validation->prepareData($optSchema, $options, true);
        $v->assert($options);
        if (isset($options['registered_city_id'])) {
            $regCity = $this->db->query('App:RegisteredCity')
                ->findOrFail($options['registered_city_id']);
            if (is_null($regCity->city_id)) {
                $mapCity = $this->createCityFromRegistered($regCity);
                $mapCity->increment('initiatives_count');
                $regCity->city()->associate($mapCity);
                $regCity->save();
                $cityId = $mapCity->id;
            } else {
                $cityId = $regCity->city_id;
                $mapCity = $this->db->query('App:City')->find($cityId);
                $mapCity->increment('initiatives_count');
            }
        } else {
            $cityId = null;
        }
        $initiative = $this->db->create('App:Initiative', $data);
        $initiative->city_id = $cityId;
        $initiative->save();
        if ($options['set_owner']) {
            $initiative->members()->attach(
                $subject->id, ['relation' => 'owner']
            );
        }
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'createInitiative',
                'object' => $initiative,
            ]);
        }
        return $initiative;
    }

    private function createCityFromRegistered($registered)
    {
        $s = $this->db->createAndSave('App:Space', [
            'point' => $registered->point,
        ]);
        return $this->db->createAndSave('App:City', [
            'name' => $registered->name,
            'country_id' => $registered->country_id,
            'space_id' => $s->id,
        ]);
    }
}
