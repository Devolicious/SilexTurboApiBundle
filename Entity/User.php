<?php

namespace Devolicious\SilexTurboApiBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

/**
* @author ruud
*/
class User implements UserInterface
{
    
    private $username;
    private $password;
    private $roles;
    private $salt;

    public function eraseCredentials()
    {
        $this->username = null;
        $this->password = null;
        $this->roles = null;
        $this->salt = null;
    }

    public function setUsername($x)
    {
        $this->username = $x;
    }


    public function getUsername()
    {
        return $this->username;
    }


    public function setPassword($x)
    {
        $this->password = $x;
    }


    public function getPassword()
    {
        return $this->password;
    }


    public function setRoles($x)
    {
        $this->roles = $x;
    }


    public function getRoles()
    {
        return $this->roles;
    }


    public function setSalt($x)
    {
        $this->salt = $x;
    }


    public function getSalt()
    {
        return $this->salt;
    }

}