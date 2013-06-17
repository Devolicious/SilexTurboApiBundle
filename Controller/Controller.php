<?php

namespace Devolicious\SilexTurboApiBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
* @author ruud
*/
abstract class Controller
{
    protected $response;

    public function __construct()
    {
        $this->response = new JsonResponse();
    }

    public function validate($validator, $obj)
    {
        $errors = $validator->validate($obj);
        if (count($errors) > 0) {
            $errorsMsgs = array();
            foreach ($errors as $error) {
                $errorsMsgs[$error->getPropertyPath()] = $error->getMessage();
            }
            $this->response->setData(array('errors' => $errorsMsgs, 'code' => 400));
            $this->response->setStatusCode(400);
            return false;
        }

        return true;
    }
}
