<?php

namespace App\Auth;

interface SubjectInterface
{
    public function toArray();
    
    public function rolesList();
}
