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

    // PATCH /users/{usr}
    public function updateUser($request, $response, $params)
    {
        $user = $this->resources['user']->updateUser(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('usr', $params),
            $this->helper->getDataFromRequest($request)
        );
        return $this->sendSimpleResponse(
            $response,'User updated', 200
        );
    }

    // GET /users/{usr}/groups
    public function retrieveGroups($request, $response, $params)
    {
        $groups = $this->resources['user']->retrieveGroups(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('usr', $params),
            $request->getQueryParams()
        );
        return $this->sendPaginatedResponse($request, $response, $groups);
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

    // POST /subjects/{sub}/roles/{rol}
    public function attachRole($request, $response, $params)
    {
        $attached = $this->resources['user']->attachRole(
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

    // POST /users/{usr}/groups/{gro}
    public function attachGroup($request, $response, $params)
    {
        $attached = $this->resources['user']->attachGroup(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('usr', $params),
            Utils::sanitizedStrParam('gro', $params),
            $this->helper->getDataFromRequest($request)
        );
        return $this->sendSimpleResponse($response, 'Group attached', 200, [
            'attached' => $attached
        ]);
    }

    // DELETE /users/{usr}/groups/{gro}
    public function detachGroup($request, $response, $params)
    {
        $detached = $this->resources['user']->detachGroup(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('usr', $params),
            Utils::sanitizedIdParam('gro', $params)
        );
        return $this->sendSimpleResponse($response, 'Group detached', 200, [
            'detached' => $detached
        ]);
    }

    // POST /users/{usr}/terms
    public function attachTerms($request, $response, $params)
    {
        $attached = $this->resources['user']->attachTerms(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('usr', $params),
            $this->helper->getDataFromRequest($request)
        );
        return $this->sendSimpleResponse($response, 'Terms attached', 200, [
            'attached_terms' => $attached
        ]);
    }

    // DELETE /users/{usr}/terms/{trm}
    public function detachTerm($request, $response, $params)
    {
        $detached = $this->resources['user']->detachTerm(
            $request->getAttribute('subject'),
            Utils::sanitizedIdParam('usr', $params),
            Utils::sanitizedIdParam('trm', $params)
        );
        return $this->sendSimpleResponse($response, 'Term detached', 200, [
            'detached' => $detached
        ]);
    }

    // POST /subjects/{sub}/picture
    public function updatePicture($request, $response, $params)
    {
        $updated = $this->resources['user']->updatePicture(
            $request->getAttribute('subject'),
            $request->getBody()
        );
        return $this->sendSimpleResponse($response, 'Picture updated', 200, [
            'updated' => $updated
        ]);
    }

    // DELETE /subjects/{sub}/picture
    public function deletedPicture($request, $response, $params)
    {
        $deleted = $this->resources['user']->deletePicture(
            $request->getAttribute('subject')
        );
        return $this->sendSimpleResponse($response, 'Picture deleted', 200, [
            'deleted' => $deleted
        ]);
    }
}
