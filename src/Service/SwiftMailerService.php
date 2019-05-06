<?php

namespace App\Service;

class SwiftMailerService
{
    private $mailer;
    
    public function __construct($transport, $options)
    {
        if ($transport == 'null') {
            $transport = new \Swift_NullTransport();
        } elseif ($transport == 'smtp') {
            $transport = new \Swift_SmtpTransport($options['host'], $options['port']);
            $transport->setUsername($options['username']);
            $transport->setPassword($options['password']);
        } elseif ($transport == 'sendmail') {
            $transport = new \Swift_SendmailTransport($options['command']);
        } elseif ($transport == 'mail') {
            $transport = new \Swift_MailTransport();
        }
        $this->mailer = new \Swift_Mailer($transport);
    }

    public function send($message)
    {
        return $this->mailer->send($message);
    }

    public function sendMail($subject, $to, $body)
    {
        $message = new \Swift_Message($subject);
        $message->setTo($to);
        if (is_array($body)) {
            $first = true;
            foreach ($body as $part) {
                if ($first) {
                    $message->setBody($part);
                    $first = false;
                } else {
                    $message->addPart($part);
                }
            }
        } else {
            $message->setBody($body);
        }
        return $this->send($message);
    }
    
    public function getMailer()
    {
        return $this->mailer;
    }
}