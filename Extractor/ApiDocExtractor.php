<?php

namespace Devolicious\SilexTurboApiBundle\Extractor;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Nelmio\ApiDocBundle\Util\DocCommentExtractor;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


/**
* @author ruud
*/
class ApiDocExtractor
{
    private $container;
    private $docs = array();
    private $commentExtractor;
    private $secureAnnotation = 'Devolicious\\SilexTurboApiBundle\\Annotation\\Secure';
    private $apidocAnnotation = 'Nelmio\\ApiDocBundle\\Annotation\\ApiDoc';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->commentExtractor = new DocCommentExtractor();
    }

    public function getData($name, RouteCollection $collection)
    {
        $date = new \DateTime();
        $data = array(
            'apiName' => $name,
            'docs' => $this->all($collection),
            'css' => file_get_contents($this->container->getParameter('nelmio.assets.css')),
            'js' => file_get_contents($this->container->getParameter('nelmio.assets.js')),
            'date' => $date->format('Y/m/d H:i:s'),
            'displayContent' => false
        );

        return $data;
    }

    public function all(RouteCollection $collection)
    {
        $routes = $collection->all();
        foreach ($routes as $route) {
            if (!$route instanceof Route) {
                throw new \InvalidArgumentException(sprintf('All elements of $routes must be instances of Route. "%s" given', gettype($route)));
            }

            list($class, $method) = explode(':', $route->getDefault('_controller'));
            if ($this->container->has($class)) {
                $class = get_class($this->container->get($class));
                $refl = new \ReflectionMethod($class, $method . 'Action');
                $reader = new AnnotationReader();
                $apidoc = $reader->getMethodAnnotation($refl, $this->apidocAnnotation);

                if (!$apidoc instanceof $this->apidocAnnotation) {
                    continue;
                }

                $apidoc = $this->extractData($apidoc, $route, $refl);
                $apidoc->setRoute($route);
                
                $apidoc->setDocumentation($this->commentExtractor->getDocCommentText($refl));


                $secure = $reader->getMethodAnnotation($refl, $this->secureAnnotation);
                if ($secure instanceof $this->secureAnnotation) {
                    $apidoc->setAuthentication(true);
                }

                $this->docs[] = $apidoc;
            }
        }
        
        return $this->docs;
    }

    public function normalizeData($arr)
    {
        $data = array(
            'name'   => $arr['name'] ?: '',
            'dataType'      => $arr['dataType'] ?: '',
            'required'   => $arr['required'] ?: false,
            'description'   => $arr['description'] ?: ''
        );

        return $data;
    }

    public function extractData(ApiDoc $annotation, Route $route, \ReflectionMethod $method)
    {
        if (null !== $input = $annotation->getInput()) {
            $input = array_map(array($this, "normalizeData"), $input);
            $annotation->setParameters($input);
        }

        if (null !== $output = $annotation->getOutput()) {
            $output = array_map(array($this, "normalizeData"), $output);
            $annotation->setResponse($output);
        }

        // requirements
        $requirements = array();
        foreach ($route->getRequirements() as $name => $value) {
            if ('_method' !== $name) {
                $requirements[$name] = array(
                    'requirement'   => $value,
                    'dataType'      => '',
                    'description'   => '',
                );
            }
            if ('_scheme' == $name) {
                $https = ('https' == $value);
                $annotation->setHttps($https);
            }
        }

        $paramDocs = array();
        foreach (explode("\n", $this->commentExtractor->getDocComment($method)) as $line) {
            if (preg_match('{^@param (.+)}', trim($line), $matches)) {
                $paramDocs[] = $matches[1];
            }
            if (preg_match('{^@deprecated\b(.*)}', trim($line), $matches)) {
                $annotation->setDeprecated(true);
            }
        }

        $regexp = '{(\w*) *\$%s\b *(.*)}i';
        foreach ($route->compile()->getVariables() as $var) {
            $found = false;
            foreach ($paramDocs as $paramDoc) {
                if (preg_match(sprintf($regexp, preg_quote($var)), $paramDoc, $matches)) {
                    $requirements[$var]['dataType']    = isset($matches[1]) ? $matches[1] : '';
                    $requirements[$var]['description'] = $matches[2];

                    if (!isset($requirements[$var]['requirement'])) {
                        $requirements[$var]['requirement'] = '';
                    }

                    $found = true;
                    break;
                }
            }

            if (!isset($requirements[$var]) && false === $found) {
                $requirements[$var] = array('requirement' => '', 'dataType' => '', 'description' => '');
            }
        }

        $annotation->setRequirements($requirements);

        return $annotation;
    }

    public function registerNelmioApiDocDir($rootBundleDir)
    {
        AnnotationRegistry::registerAutoloadNamespace("Nelmio", $rootBundleDir);
    }
}