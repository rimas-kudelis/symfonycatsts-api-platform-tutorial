<?php

declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\ApiResource\DailyStats;
use App\Service\StatsHelper;

class DailyStatsProvider implements CollectionDataProviderInterface, ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private StatsHelper $statsHelper;

    public function __construct(StatsHelper $statsHelper)
    {
        $this->statsHelper = $statsHelper;
    }

    public function getCollection(string $resourceClass, string $operationName = null)
    {
        return $this->statsHelper->fetchMany();
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        return $this->statsHelper->fetchOne($id);
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return DailyStats::class === $resourceClass;
    }
}
