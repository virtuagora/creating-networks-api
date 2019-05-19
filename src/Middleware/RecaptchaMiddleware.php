<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Util\Exception\RecaptchaException;
use ReCaptcha\ReCaptcha;

class RecaptchaMiddleware
{
    protected $settings;
    protected $env;

    public function __construct($c)
    {
        $this->settings = $c->get('settings')['recaptcha'];
        $this->env = $c->get('settings')['env'];
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        if ($this->env == 'dev') {
            return $next($request, $response);
        }
        $payload = $request->getParsedBody();
        if (!isset($payload[$this->settings['fieldname']])) {
            throw new RecaptchaException();
        }
        $recaptchaToken = $payload[$this->settings['fieldname']];
        $recaptcha = new ReCaptcha($this->settings['secret']);
        if (isset($this->settings['hostname'])) {
            $recaptcha->setExpectedHostname($this->settings['hostname']);
        }
        $recaptchaResp = $recaptcha->verify($recaptchaToken);
        if (!$recaptchaResp->isSuccess()) {
            throw new RecaptchaException($recaptchaResp->getErrorCodes());
        }
        return $next($request, $response);
    }
}
