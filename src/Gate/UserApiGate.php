<?php

namespace App\Gate;

use App\Util\Utils;
use App\Util\ContainerClient;
use App\Util\Paginator;
use App\Util\Exception\AppException;
use App\Util\Exception\UnauthorizedException;

class UserApiGate extends AbstractApiGate
{
    protected $modelName = 'User';
    protected $modelSlug = 'usr';

    // POST /pending-users
    public function createPendingUser($request, $response, $params)
    {
        $subject = $request->getAttribute('subject');
        $data = $this->helper->getDataFromRequest($request);
        $pending = $this->resources['user']->createPendingUser($subject, $data);
        return $this->sendSimpleResponse($response, 'Pending user created');
    }

    // POST /users
    public function createUser($request, $response, $params)
    {
        $user = $this->resources['user']->createUser(
            $request->getAttribute('subject'),
            $this->helper->getDataFromRequest($request),
            $this->helper->getOptionsFromRequest($request)
        );
        return $this->sendCreatedResponse($response, $user);
    }

    // GET /users/{usr}
    public function retrieveUser($request, $response, $params)
    {
        $user = $this->resources['user']->retrieveUser(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('usr', $params),
            $request->getQueryParams()
        );
        return $this->sendEntityResponse($response, $user);
    }

    // POST /reset-tokens
    public function createResetToken($request, $response, $params)
    {
        $user = $this->resources['user']->createResetToken(
            $request->getAttribute('subject'),
            $this->helper->getDataFromRequest($request)
        );
        return $this->sendSimpleResponse($response, 'Reset token created');
    }

    // PUT /users/{usr}/password
    public function updatePassword($request, $response, $params)
    {
        $user = $this->resources['user']->updatePassword(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('usr', $params),
            $this->helper->getDataFromRequest($request),
            $this->helper->getOptionsFromRequest($request)
        );
        return $this->sendSimpleResponse($response, 'Password updated');
    }

    // POST /subjects/{sub}/role/{rol}
    public function attachRole($request, $response, $params)
    {
        $attached = $this->resources['user']->attachTerms(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('sub', $params),
            Utils::sanitizedStrParam('rol', $params),
            $this->helper->getDataFromRequest($request)
        );
        return $this->sendSimpleResponse($response, 'Role attached', 200, [
            'attached' => $attached
        ]);
    }

    // GET /subjects
    public function retrieveSubjects($request, $response, $params)
    {
        $subject = $request->getAttribute('subject');
        $subjs = $this->resources['user']->retrieveSubjects(
            $subject, $request->getQueryParams()
        );
        return $this->sendPaginatedResponse($request, $response, $subjs);
    }
}
