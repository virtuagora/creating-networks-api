<?php

namespace App\Resource;

use App\Util\Exception\AppException;
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
                'data' => $type->schema,
            ],
            'required' => ['name', 'description', 'data'],
            'additionalProperties' => false,
        ];
        return $schema;
    }

    public function retrieveInitiative($subject, $id, $options = [])
    {
        return $this->db->query('App:Initiative')
            ->findOrFail($id);
    }

    public function createInitiative($subject, $data)
    {
        $v = $this->validation->fromSchema($this->retrieveSchema());
        $v->assert($this->validation->prepareData($schema, $data, true));
        $initiative = $this->db->create('App:Initiative', $data);
        $initiative->save();
        return $initiative;
    }
}
