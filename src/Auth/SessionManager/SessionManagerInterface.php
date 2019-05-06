<?php

namespace App\Auth\SessionManager;

use App\Auth\SubjectInterface as Subject;
use Psr\Http\Message\ServerRequestInterface as Request;

interface SessionManagerInterface
{
    public function authenticate(Request $request);

    public function signIn(Subject $subject);

    public function signOut();
}
