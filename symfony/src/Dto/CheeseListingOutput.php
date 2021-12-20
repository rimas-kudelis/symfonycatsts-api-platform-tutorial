<?php

declare(strict_types=1);

namespace App\Dto;

use Carbon\Carbon;
use Symfony\Component\Serializer\Annotation\Groups;

class CheeseListingOutput
{
    /**
     * The title of this listing.
     */
    #[Groups(['cheese:read'])]
    public string $title;

    #[Groups(['cheese:read'])]
    public string $description;

    #[Groups(['cheese:read'])]
    public int $price;

    public \DateTimeInterface $createdAt;

    /**
     * Shortened text of the description.
     */
    #[Groups(["cheese:read"])]
    public function getShortDescription(): ?string
    {
        if (40 > strlen($this->description)) {
            return $this->description;
        }

        return substr($this->description, 0, 40).'...';
    }

    /**
     * Time since this listing has been added.
     */
    #[Groups(["cheese:read"])]
    public function getCreatedAtAgo(): string
    {
        return Carbon::instance($this->createdAt)->diffForHumans();
    }
}
