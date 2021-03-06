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
                    'maxLength' => 100,
                ],
                'description' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 750,
                ],
                'public_data' => $type->public_schema,
                'private_data' => $type->private_schema,
            ],
            'required' => [
                'name', 'description', 'public_data', 'private_data'
            ],
            'additionalProperties' => false,
        ];
        if (isset($options['edit'])) {
            $schema = $this->validation->prepareSchema($schema);
        }
        return $schema;
    }

    public function retrieveInitiative($subject, $id, $options = [])
    {
        $init = $this->db->query('App:Initiative', [
            'terms', 'countries', 'city.country.region', 'subject'
        ])->findOrFail($id);
        if ($this->authorization->check($subject, 'updateInitiative', $init)) {
            $init->addVisible('private_data');
        }
        if (isset($subject->id)) {
            $relation = $this->db->table('subject_group')
            ->select('relation', 'title')
            ->where([
                ['subject_id', '=', $subject->id],
                ['group_id', '=', $init->id],
            ])->first();
        } else {
            $relation = null;
        }
        $init->setContext([
            'connection' => $relation,
        ]);
        return $init;
    }

    public function retrieveInitiatives($subject, $options = [])
    {
        $pagSch = $this->helper->getPaginatedQuerySchema([
            'city_id' => [
                'type' => 'integer',
                'minimum' => -1,
            ],
            'country_id' => [
                'type' => 'integer',
                'minimum' => 0,
            ],
            'region_id' => [
                'type' => 'integer',
                'minimum' => 0,
            ],
            'registered_city_id' => [
                'type' => 'integer',
                'minimum' => 0,
            ],
            's' => [
                'type' => 'string',
            ],
            'terms' => [
                'type' => 'array',
                'items' => [
                    'type' => 'integer',
                    'minimum' => 1,
                ],
            ],
            'countries' => [
                'type' => 'array',
                'items' => [
                    'type' => 'integer',
                    'minimum' => 1,
                ],
            ],
        ], 200);
        $v = $this->validation->fromSchema($pagSch);
        $options = Utils::prepareData($pagSch, $options);
        $v->assert($options);
        $query = $this->db->query('App:Initiative', ['terms', 'subject']);
        if (isset($options['city_id'])) {
            if ($options['city_id'] == -1) {
                $query->whereNull('city_id');
            } else {
                $query->where('city_id', $options['city_id']);
            }
        } elseif (isset($options['country_id'])) {
            $query->whereHas('city', function ($q) use ($options) {
                $q->where('country_id', $options['country_id']);
            });
        } elseif (isset($options['region_id'])) {
            $query->whereHas('city.country', function ($q) use ($options) {
                $q->where('region_id', $options['region_id']);
            });
        } elseif (isset($options['registered_city_id'])) {
            $query->whereHas('city.registered_city', function ($q) use ($options) {
                $q->where('id', $options['registered_city_id']);
            });
        }
        if (isset($options['s'])) {
            $query->whereHas('subject', function ($q) use ($options) {
                $filter = Utils::traceStr($options['s']);
                $q->where('trace', 'LIKE', "%$filter%");
            });
        }
        if (isset($options['terms'])) {
            $query->whereHas('terms', function ($q) use ($options) {
                $q->whereIn('term_id', $options['terms']);
            });
        }
        if (isset($options['countries'])) {
            $query->whereHas('countries', function ($q) use ($options) {
                $q->whereIn('country_id', $options['countries']);
            });
        }
        return new Paginator($query, $options);
    }

    public function retrieveMembers($subject, $id, $options = [])
    {
        $init = $this->db->query('App:Initiative')
            ->findOrFail($id);
        $pagSch = $this->helper->getPaginatedQuerySchema([
            'relation' => [
                'type' => 'string',
                'enum' => ['owner'],
            ],
        ]);
        $v = $this->validation->fromSchema($pagSch);
        $options = $this->validation->prepareData($pagSch, $options, true);
        $v->assert($options);
        $query = $init->members();
        if (isset($options['relation'])) {
            $query->wherePivot('relation', $options['relation']);
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
                'terms' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'integer',
                        'minimum' => 1,
                    ],
                ],
                'countries' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'integer',
                        'minimum' => 1,
                    ],
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
        if (isset($options['terms'])) {
            $this->attachTerms($subject, $initiative->id, $options, 0);
        }
        if (isset($options['countries'])) {
            $this->attachCountries($subject, $initiative->id, $options, 0);
        }
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'createInitiative',
                'object' => $initiative,
            ]);
        }
        return $initiative;
    }

    public function updateInitiative($subject, $iniId, $data, $options = [], $flags = 3)
    {
        $init = $this->db->query('App:Initiative')
            ->findOrFail($iniId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'updateInitiative', $init
            );
        }
        $schema = $this->retrieveSchema(['edit' => true]);
        $v = $this->validation->fromSchema($schema);
        $data = $this->validation->prepareData($schema, $data);
        $v->assert($data);
        $init->fill($data);
        $init->save();
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'updateInitiative',
                'object' => $init,
            ]);
        }
        return $init;
    }

    public function deleteInitiative($subject, $iniId, $options = [], $flags = 3)
    {
        $init = $this->db->query('App:Initiative', ['terms', 'city'])
            ->findOrFail($iniId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'deleteInitiative', $init
            );
        }
        $city = $init->city;
        $deleted = $init->delete();
        if (isset($city) && $deleted) {
            $city->decrement('initiatives_count');
            if ($city->initiatives_count == 0) {
                $city->delete();
            }
        }
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'deleteInitiative',
                'object' => $init,
            ]);
        }
        return $deleted;
    }

    public function attachCity($subject, $iniId, $data, $flags = 3)
    {
        $init = $this->db->query('App:Initiative')->findOrFail($iniId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'updateInitiative', $init
            );
        }
        $schema = [
            'type' => 'object',
            'properties' => [
                'registered_city_id' => [
                    'type' => 'integer',
                    'minimum' => 1,
                ],
            ],
            'additionalProperties' => false,
            'required' => ['registered_city_id'],
        ];
        $v = $this->validation->fromSchema($schema);
        $data = $this->validation->prepareData($schema, $data);
        $regCity = $this->db->query('App:RegisteredCity')
            ->findOrFail($data['registered_city_id']);
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
        $init->city_id = $cityId;
        $init->save();
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'updateInitiative',
                'object' => $init,
            ]);
        }
        return $mapCity;
    }

    public function detachCity($subject, $iniId, $flags = 3)
    {
        $init = $this->db->query('App:Initiative', ['city'])
            ->findOrFail($iniId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'updateInitiative', $init
            );
        }
        $city = $init->city;
        if (isset($city)) {
            $init->city()->dissociate();
            $city->decrement('initiatives_count');
            if ($city->initiatives_count == 0) {
                $city->delete();
            }
            return true;
        }
        return false;
    }

    public function attachTerms($subject, $iniId, $data, $flags = 7)
    {
        $init = $this->db->query('App:Initiative')->findOrFail($iniId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'associateInitiativeTerm', $init
            );
        }
        if ($flags & Utils::VALIDATIONFLAG) {
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
        }
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

    public function attachCountries($subject, $iniId, $data, $flags = 7)
    {
        $init = $this->db->query('App:Initiative')->findOrFail($iniId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'associateInitiativeCountry', $init
            );
        }
        if ($flags & Utils::VALIDATIONFLAG) {
            $schema = [
                'type' => 'object',
                'properties' => [
                    'countries' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'integer',
                            'minimum' => 1,
                        ],
                    ],
                ],
                'additionalProperties' => false,
                'required' => ['countries'],
            ];
            $v = $this->validation->fromSchema($schema);
            $data = $this->validation->prepareData($schema, $data);
        }
        $countries = $this->db->query('App:Country')
            ->whereIn('id', $data['countries'])
            ->get();
        $changes = $init->countries()->syncWithoutDetaching(
            $countries->pluck('id')->toArray()
        );
        foreach ($countries as $country) {
            if (in_array($country->id, $changes['attached'])) {
                $country->increment('initiatives_count');
            }
        }
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'associateInitiativeCountry',
                'object' => $init,
            ]);
        }
        return $changes['attached'];
    }

    public function detachCountry($subject, $iniId, $couId, $flags = 3)
    {
        $init = $this->db->query('App:Initiative')->findOrFail($iniId);
        $coun = $this->db->query('App:Country')->findOrFail($couId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'associateInitiativeCountry', $init
            );
        }
        $changes = $init->countries()->detach($couId);
        if ($changes >= 1) {
            $coun->decrement('initiatives_count');
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

    public function addMember($subject, $iniId, $subId, $flags = 3)
    {
        $init = $this->db->query('App:Initiative')->findOrFail($iniId);
        $user = $this->db->query('App:Subject')
            ->where('type', 'User')
            ->findOrFail($subId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'joinInitiative', $user
            );
        }
        $changes = $init->members()->syncWithoutDetaching([
            $subId => [
                'relation' => 'follower',
            ],
        ]);
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'joinInitiative',
                'object' => $init,
            ]);
        }
        return $changes > 0;
    }

    public function removeMember($subject, $iniId, $subId, $flags = 3)
    {
        $init = $this->db->query('App:Initiative')->findOrFail($iniId);
        $user = $this->db->query('App:Subject')
            ->where('type', 'User')
            ->findOrFail($subId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'leaveInitiative', $user
            );
        }
        $changes = $init->members()->detach($subId);
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'leaveInitiative',
                'object' => $init,
            ]);
        }
        return $changes > 0;
    }

    public function updateMember($subject, $iniId, $subId, $data, $flags = 3)
    {
        $init = $this->db->query('App:Initiative')->findOrFail($iniId);
        $user = $this->db->query('App:Subject')
            ->where('type', 'User')
            ->findOrFail($subId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'updateInitiativeMember', $init
            );
        }
        $schema = [
            'type' => 'object',
            'properties' => [
                'relation' => [
                    'type' => 'string',
                    // TODO get from InitiativeType
                    'enum' => ['follower', 'member', 'owner'],
                ],
            ],
            'additionalProperties' => false,
            'required' => ['relation'],
        ];
        $v = $this->validation->fromSchema($schema);
        $data = $this->validation->prepareData($schema, $data);
        $changes = $init->members()->syncWithoutDetaching([
            $subId => [
                'relation' => $data['relation'],
            ],
        ]);
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'updateInitiativeMember',
                'object' => $init,
            ]);
        }
        return $changes > 0;
    }

    public function updatePicture($subject, $iniId, $pic, $file, $flags = 3)
    {
        $init = $this->db->query('App:Initiative')->findOrFail($iniId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'updateInitiative', $init
            );
        }
        // TODO improve and document error
        if (!in_array($pic, ['cover'])) {
            throw new AppException(
                'Picture not valid', 'invalidOption'
            );
        }
        $baseDir = __DIR__ . '/../../public';
        $localDir = '/data/initiatives/' . $iniId;
        $fileName =  $pic . '.jpg';
        if (!file_exists($baseDir . $localDir)) {
            mkdir($baseDir . $localDir, 0777, true);
        }
        $this->image->make($file->getContents())
            ->widen(1000)->resizeCanvas(1000, 600, 'center', false, '#F7912D')
            ->save($baseDir . $localDir . '/' . $fileName, 85);
        $pictures = $init->pictures;
        $pictures[$pic] = [
            'path' => $localDir . '/' . $fileName,
        ];
        $init->pictures = $pictures;
        $init->save();
        return $pictures;
    }

    public function deletePicture($subject, $iniId, $pic, $flags = 3)
    {
        $init = $this->db->query('App:Initiative')->findOrFail($iniId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'updateInitiative', $init
            );
        }
        $pictures = $init->pictures;
        // TODO improve and document error
        if (!isset($pictures[$pic])) {
            throw new AppException(
                'Picture not valid', 'invalidOption'
            );
        }
        $path = __DIR__ . '/../../public/data/initiatives/' . $iniId . '/' .
            $pic . '.jpg';
        $exists = file_exists($path);
        if (!$exists) {
            return false;
        }
        unlink($path);
        unset($pictures[$pic]);
        $init->pictures = $pictures;
        $init->save();
        return true;
    }
}
