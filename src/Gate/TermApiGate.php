<?php

namespace App\Gate;

use App\Util\Utils;
use App\Util\ContainerClient;
use App\Util\Paginator;
use App\Util\Exception\AppException;
use App\Util\Exception\UnauthorizedException;

class TermApiGate extends AbstractApiGate
{
    protected $modelName = 'Term';
    protected $modelSlug = 'trm';

    // POST /terms
    public function createTerm($request, $response, $params)
    {
        $term = $this->resources['term']->createTerm(
            $request->getAttribute('subject'),
            $this->helper->getDataFromRequest($request),
            $this->helper->getOptionsFromRequest($request)
        );
        return $this->sendCreatedResponse($response, $term);
    }

    // GET /terms
    public function retrieveTerms($request, $response, $params)
    {
        $subject = $request->getAttribute('subject');
        $terms = $this->resources['term']->retrieveTerms(
            $subject, $request->getQueryParams()
        );
        return $this->sendPaginatedResponse($request, $response, $terms);
    }

    // GET /terms/{trm}
    public function retrieveTerm($request, $response, $params)
    {
        $term = $this->resources['term']->retrieveTerm(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('trm', $params),
            $request->getQueryParams()
        );
        return $this->sendEntityResponse($response, $term);
    }

    // DELETE /terms/{trm}
    public function deleteTerm($request, $response, $params)
    {
        $deleted = $this->resources['term']->deleteTerm(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('trm', $params)
        );
        return $this->sendSimpleResponse(
            $response,'Term deleted', 200, ['deleted' => $deleted]
        );
    }
}
