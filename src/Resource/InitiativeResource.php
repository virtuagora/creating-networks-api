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

    public function retrieveInitiatives($subject, $options = [])
    {
        $pagSch = $this->helper->getPaginatedQuerySchema([
            'city_id' => [
                'type' => 'integer',
                'minimum' => -1,
            ],
            's' => [
                'type' => 'string',
            ],
        ]);
        $v = $this->validation->fromSchema($pagSch);
        $options = $this->validation->prepareData($pagSch, $options, true);
        $v->assert($options);
        $query = $this->db->query('App:Initiative');
        if (isset($options['city_id'])) {
            if ($options['city_id'] == -1) {
                $query->whereNull('city_id');
            } else {
                $query->where('city_id', $options['country_id']);
            }
        }
        if (isset($options['s'])) {
            $filter = Utils::traceStr($options['s']);
            $query->where('trace', 'LIKE', "%$filter%");
        }
        return new Paginator($query, $options);
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
        $iniSub = $this->db->create('App:Subject', [
            'username' => 'group:' . $initiative->id,
            'display_name' => $initiative->name,
            'img_type' => 0,
            'img_hash' => 'group',
        ]);
        $iniSub->type = 'Group';
        $iniSub->group()->associate($initiative);
        $iniSub->save();
        $iniSub->roles()->attach('InitiativeGroup');
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

    public function attachTerms($subject, $iniId, $data, $flags = 3)
    {
        $init = $this->db->query('App:Initiative')->findOrFail($iniId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'associateInitiativeTerm', $init
            );
        }
        $schema = [
            'type' => 'object',
            'properties' => [
                'terms' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'integer',
                        'minimum' => 1,
                    ],
                ],
            ],
            'additionalProperties' => false,
            'required' => ['terms'],
        ];
        $v = $this->validation->fromSchema($schema);
        $data = $this->validation->prepareData($schema, $data);
        $terms = $this->db->query('App:Term')
            ->whereIn('id', $data['terms'])
            ->get();
        $changes = $init->terms()->syncWithoutDetaching(
            $terms->pluck('id')->toArray()
        );
        foreach ($terms as $term) {
            if (in_array($term->id, $changes['attached'])) {
                $term->increment('count');
            }
        }
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'associateInitiativeTerm',
                'object' => $init,
            ]);
        }
        return $changes['attached'];
    }

    public function detachTerm($subject, $iniId, $trmId, $flags = 3)
    {
        $init = $this->db->query('App:Initiative')->findOrFail($iniId);
        $term = $this->db->query('App:Term')->findOrFail($trmId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'associateInitiativeTerm', $init
            );
        }
        $changes = $init->terms()->detach($trmId);
        if ($changes >= 1) {
            $term->decrement('count');
            return true;
        }
        return false;
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
