<?php

namespace App\Auth;

interface ObjectInterface
{
    public function relationsWith(SubjectInterface $subject);
}
