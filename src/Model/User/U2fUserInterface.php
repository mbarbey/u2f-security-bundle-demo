<?php

namespace App\Model\User;

use Doctrine\Common\Collections\Collection;
use App\Model\Key\U2fKeyInterface;

interface U2fUserInterface
{
    /**
     * @return Collection|U2fKeyInterface[]
     */
    public function getU2fKeys(): Collection;

    /**
     * @param U2fKeyInterface $u2fKey
     * @return self
     */
    public function addU2fKey(U2fKeyInterface $u2fKey);

    /**
     * @param U2fKeyInterface $u2fKey
     * @return self
     */
    public function removeU2fKey(U2fKeyInterface $u2fKey);
}
