<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="app_users")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Key", mappedBy="user", orphanRemoval=true)
     */
    private $u2fKeys;

    public function __construct()
    {
        $this->u2fKeys = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserInterface::getUsername()
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return self
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserInterface::getSalt()
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @return string|NULL
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string $password
     * @return self
     */
    public function setPlainPassword(string $password = null): self
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserInterface::getPassword()
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return self
     */
    public function setPassword(string $password = null): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserInterface::getRoles()
     */
    public function getRoles(): array
    {
        return array('ROLE_USER');
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserInterface::eraseCredentials()
     */
    public function eraseCredentials(): void
    {
    }

    /**
     * {@inheritDoc}
     * @see \Serializable::serialize()
     */
    public function serialize(): string
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
        ));
    }

    /**
     * {@inheritDoc}
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized): void
    {
        list (
            $this->id,
            $this->username,
            $this->password,
        ) = unserialize($serialized, array('allowed_classes' => false));
    }

    /**
     * @return Collection|Key[]
     */
    public function getU2fKeys(): Collection
    {
        return $this->u2fKeys;
    }

    /**
     * @param Key $u2fKey
     * @return self
     */
    public function addU2fKey(Key $u2fKey): self
    {
        if (!$this->u2fKeys->contains($u2fKey)) {
            $this->u2fKeys[] = $u2fKey;
            $u2fKey->setUser($this);
        }

        return $this;
    }

    /**
     * @param Key $u2fKey
     * @return self
     */
    public function removeU2fKey(Key $u2fKey): self
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
