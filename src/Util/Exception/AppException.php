<?php

namespace App\Util\Exception;

use RuntimeException;

class AppException extends RuntimeException
{
    protected $type;

    public function __construct($message, $type = 'error', $code = 400, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }
}
