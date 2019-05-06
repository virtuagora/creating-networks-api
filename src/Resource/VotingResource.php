<?php

namespace App\Resource;

use App\Util\Exception\AppException;

class VotingResource extends Resource
{
    public function retrieveSchema($options = [])
    {
        $schema = [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 200,
                ],
                'text' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'close_date' => [
                    'type' => 'string',
                    'format' => 'datetime'
                ],
                'options' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                        'minLength' => 1,
                        'maxLength' => 200,
                    ],
                ],
            ],
            'required' => ['title', 'text', 'options'],
            'additionalProperties' => false,
        ];
        return $schema;
    }

    public function createOne($subject, $data)
    {
        $v = $this->validation->fromSchema($this->retrieveSchema());
        $v->assert($data);
        $voting = $this->db->new('App:Voting');
        $voting->title = $data['title'];
        $voting->text = $data['text'];
        if (isset($data['close_date'])) {
            $voting->close_date = $data['close_date'];
        }
        $voting->title = $data['title'];
        $voting->save();

        $ballot = $this->db->new('App:Ballot');
        $ballot->options = $data['options'];
        $ballot->node_id = $voting->id;
        $ballot->save();

        return $voting;
    }
}
