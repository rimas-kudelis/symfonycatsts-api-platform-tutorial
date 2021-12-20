<?php

declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Service\StatsHelper;
use ArrayIterator;
use DateTimeInterface;

class DailyStatsPaginator implements PaginatorInterface, \IteratorAggregate
{
    private ?ArrayIterator $dailyStatsIterator = null;
    private StatsHelper $statsHelper;
    private int $currentPage;
    private int $maxResults;
    private ?DateTimeInterface $fromDate;

    public function __construct(StatsHelper $statsHelper, int $currentPage, int $maxResults)
    {
        $this->statsHelper = $statsHelper;
        $this->currentPage = $currentPage;
        $this->maxResults = $maxResults;
    }

    public function getLastPage(): float
    {
        return ceil($this->getTotalItems() / $this->getItemsPerPage()) ?: 1.;
    }

    public function getTotalItems(): float
    {
        return $this->statsHelper->count();
    }

    public function getCurrentPage(): float
    {
        return $this->currentPage;
    }

    public function getItemsPerPage(): float
    {
        return $this->maxResults;
    }

    public function count()
    {
        return iterator_count($this->getIterator());
    }

    public function getIterator()
    {
        if (null === $this->dailyStatsIterator) {
            $offset = (int)(string)(($this->getCurrentPage() - 1) * $this->getItemsPerPage());

            $criteria = [];
            if (null !== $this->fromDate) {
                $criteria['from'] = $this->fromDate;
            }
            $this->dailyStatsIterator = new ArrayIterator(
                $this->statsHelper->fetchMany(
                    $this->maxResults,
                    $offset,
                    $criteria,
                ),
            );
        }

        return $this->dailyStatsIterator;
    }

    public function setFromDate(?DateTimeInterface $fromDate): void
    {
        $this->fromDate = $fromDate;
    }
}
