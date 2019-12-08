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

    // GET /initiatives/{ini}/members
    public function retrieveMembers($request, $response, $params)
    {
        $users = $this->resources['initiative']->retrieveMembers(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('ini', $params),
            $request->getQueryParams()
        );
        return $this->sendPaginatedResponse($request, $response, $users);
    }

    // PATCH /initiatives/{ini}
    public function updateInitiative($request, $response, $params)
    {
        $init = $this->resources['initiative']->updateInitiative(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('ini', $params),
            $this->helper->getDataFromRequest($request)
        );
        return $this->sendSimpleResponse(
            $response,'Initiative updated', 200
        );
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

    // DELETE /initiatives/{ini}
    public function deleteInitiative($request, $response, $params)
    {
        $deleted = $this->resources['initiative']->deleteInitiative(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('ini', $params)
        );
        return $this->sendSimpleResponse(
            $response,'Initiative deleted', 200, ['deleted' => $deleted]
        );
    }

    // POST /initiatives/{ini}/city
    public function attachCity($request, $response, $params)
    {
        $attached = $this->resources['initiative']->attachCity(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('ini', $params),
            $this->helper->getDataFromRequest($request)
        );
        return $this->sendSimpleResponse($response, 'City attached', 200, [
            'city' => $attached
        ]);
    }

    // DELETE /initiatives/{ini}/city
    public function detachCity($request, $response, $params)
    {
        $detached = $this->resources['initiative']->detachCity(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('ini', $params)
        );
        return $this->sendSimpleResponse($response, 'City detached', 200, [
            'detached' => $detached
        ]);
    }

    // POST /initiatives/{ini}/terms
    public function attachTerms($request, $response, $params)
    {
        $attached = $this->resources['initiative']->attachTerms(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('ini', $params),
            $this->helper->getDataFromRequest($request)
        );
        return $this->sendSimpleResponse($response, 'Terms attached', 200, [
            'attached_terms' => $attached
        ]);
    }

    // DELETE /initiatives/{ini}/terms/{trm}
    public function detachTerm($request, $response, $params)
    {
        $detached = $this->resources['initiative']->detachTerm(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('ini', $params),
            Utils::sanitizedIdParam('trm', $params)
        );
        return $this->sendSimpleResponse($response, 'Term detached', 200, [
            'detached' => $detached
        ]);
    }

    // POST /initiatives/{ini}/countries
    public function attachCountries($request, $response, $params)
    {
        $attached = $this->resources['initiative']->attachCountries(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('ini', $params),
            $this->helper->getDataFromRequest($request)
        );
        return $this->sendSimpleResponse($response, 'Countries attached', 200, [
            'attached_countries' => $attached
        ]);
    }

    // DELETE /initiatives/{ini}/countries/{cou}
    public function detachCountry($request, $response, $params)
    {
        $detached = $this->resources['initiative']->detachCountry(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('ini', $params),
            Utils::sanitizedIdParam('cou', $params)
        );
        return $this->sendSimpleResponse($response, 'Country detached', 200, [
            'detached' => $detached
        ]);
    }

    // POST /initiatives/{ini}/members/{usr}
    public function addMember($request, $response, $params)
    {
        $result = $this->resources['initiative']->addMember(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('ini', $params),
            Utils::sanitizedIdParam('usr', $params)
        );
        return $this->sendSimpleResponse($response, 'Member added', 200, [
            'added' => $result
        ]);
    }

    // DELETE /initiatives/{ini}/members/{usr}
    public function removeMember($request, $response, $params)
    {
        $result = $this->resources['initiative']->removeMember(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('ini', $params),
            Utils::sanitizedIdParam('usr', $params)
        );
        return $this->sendSimpleResponse($response, 'Member removed', 200, [
            'removed' => $result
        ]);
    }

    // PATCH /initiatives/{ini}/members/{usr}
    public function updateMember($request, $response, $params)
    {
        $result = $this->resources['initiative']->updateMember(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('ini', $params),
            Utils::sanitizedIdParam('usr', $params),
            $this->helper->getDataFromRequest($request)
        );
        return $this->sendSimpleResponse($response, 'Member updated', 200, [
            'updated' => $result
        ]);
    }

    // PUT /initiatives/{ini}/pictures/{pic}
    public function updatePicture($request, $response, $params)
    {
        $pics = $this->resources['initiative']->updatePicture(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('ini', $params),
            Utils::sanitizedStrParam('pic', $params),
            $request->getBody()
        );
        return $this->sendSimpleResponse($response, 'Picture updated', 200, [
            'pictures' => $pics
        ]);
    }

    // DELETE /initiatives/{ini}/pictures/{pic}
    public function deletePicture($request, $response, $params)
    {
        $deleted = $this->resources['initiative']->deletePicture(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('ini', $params),
            Utils::sanitizedStrParam('pic', $params)
        );
        return $this->sendSimpleResponse($response, 'Picture deleted', 200, [
            'deleted' => $deleted
        ]);
    }
}
