<?php

declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\Pagination;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\ApiResource\DailyStats;
use App\Service\StatsHelper;

class DailyStatsProvider implements CollectionDataProviderInterface, ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private StatsHelper $statsHelper;
    private Pagination $pagination;

    public function __construct(StatsHelper $statsHelper, Pagination $pagination)
    {
        $this->statsHelper = $statsHelper;
        $this->pagination = $pagination;
    }

    public function getCollection(string $resourceClass, string $operationName = null)
    {
        [$page, $offset, $limit] = $this->pagination->getPagination($resourceClass, $operationName);

        return new DailyStatsPaginator(
            $this->statsHelper,
            $page,
            $limit,
        );
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
