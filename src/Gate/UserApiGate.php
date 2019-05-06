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
        $subject = $request->getAttribute('subject');
        $token = $this->helper->getFieldFromRequest($request, 'token', [
            'type' => 'string',
            'minLength' => 10,
            'maxLength' => 100,
        ]);
        $data = $this->helper->getDataFromRequest($request);
        $user = $this->resources['user']->createUser($subject, $data, $token);
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
}
