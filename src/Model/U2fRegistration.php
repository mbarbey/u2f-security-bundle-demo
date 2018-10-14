<?php


namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Mbarbey\U2fSecurityBundle\Model\U2fRegistration\U2fRegistration as Base;

class U2fRegistration extends Base
{
    /**
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
