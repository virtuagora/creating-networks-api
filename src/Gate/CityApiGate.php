<?php

namespace App\Gate;

use App\Util\Utils;
use App\Util\ContainerClient;

class CityApiGate extends AbstractApiGate
{
    protected $modelName = 'City';
    protected $modelSlug = 'cit';

    // GET /cities
    public function retrieveCities($request, $response, $params)
    {
        $subject = $request->getAttribute('subject');
        $cities = $this->resources['city']->retrieveCities(
            $subject, $request->getQueryParams()
        );
        return $this->sendPaginatedResponse($request, $response, $cities);
    }

    // GET /cities/{cit}
    public function retrieveCity($request, $response, $params)
    {
        $city = $this->resources['city']->retrieveCity(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('cit', $params),
            $request->getQueryParams()
        );
        return $this->sendEntityResponse($response, $city);
    }

    // GET /registered-cities
    public function retrieveRegisteredCities($request, $response, $params)
    {
        $subject = $request->getAttribute('subject');
        $cities = $this->resources['city']->retrieveRegisteredCities(
            $subject, $request->getQueryParams()
        );
        return $this->sendPaginatedResponse($request, $response, $cities);
    }
}
