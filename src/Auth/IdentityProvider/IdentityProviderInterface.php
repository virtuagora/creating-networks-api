<?php

namespace App\Auth\IdentityProvider;

interface IdentityProviderInterface
{
    public function getSignInFields(array $data);

    public function retrieveSubject(array $data);

    public function createMagicToken(array $data);

    public function getSignUpFields(string $token, array $data);
}