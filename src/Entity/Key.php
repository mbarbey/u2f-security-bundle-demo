<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="app_u2fKeys")
 * @ORM\Entity(repositoryClass="App\Repository\KeyRepository")
 */
class Key
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
     * @ORM\Column(type="string", length=255)
     */
    public $keyHandle;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public $publicKey;

    /**
     * @ORM\Column(type="text")
     */
    public $certificate;

    /**
     * @ORM\Column(type="integer")
     */
    public $counter;

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

    /**
     * @return string|NULL
     */
    public function getKeyHandle(): ?string
    {
        return $this->keyHandle;
    }

    /**
     * @param string $keyHandle
     * @return self
     */
    public function setKeyHandle(string $keyHandle): self
    {
        $this->keyHandle = $keyHandle;

        return $this;
    }

    /**
     * @return string|NULL
     */
    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     * @return self
     */
    public function setPublicKey(string $publicKey): self
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    /**
     * @return string|NULL
     */
    public function getCertificate(): ?string
    {
        return $this->certificate;
    }

    /**
     * @param string $certificate
     * @return self
     */
    public function setCertificate(string $certificate): self
    {
        $this->certificate = $certificate;

        return $this;
    }

    /**
     * @return int|NULL
     */
    public function getCounter(): ?int
    {
        return $this->counter;
    }

    /**
     * @param int $counter
     * @return self
     */
    public function setCounter(int $counter): self
    {
        $this->counter = $counter;

        return $this;
    }
}
