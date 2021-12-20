<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

class CheeseListingInput
{
    #[Groups(["cheese:write", "user:write"])]
    public string $title;

    #[Groups(["cheese:write", "user:write"])]
    public int $price;

    #[Groups(["cheese:collection:post"])]
    public User $owner;

    #[Groups(['cheese:write'])]
    public bool $isPublished = false;

    public string $description;

    /**
     * Description of this listing as plain text.
     */
    #[Groups(["cheese:write", "user:write"])]
    #[SerializedName("description")]
    public function setTextDescription(string $description): self
    {
        $this->description = nl2br($description);

        return $this;
    }
}
