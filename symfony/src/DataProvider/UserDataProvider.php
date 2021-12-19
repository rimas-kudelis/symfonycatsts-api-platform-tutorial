<?php

declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\DenormalizedIdentifiersAwareItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;

class UserDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface, DenormalizedIdentifiersAwareItemDataProviderInterface
{
    private ContextAwareCollectionDataProviderInterface $decoratedCollectionDataProvider;
    private DenormalizedIdentifiersAwareItemDataProviderInterface $decoratedItemDataProvider;
    private Security $security;

    public function __construct(
        ContextAwareCollectionDataProviderInterface $decoratedCollectionDataProvider,
        DenormalizedIdentifiersAwareItemDataProviderInterface $decoratedItemDataProvider,
        Security $security
    ) {
        $this->decoratedCollectionDataProvider = $decoratedCollectionDataProvider;
        $this->decoratedItemDataProvider = $decoratedItemDataProvider;
        $this->security = $security;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        /** @var User[] $users */
        $users = $this->decoratedCollectionDataProvider->getCollection($resourceClass, $operationName, $context);

        $currentUser = $this->security->getUser();
//        foreach ($users as $user) {
//            $user->setIsMe($currentUser === $user);
//        }

        return $users;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        /** @var User|null $item */
        $item = $this->decoratedItemDataProvider->getItem($resourceClass, $id, $operationName, $context);

//        if ($item instanceof User) {
//            $item->setIsMe($item === $this->security->getUser());
//        }

        return $item;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return User::class === $resourceClass;
    }
}
