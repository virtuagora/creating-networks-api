<?php

namespace App\Gate;

use App\Util\Utils;
use App\Util\ContainerClient;
use App\Util\Paginator;
use App\Util\Exception\AppException;
use App\Util\Exception\UnauthorizedException;

class videoApiGate extends AbstractApiGate
{
    protected $modelName = 'Video';
    protected $modelSlug = 'vid';

    // POST /videos
    public function createVideo($request, $response, $params)
    {
        $vide = $this->resources['video']->createVideo(
            $request->getAttribute('subject'),
            $this->helper->getDataFromRequest($request),
            $this->helper->getOptionsFromRequest($request)
        );
        return $this->sendCreatedResponse($response, $vide);
    }

    // GET /videos
    public function retrieveVideos($request, $response, $params)
    {
        $subject = $request->getAttribute('subject');
        $vides = $this->resources['video']->retrieveVideos(
            $subject, $request->getQueryParams()
        );
        return $this->sendPaginatedResponse($request, $response, $vides);
    }

    // GET /videos/{vid}
    public function retrieveVideo($request, $response, $params)
    {
        $vide = $this->resources['video']->retrieveVideo(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('vid', $params),
            $request->getQueryParams()
        );
        return $this->sendEntityResponse($response, $vide);
    }

    // PATCH /videos/{vid}
    public function updateVideo($request, $response, $params)
    {
        $init = $this->resources['video']->updateVideo(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('vid', $params),
            $this->helper->getDataFromRequest($request)
        );
        return $this->sendSimpleResponse(
            $response,'Video updated', 200
        );
    }

    // DELETE /videos/{vid}
    public function deleteVideo($request, $response, $params)
    {
        $deleted = $this->resources['video']->deleteVideo(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('vid', $params)
        );
        return $this->sendSimpleResponse(
            $response,'Video deleted', 200, ['deleted' => $deleted]
        );
    }
}
