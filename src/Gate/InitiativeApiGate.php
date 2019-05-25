<?php

namespace App\Gate;

use App\Util\Utils;
use App\Util\ContainerClient;
use App\Util\Paginator;
use App\Util\Exception\AppException;
use App\Util\Exception\UnauthorizedException;

class InitiativeApiGate extends AbstractApiGate
{
    protected $modelName = 'Initiative';
    protected $modelSlug = 'ini';

    // POST /initiatives
    public function createInitiative($request, $response, $params)
    {
        $init = $this->resources['initiative']->createInitiative(
            $request->getAttribute('subject'),
            $this->helper->getDataFromRequest($request),
            $this->helper->getOptionsFromRequest($request)
        );
        return $this->sendCreatedResponse($response, $init);
    }

    // GET /initiatives/{ini}
    public function retrieveInitiative($request, $response, $params)
    {
        $init = $this->resources['initiative']->retrieveInitiative(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('ini', $params),
            $request->getQueryParams()
        );
        return $this->sendEntityResponse($response, $init);
    }

    // GET /initiatives
    public function retrieveInitiatives($request, $response, $params)
    {
        $subject = $request->getAttribute('subject');
        $inits = $this->resources['initiative']->retrieveInitiatives(
            $subject, $request->getQueryParams()
        );
        return $this->sendPaginatedResponse($request, $response, $inits);
    }
}
