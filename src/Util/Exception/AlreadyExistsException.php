<?php

namespace App\Util\Exception;

class AlreadyExistsException extends AppException
{
    public function __construct($fields)
    {
        parent::__construct(
            'Ya existe una entidad con los siguientes campos: '.implode(', ',$fields),
            'entityAlreadyExists',
            400
        );
    }
}
