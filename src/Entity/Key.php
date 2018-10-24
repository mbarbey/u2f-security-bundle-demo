<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mbarbey\U2fSecurityBundle\Model\Key\U2fKey;

/**
 * @ORM\Table(name="app_u2fKeys")
 * @ORM\Entity(repositoryClass="App\Repository\KeyRepository")
 */
class Key extends U2fKey
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\user", inversedBy="u2fKeys")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @return int|NULL
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|NULL
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
