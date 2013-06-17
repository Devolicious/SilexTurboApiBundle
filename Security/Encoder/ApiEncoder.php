<?php

namespace Devolicious\SilexTurboApiBundle\Security\Encoder;

/**
* @author ruud
*/
class ApiEncoder
{
    private $salt;

    public function hash($username, $apiKey)
    {
        $today = new \DateTime();

        return md5(strtolower($username).$today->format('d-m-Y').$apiKey);
    }

    public function isHashValid($hash, $username, $apiKey)
    {
        if ($hash == $this->hash($username, $apiKey)) {
            return true;
        }

        return false;
    }
}