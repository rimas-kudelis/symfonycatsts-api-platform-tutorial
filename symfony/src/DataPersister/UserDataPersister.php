<?php

declare(strict_types=1);

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;

class UserDataPersister implements ContextAwareDataPersisterInterface
{
    private DataPersisterInterface $decoratedPersister;
    private UserPasswordHasherInterface $passwordHasher;
    private LoggerInterface $logger;
    private Security $security;

    public function __construct(
        DataPersisterInterface $decoratedPersister,
        UserPasswordHasherInterface $passwordHasher,
        LoggerInterface $logger,
        Security $security
    ) {
        $this->decoratedPersister = $decoratedPersister;
        $this->passwordHasher = $passwordHasher;
        $this->logger = $logger;
        $this->security = $security;
    }
    public function supports($data, array $context = []): bool
    {
        return $data instanceof User && $this->decoratedPersister->supports($data);
    }

    /**
     * @param User $data
     */
    public function persist($data, array $context = [])
    {
        if ('put' === ($context['item_operation_name'] ?? null)) {
            $this->logger->info(sprintf('User %s is being updated.', $data->getEmail()));
        }

        if (null === $data->getId()) {
            // Send registration email or do whatever other actions are necessary upon registration.
            $this->logger->info(sprintf('User %s just registered.', $data->getEmail()));
        }

        if (null !== $data->getPlainPassword() && '' !== $data->getPlainPassword()) {
            $data->setPassword($this->passwordHasher->hashPassword($data, $data->getPlainPassword()));
            $data->eraseCredentials();
        }

        $data->setIsMe($this->security->getUser() === $data);

        $this->decoratedPersister->persist($data);
    }

    public function remove($data, array $context = [])
    {
        $this->decoratedPersister->remove($data);
    }
}
