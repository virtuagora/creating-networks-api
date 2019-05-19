<?php

namespace App\Service;

class ErrorHandlerService
{
    protected $logger;
    protected $exceptions;
    protected $env;
    
    public function __construct(array $exceptions, $env = 'pro', $logger = null)
    {
        $this->env = $env;
        $this->logger = $logger;
        $this->exceptions = $exceptions;
    }

    public function __invoke($request, $response, $exception)
    {
        if (isset($this->logger)) {
            $this->logger->error(
                $exception->getMessage().
                ' ['.$exception->getFile().' - '.$exception->getLine().']'
            );
        }
        $errorData = [
            'message' => 'Internal error',
            'code' => 'error',
            'status' => 500,
        ];
        foreach ($this->exceptions as $trigger => $handler) {
            if ($exception instanceof $trigger) {
                $errorData = array_merge($errorData, call_user_func($handler, $exception));
                break;
            }
        }
        if ($this->env != 'pro') {
            $errorData['trace'] = $exception->getTraceAsString();
        }
        return $response->withStatus($errorData['status'])->withJSON($errorData);
    }
}
