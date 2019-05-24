<?php

namespace App\Util\Exception;

class RecaptchaException extends AppException
{
    protected $errors;

    public function __construct($errors = null)
    {
        parent::__construct(
            'There was an error with the captcha',
            isset($errors) ? 'recaptchaError' : 'recaptchaNotFound',
            400
        );
        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
