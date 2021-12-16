<?php

declare(strict_types=1);

namespace App\Doctrine;

use App\Entity\CheeseListing;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;

class CheeseListingSetOwnerListener
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function prePersist(CheeseListing $cheeseListing)
    {
        if (null !== $cheeseListing->getOwner()) {
            return;
        }

        $user = $this->security->getUser();
        if ($user instanceof User) {
            $cheeseListing->setOwner($user);
        }
    }
}
