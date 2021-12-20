<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\ApiPlatform\DailyStatsDateFilter;
use App\Entity\CheeseListing;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    collectionOperations: ['get'],
    itemOperations: ['get', 'put'],
    paginationItemsPerPage: 7,
)]
#[ApiFilter(filterClass: DailyStatsDateFilter::class, arguments: ['throwOnInvalid' => true])]
class DailyStats
{
    #[Groups(['dailystats:read'])]
    public \DateTimeInterface $date;

    #[Groups(['dailystats:read', 'dailystats:write'])]
    public int $totalVisitors;

    /**
     * The 5 most popular cheese listing from this date.
     *
     * @var CheeseListing[]
     */
    #[Groups(['dailystats:read'])]
    public array $mostPopularListings;

    /**
     * @param CheeseListing[] $mostPopularListings
     */
    public function __construct(\DateTimeInterface $date, int $totalVisitors, array $mostPopularListings)
    {
        $this->date = $date;
        $this->totalVisitors = $totalVisitors;
        $this->mostPopularListings = $mostPopularListings;
    }

    #[ApiProperty(identifier: true)]
    public function getDateString(): string
    {
        return $this->date->format('Y-m-d');
    }
}
