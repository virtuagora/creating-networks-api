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
    public function updateUserPassword($request, $response, $params)
    {
        $user = $this->resources['user']->updateUserPassword(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('usr', $params),
            $this->helper->getDataFromRequest($request),
            $this->helper->getOptionsFromRequest($request)
        );
        return $this->sendSimpleResponse($response, 'Password updated');
    }
}
