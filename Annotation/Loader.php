<?php

namespace Devolicious\SilexTurboApiBundle\Annotation;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Collections\ArrayCollection;


/**
* @author ruud
*/
class Loader
{
    private $directories;

    public function __construct()
    {
        $this->directories = new ArrayCollection();
    }

    public function setDirectory($namespace, $path)
    {
        $this->directories->set($namespace, $path);
    }

    public function getDirectories()
    {
        return $this->directories;
    }
    
    public function registerAnnotations()
    {
        foreach ($this->directories as $namespace => $path) {
            AnnotationRegistry::registerAutoloadNamespace($namespace, $path);
        }
    }
}