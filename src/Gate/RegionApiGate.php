<?php

namespace App\Gate;

use App\Util\Utils;
use App\Util\ContainerClient;

class RegionApiGate extends AbstractApiGate
{
    protected $modelName = 'Region';
    protected $modelSlug = 'reg';

    // GET /regions
    public function retrieveRegions($request, $response, $params)
    {
        $subject = $request->getAttribute('subject');
        $regions = $this->resources['region']->retrieveRegions(
            $subject, $request->getQueryParams()
        );
        return $this->sendPaginatedResponse($request, $response, $regions);
    }

    // GET /regions/{reg}
    public function retrieveRegion($request, $response, $params)
    {
        $region = $this->resources['region']->retrieveRegion(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('reg', $params),
            $request->getQueryParams()
        );
        return $this->sendEntityResponse($response, $region);
    }
}
