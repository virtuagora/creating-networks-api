<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Util\Exception\RecaptchaException;
use ReCaptcha\ReCaptcha;

class RecaptchaMiddleware
{
    protected $settings;

    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        $payload = $request->getParsedBody();
        if (isset($payload[$this->settings['fieldname']])) {
            $recaptchaToken = $payload[$this->settings['fieldname']];
        } else {
            throw new RecaptchaException();
        }
        $recaptcha = new ReCaptcha($this->settings['secret']);
        if (isset($this->settings['hostname'])) {
            $recaptcha->setExpectedHostname($this->settings['hostname']);
        }
        $recaptchaResp = $recaptcha->verify($recaptchaToken);
        if ($recaptchaResp->isSuccess()) {
            return $next($request, $response);
        } else {
            throw new RecaptchaException($recaptchaResp->getErrorCodes());
        }
    }
}
