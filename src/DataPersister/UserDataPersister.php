<?php

declare(strict_types=1);

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserDataPersister implements DataPersisterInterface
{
    private DataPersisterInterface $decoratedPersister;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        DataPersisterInterface $decoratedPersister,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->decoratedPersister = $decoratedPersister;
        $this->passwordHasher = $passwordHasher;
    }
    public function supports($data): bool
    {
        return $data instanceof User && $this->decoratedPersister->supports($data);
    }

    /**
     * @param User $data
     */
    public function persist($data)
    {
        if (null !== $data->getPlainPassword() && '' !== $data->getPlainPassword()) {
            $data->setPassword($this->passwordHasher->hashPassword($data, $data->getPlainPassword()));
            $data->eraseCredentials();
        }

        $this->decoratedPersister->persist($data);
    }

    public function remove($data)
    {
        $this->decoratedPersister->remove($data);
    }
}
