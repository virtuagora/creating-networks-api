<?php

namespace App\Gate;

use App\Util\ContainerClient;
use App\Util\Exception\AppException;

class SessionApiGate extends ContainerClient
{
    // POST /token
    public function createSession($request, $response, $params)
    {
        $credentialsSupplied = isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']);
        if (!$credentialsSupplied) {
            throw new AppException('Credenciales de acceso no ingresadas');
        }
        $result = $this->identity->signIn('local', [
            'email' => $_SERVER['PHP_AUTH_USER'],
            'password' => $_SERVER['PHP_AUTH_PW'],
        ]);
        if ($result['status'] == 'success') {
            $user = $result['user'];
            $session = $this->session->signIn($user->subject->toDummy());
            return $response->withJSON([
                'token_type' => 'bearer',
                'access_token' => $session['token'],
                'expiration' => $this->jwt->getClaim($session['token'], 'exp'),
                'user' => $user->toArray(),
            ]);
        } else {
            throw new AppException($result['status'].'Credenciales de acceso incorrectas');
        }
    }
}
