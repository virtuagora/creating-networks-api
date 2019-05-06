<?php

namespace App\Service;

use Firebase\JWT\JWT;

class JwtService
{
    protected $priKey;
    protected $pubKey;
    protected $alg;
    protected $iss;
    protected $ttl;

    public function __construct($settings)
    {
        $this->ttl = $settings['ttl'];
        $this->alg = $settings['alg'];
        $this->iss = $settings['iss'];
        if (in_array($this->alg, ['RS256', 'RS384', 'RS512'])) {
            if ($settings['storedKey']) {
                $this->priKey = openssl_get_privatekey($settings['priKey']);
                $this->pubKey = openssl_get_publickey($settings['pubKey']);
            } else {
                $this->priKey = $settings['priKey'];
                $this->pubKey = $settings['pubKey'];
            }
        } else {
            $this->priKey = $this->pubKey = $settings['secret'];
        }
    }

    public function encode($claims)
    {
        return JWT::encode($claims, $this->priKey, $this->alg);
    }

    public function decode($jwt)
    {
        return (array) JWT::decode($jwt, $this->pubKey, [$this->alg]);
    }
    
    public function createToken($claims)
    {
        $claims['iss'] = $this->iss;
        $today = time();
        $claims['iat'] = $today;
        $claims['nbf'] = $today;
        $claims['exp'] = $today + $this->ttl;
        return $this->encode($claims);
    }

    public function getClaim($jwt, $claim)
    {
        return $this->decode($jwt)[$claim];
    }
}
