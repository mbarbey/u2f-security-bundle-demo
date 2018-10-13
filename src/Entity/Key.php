<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Model\Key\U2fKey;

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
    private $user;

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
     * @return user|NULL
     */
    public function getUser(): ?user
    {
        return $this->user;
    }

    /**
     * @param user $user
     * @return self
     */
    public function setUser(?user $user): self
    {
        $this->user = $user;

        return $this;
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
