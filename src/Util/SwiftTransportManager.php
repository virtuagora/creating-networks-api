<?php

namespace App\Util;

use GuzzleHttp\Client as HttpClient;
use Swift_SmtpTransport as SmtpTransport;
use Illuminate\Mail\Transport\LogTransport;
use Illuminate\Mail\Transport\ArrayTransport;
use Swift_SendmailTransport as MailTransport;
use Illuminate\Mail\Transport\MailgunTransport;
use Illuminate\Mail\Transport\MandrillTransport;
use Illuminate\Mail\Transport\SparkPostTransport;
use Swift_SendmailTransport as SendmailTransport;
use Swift_NullTransport as NullTransport;

class SwiftTransportManager
{
    protected $guzzle;

    public function __construct($guzzle = null)
    {
        if (is_null($guzzle)) {
            $this->guzzle = new HttpClient();
        } else {
            $this->guzzle = $guzzle;
        }
    }

    public function getDriver($driver, $config)
    {
        $name = 'create'.ucfirst($driver).'Driver';
        if (method_exists($this, $name)) {
            return call_user_func([$this, $name], $config);
        } else {
            return $this->createNullDriver($config);
        }
    }

    public function createSmtpDriver($config)
    {
        $transport = new SmtpTransport($config['host'], $config['port']);
        if (isset($config['encryption'])) {
            $transport->setEncryption($config['encryption']);
        }
        if (isset($config['username'])) {
            $transport->setUsername($config['username']);
            $transport->setPassword($config['password']);
        }
        if (isset($config['stream'])) {
            $transport->setStreamOptions($config['stream']);
        }
        return $transport;
    }

    public function createSendmailDriver($config)
    {
        return new SendmailTransport($config);
    }

    public function createMailDriver($config)
    {
        return new MailTransport;
    }

    public function createNullDriver($config)
    {
        return new NullTransport;
    }

    public function createMailgunDriver($config)
    {
        return new MailgunTransport(
            $this->guzzle,
            $config['secret'],
            $config['domain'],
            $config['endpoint'] ?? null
        );
    }

    public function createMandrillDriver($config)
    {
        return new MandrillTransport(
            $this->guzzle, $config['secret']
        );
    }

    public function createSparkPostDriver($config)
    {
        return new SparkPostTransport(
            $this->guzzle, $config['secret'], $config['options'] ?? []
        );
    }

    // public function createLogDriver($config)
    // {
    //     return new LogTransport($this->app->make(LoggerInterface::class));
    // }

    public function createArrayDriver($config)
    {
        return new ArrayTransport;
    }   
}
