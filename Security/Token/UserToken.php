<?php

namespace Devolicious\SilexTurboApiBundle\Security\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class UserToken extends AbstractToken
{
    private $apiKey;
    private $hashedApiKey;
    private $apiType;
    private $apiUsername;

    public function __construct(array $roles = array())
    {
        parent::__construct($roles);
        $this->setAuthenticated(count($roles) > 0);
    }

    public function getCredentials()
    {
        return '';
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getApiType()
    {
        return $this->apiType;
    }

    public function setApiType($apiType)
    {
        $this->apiType = $apiType;
    }

    public function getApiUsername()
    {
        return $this->apiUsername;
    }

    public function setApiUsername($apiUsername)
    {
        $this->apiUsername = $apiUsername;
    }

    public function getHashedApiKey()
    {
        return $this->hashedApiKey;
    }

    public function setHashedApiKey($hashedApiKey)
    {
        $this->hashedApiKey = $hashedApiKey;
    }

}