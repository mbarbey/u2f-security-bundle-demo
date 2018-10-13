<?php

namespace App\Model\Key;

use Doctrine\ORM\Mapping as ORM;

abstract class U2fKey implements U2fKeyInterface
{
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
