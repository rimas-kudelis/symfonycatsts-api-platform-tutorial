<?php

declare(strict_types=1);

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\CheeseListing;
use App\Entity\CheeseNotification;
use Doctrine\ORM\EntityManagerInterface;

class CheeseListingDataPersister implements DataPersisterInterface
{
    private DataPersisterInterface $decoratedPersister;
    private EntityManagerInterface $entityManager;

    public function __construct(
        DataPersisterInterface $decoratedPersister,
        EntityManagerInterface $entityManager
    ) {
        $this->decoratedPersister = $decoratedPersister;
        $this->entityManager = $entityManager;
    }

    public function supports($data): bool
    {
        return $data instanceof CheeseListing;
    }

    /**
     * @param CheeseListing $data
     */
    public function persist($data)
    {
        $originalData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($data);

        if ($data->getIsPublished() && !($originalData['isPublished'] ?? false)) {
            $notification = new CheeseNotification($data, 'Cheese listing has been created.');
            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }

        $this->decoratedPersister->persist($data);
    }

    public function remove($data)
    {
        $this->decoratedPersister->remove($data);
    }
}
