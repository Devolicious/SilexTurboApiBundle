<?php

namespace Devolicious\SilexTurboApiBundle\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 * @author ruud
 */
class Secure
{
    
    private $roles;

    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $values['roles'] = $values['value'];
        }
        if (!isset($values['roles'])) {
            throw new InvalidArgumentException('You must define a "roles" attribute for each Secure annotation.');
        }

        $this->roles = is_array($values['roles']) ? $values['roles'] : array_map('trim', explode(',', $values['roles']));
    }

    public function getRoles()
    {
        return $this->roles;
    }
}