<?php

namespace App\Resource;

use App\Util\Exception\AppException;
use App\Util\Paginator;
use App\Util\Utils;

class VideoResource extends Resource
{
    public function retrieveSchema($options = [])
    {
        $type = $this->db->query('App:NodeType')->findOrFail('Video');
        $schema = [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 100,
                ],
                'content' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 1000,
                ],
                'public_data' => $type->public_schema,
                'private_data' => $type->private_schema,
            ],
            'required' => [
                'title', 'content', 'public_data', 'private_data'
            ],
            'additionalProperties' => false,
        ];
        if (isset($options['edit'])) {
            $schema = $this->validation->prepareSchema($schema);
        }
        return $schema;
    }

    public function retrieveVideo($subject, $id, $options = [])
    {
        $vide = $this->db->query(
            'App:Node', ['subjects']
        )->findOrFail($id);
        if ($this->authorization->check($subject, 'updateVideo', $vide)) {
            $vide->addVisible('private_data');
        }
        return $vide;
    }

    public function retrieveVideos($subject, $options = [])
    {
        $pagSch = $this->helper->getPaginatedQuerySchema([
            's' => [
                'type' => 'string',
            ],
        ], 50);
        $v = $this->validation->fromSchema($pagSch);
        $options = $this->validation->prepareData($pagSch, $options, true);
        $v->assert($options);
        $query = $this->db->query('App:Video');
        if (isset($options['s'])) {
            $filter = Utils::traceStr($options['s']);
            $query->where('trace', 'LIKE', "%$filter%");
        }
        return new Paginator($query, $options);
    }

    public function createVideo($subject, $data, $options = [], $flags = 3)
    {
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail($subject, 'createVideo');
        }
        $schema = $this->retrieveSchema();
        $v = $this->validation->fromSchema($schema);
        $data = $this->validation->prepareData($schema, $data);
        $v->assert($data);
        $optSchema = [
            'type' => 'object',
            'properties' => [
                'initiative_id' => [
                    'type' => 'integer',
                    'minimum' => 1,
                ],
            ],
            'additionalProperties' => false,
        ];
        $v = $this->validation->fromSchema($optSchema);
        $options = $this->validation->prepareData($optSchema, $options, true);
        $v->assert($options);
        if (isset($options['initiative_id'])) {
            $init = $this->db->query('App:Initiative', ['subject'])
                ->find($options['initiative_id']);
        }
        $video = $this->db->create('App:Video', $data);
        $video->author_id = $subject->id;
        $video->save();
        if (isset($init)) {
            $video->subjects()->attach(
                $init->subject->id, ['relation' => 'initiative']
            );
        }
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'createVideo',
                'object' => $video,
            ]);
        }
        return $video;
    }

    public function updateVideo($subject, $vidId, $data, $options = [], $flags = 3)
    {
        $vide = $this->db->query('App:Video')
            ->findOrFail($vidId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'updateVideo', $vide
            );
        }
        $schema = $this->retrieveSchema(['edit' => true]);
        $v = $this->validation->fromSchema($schema);
        $data = $this->validation->prepareData($schema, $data);
        $v->assert($data);
        $vide->fill($data);
        $vide->save();
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'updateVideo',
                'object' => $vide,
            ]);
        }
        return $vide;
    }

    public function deleteVideo($subject, $vidId, $options = [], $flags = 3)
    {
        $vide = $this->db->query('App:Video')
            ->findOrFail($vidId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'deleteVideo', $vide
            );
        }
        $deleted = $vide->delete();
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'deleteVideo',
                'object' => $vide,
            ]);
        }
        return $deleted;
    }
}
