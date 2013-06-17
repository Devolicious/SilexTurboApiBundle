<?php

namespace Devolicious\SilexTurboApiBundle\Security\Provider;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\DBAL\Connection;
use Devolicious\SilexTurboApiBundle\Entity\User;

class UserProvider implements UserProviderInterface
{
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function loadUserByUsername($username)
    {
        $query = <<<EOQ
SELECT u.*, group_concat(r.ROLE SEPARATOR ',') as ROLES
FROM USERS u,
    USER_ROLES ur,
    ROLES r
WHERE u.id = ur.user_id
    and r.id = ur.role_id
    and u.login = ?
GROUP BY u.id
EOQ;
        $stmt = $this->conn->executeQuery($query, array(strtolower($username)));

        if (!$data = $stmt->fetch()) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        $user = new User();
        $user->setUsername($data['LOGIN']);
        $user->setPassword($data['PASSWORD']);
        $user->setRoles(explode(',', $data['ROLES']));

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }
}