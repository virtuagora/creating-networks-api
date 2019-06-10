<?php

namespace App\Service;

use App\Util\DummySubject;
use App\Util\Exception\AppException;

class HelperService
{
    protected $validation;
    protected $router;
    protected $request;
    
    public function __construct($validation, $router, $request)
    {
        $this->validation = $validation;
        $this->router = $router;
        $this->request = $request;
    }

    public function baseUrl($request = null)
    {
        if (is_null($request)) {
            $request = $this->request;
        }
        return $request->getUri()->getBaseUrl();
    }

    public function pathFor($name, $params = [], $query = [], $full = false)
    {
        if ($full) {
            return $this->baseUrl().$this->router->pathFor($name, $params, $query);
        } else {
            return $this->router->pathFor($name, $params, $query);
        }
    }

    public function getDataFromRequest($request)
    {
        $mediaType = $request->getMediaType();
        if ($mediaType == 'application/json') {
            $data = $request->getParsedBody();
            if (isset($data['data'])) {
                return $data['data'];
            } else {
                throw new AppException(
                    'Payload must be wrapped in data attribute',
                    'payloadNotWrapped', 400
                );
            }
        } elseif ($mediaType == 'application/x-www-form-urlencoded') {
            return $request->getParsedBody();
        }
    }

    public function getOptionsFromRequest($request)
    {
        $data = $request->getParsedBody();
        if (isset($data['options'])) {
            $mediaType = $request->getMediaType();
            if ($mediaType == 'application/json') {
                return $data['options'];
            } elseif ($mediaType == 'application/x-www-form-urlencoded') {
                $options = json_decode($data['options'], true);
                return is_array($options) ? $options : [];
            }
        }
        return [];
    }

    public function getFieldFromRequest($request, $field, $schema)
    {
        $data = $request->getParsedBody();
        if (!isset($data[$field])) {
            throw new AppException(
                'Field ' . $field . ' not found in payload',
                'fieldNotFound', 400
            );
        }
        $v = $this->validation->fromSchema($schema);
        $v->assert($data[$field]);
        return $data[$field];
    }

    public function getPaginatedQuerySchema(array $properties = [], $size = 25)
    {
        $properties['offset'] = [
            'type' => 'integer',
            'minimum' => 0,
            'maximum' => 10000,
            'default' => 0,
        ];
        $properties['size'] = [
            'type' => 'integer',
            'minimum' => 1,
            'maximum' => 100,
            'default' => $size,
        ];
        $schema = [
            'type' => 'object',
            'properties' => $properties,
        ];
        return $schema;
    }
}
