<?php

namespace App\Gate;

use App\Util\Utils;
use App\Util\ContainerClient;

class CountryApiGate extends AbstractApiGate
{
    protected $modelName = 'Country';
    protected $modelSlug = 'cou';

    // GET /countries
    public function retrieveCountries($request, $response, $params)
    {
        $subject = $request->getAttribute('subject');
        $contries = $this->resources['country']->retrieveCountries(
            $subject, $request->getQueryParams()
        );
        return $this->sendPaginatedResponse($request, $response, $contries);
    }

    // GET /countries/{cou}
    public function retrieveCountry($request, $response, $params)
    {
        $country = $this->resources['country']->retrieveCountry(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('cou', $params),
            $request->getQueryParams()
        );
        return $this->sendEntityResponse($response, $country);
    }
}
