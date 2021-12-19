<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Core\Action\NotFoundAction;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\CheeseListing;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    collectionOperations: ['get'],
    itemOperations: [
        'get' => [
            'method' => 'GET',
            'controller' => NotFoundAction::class,
            'read' => false,
            'output' => false,
        ],
    ]
)]
class DailyStats
{
    #[Groups(['dailystats:read'])]
    public \DateTimeInterface $date;

    #[Groups(['dailystats:read'])]
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