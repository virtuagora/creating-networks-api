<?php

namespace App\Resource;

use App\Util\Exception\AppException;
use App\Util\Paginator;
use App\Util\Utils;

class TermResource extends Resource
{
    public function retrieveSchema($options = [])
    {
        $options = array_merge([
            'taxonomy_id' => 'topics',
        ], $options);
        $taxonomy = $this->db->query('App:Taxonomy')
            ->findOrFail($options['taxonomy_id']);
        $schema = [
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 100,
                ],
                'taxonomy_id' => [
                    'type' => 'string',
                    'const' => $taxonomy->id,
                ],
                'data' => $taxonomy->schema,
            ],
            'required' => ['name', 'taxonomy_id', 'data'],
            'additionalProperties' => false,
        ];
        return $schema;
    }

    public function retrieveTerm($subject, $id, $options = [])
    {
        return $this->db->query('App:Term')
            ->findOrFail($id);
    }

    public function retrieveTerms($subject, $options = [])
    {
        $pagSch = $this->helper->getPaginatedQuerySchema([
            'taxonomy' => [
                'type' => 'string',
                // TODO get from DB
                'enum' => ['topics'],
            ],
            's' => [
                'type' => 'string',
            ],
        ]);
        $v = $this->validation->fromSchema($pagSch);
        $options = $this->validation->prepareData($pagSch, $options, true);
        $v->assert($options);
        $query = $this->db->query('App:Term');
        if (isset($options['taxonomy'])) {
            $query->where('taxonomy_id', $options['taxonomy']);
        }
        if (isset($options['s'])) {
            $filter = Utils::traceStr($options['s']);
            $query->where('trace', 'LIKE', "%$filter%");
        }
        return new Paginator($query, $options);
    }

    public function createTerm($subject, $data, $options = [], $flags = 3)
    {
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail($subject, 'createTerm');
        }
        $schema = $this->retrieveSchema();
        $v = $this->validation->fromSchema($schema);
        $data = $this->validation->prepareData($schema, $data);
        $v->assert($data);
        $term = $this->db->create('App:Term', $data);
        $term->trace = Utils::traceStr($data['name']);
        $term->save();
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'createTerm',
                'object' => $term,
            ]);
        }
        return $term;
    }
}
