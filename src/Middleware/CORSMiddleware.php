<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CORSMiddleware
{
    protected $options;
    protected $handler;

    public function __construct($options, $handler = null)
    {
        $this->options = array_merge([
            'origin' => ['*'],
            'methods' => ['GET', 'POST'],
            'headers.allow' => ['Authorization', 'Content-Type'],
        ], $options);
        $this->handler = $handler;
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        if ($request->hasHeader('Origin')) {
            $origin = $request->getHeaderLine('Origin');
            if (in_array($origin, $this->options['origin'])) {
                $response = $response
                    ->withHeader('Access-Control-Allow-Origin', $origin)
                    ->withHeader('Access-Control-Allow-Credentials', 'true');
                if ($request->isOptions()) {
                    return $response
                        ->withHeader('Access-Control-Allow-Headers', implode(', ', $this->options['headers.allow']))
                        ->withHeader('Access-Control-Allow-Methods', implode(', ', $this->options['methods']));
                } elseif (isset($this->handler)) {
                    $this->handler->setResponse($response);
                }
            } else {
                return $response->withStatus(401);
            }
        }
        return $next($request, $response);
    }
}
