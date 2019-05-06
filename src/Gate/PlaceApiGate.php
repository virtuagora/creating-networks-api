<?php

namespace App\Gate;

use App\Util\ContainerClient;
use App\Util\Paginator;
use App\Util\Exception\AppException;
use App\Util\Exception\UnauthorizedException;
use Grimzy\LaravelMysqlSpatial\Types\Point;

class PlaceApiGate extends AbstractApiGate
{
    // POST /place
    public function postOnePlace($request, $response, $params)
    {
        $subject = $request->getAttribute('subject');
        $data = $this->helper->getDataFromRequest($request);
        $place = $this->resources['place']->createOne($subject, $data);
        return $this->sendCreatedResponse($response, $place);
    }

    // GET /place
    public function getMultiPlace($request, $response, $params)
    {
        $subject = $request->getAttribute('subject');
        $places = $this->resources['place']->retrieveMulti(
            $subject, $request->getQueryParams()
        );
        return $this->sendPaginatedResponse($request, $response, $places);
    }

    // GET /place/{pla}
    public function getOnePlace($request, $response, $params)
    {
        $subject = $request->getAttribute('subject');
        $plaId = $this->helper->getSanitizedId('pla', $params);
        $place = $this->resources['place']->retrieveOne(
            $subject, $plaId, $request->getQueryParams()
        );
        return $this->sendEntityResponse($response, $place);
    }

    // POST /place/{pla}/vote
    public function postOneVote($request, $response, $params)
    {
        $subject = $request->getAttribute('subject');
        $plaId = $this->helper->getSanitizedId('pla', $params);
        $data = $this->helper->getDataFromRequest($request);
        $vote = $this->resources['place']->createOneVote(
            $subject, $plaId, $data
        );
        return $this->sendSimpleResponse($response, 'Place voted', 201);
    }
}
