<?php

namespace App\Model\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Model\Key\U2fKeyInterface;

class U2fUser implements U2fUserInterface
{
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Key", mappedBy="user", orphanRemoval=true)
     */
    protected $u2fKeys;

    public function __construct()
    {
        $this->u2fKeys = new ArrayCollection();
    }

    /**
     * @return Collection|U2fKeyInterface[]
     */
    public function getU2fKeys(): Collection
    {
        return $this->u2fKeys;
    }

    /**
     * @param U2fKeyInterface $u2fKey
     * @return self
     */
    public function addU2fKey(U2fKeyInterface $u2fKey): self
    {
        if (!$this->u2fKeys->contains($u2fKey)) {
            $this->u2fKeys[] = $u2fKey;
            $u2fKey->setUser($this);
        }

        return $this;
    }

    /**
     * @param U2fKeyInterface $u2fKey
     * @return self
     */
    public function removeU2fKey(U2fKeyInterface $u2fKey): self
    {
        if ($this->u2fKeys->contains($u2fKey)) {
            $this->u2fKeys->removeElement($u2fKey);
            // set the owning side to null (unless already changed)
            if ($u2fKey->getUser() === $this) {
                $u2fKey->setUser(null);
            }
        }

        return $this;
    }
}
