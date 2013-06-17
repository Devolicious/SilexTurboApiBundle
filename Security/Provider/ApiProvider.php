<?php

namespace Devolicious\SilexTurboApiBundle\Security\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Devolicious\SilexTurboApiBundle\Security\Token\UserToken;

class ApiProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $encoder;

    public function __construct(UserProviderInterface $userProvider, $encoder)
    {
        $this->userProvider = $userProvider;
        $this->encoder      = $encoder;
    }

    public function authenticate(TokenInterface $token)
    {
        $user = $this->userProvider->loadUserByUsername($token->getApiUsername());

        if ($user != null) {

            if (!$this->encoder->isHashValid($token->getHashedApiKey(), $token->getApiUsername(), $user->getPassword())) {
                throw new AuthenticationException(sprintf('Invalid hashed api key or username (%s:%s)', $token->getApiUsername(), $token->getHashedApiKey()));
            }

            $authenticatedToken = new UserToken($user->getRoles());
            $authenticatedToken->setApiKey($user->getPassword());
            $authenticatedToken->setApiType($token->getApiType());
            $authenticatedToken->setHashedApiKey($token->getHashedApiKey());
            $authenticatedToken->setApiUsername($token->getApiUsername());
            $authenticatedToken->setUser($user);

            return $authenticatedToken;
        }

        throw new AuthenticationException(sprintf('Invalid hashed api key or username (%s:%s)', $token->getApiUsername(), $token->getHashedApiKey()));
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof UserToken;
    }
}