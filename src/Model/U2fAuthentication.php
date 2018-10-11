<?php


namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class U2fAuthentication
{
    /**
     * @Assert\NotBlank()
     */
    private $response;

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }
}
