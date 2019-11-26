<?php

namespace App\Gate;

use ReflectionClass;
use App\Util\ContainerClient;
use App\Util\Exception\AppException;
use Illuminate\Support\Str;

abstract class AbstractApiGate extends ContainerClient
{
    protected $modelName;
    protected $modelSlug;

    protected function sendCreatedResponse($response, $entity, $model = null)
    {
        if (isset($model)) {
            $modelName = $model;
            $modelSlug = strtolower(substr($model, 0, 3));
        } else {
            $modelName = $this->modelName;
            $modelSlug = $this->modelSlug;
        }
        $entityUri = $this->helper->pathFor('apiR1' . $modelName, [
            $modelSlug => $entity->id,
        ]);
        return $response->withStatus(201)->withJSON([
            Str::snake($modelName) => $entity->toArray(),
        ])->withHeader('Location', $entityUri);
    }

    protected function sendPaginatedResponse($request, $response, $results)
    {
        $results->setUri($request->getUri());
        return $response->withJSON($results->toArray());
    }

    protected function sendEntityResponse($response, $entity)
    {
        $data = [
            'data' => $entity->toArray(),
        ];
        if ($entity->hasContext()) {
            $data['context'] = $entity->getContext();
        }
        return $response->withJSON($data);
    }

    protected function sendSimpleResponse($response, $message, $status = 200, $fields = [])
    {
        $fields['message'] = $message;
        return $response->withStatus($status)->withJSON($fields);
    }

}