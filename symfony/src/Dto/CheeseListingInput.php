<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\CheeseListing;
use App\Entity\User;
use App\Validator as AppAssert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class CheeseListingInput
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50, maxMessage: "The title should be 50 characters or less")]
    #[Groups(["cheese:write", "user:write"])]
    public string $title = '';

    #[Assert\NotBlank]
    #[Assert\GreaterThan(value: 0)]
    #[Groups(["cheese:write", "user:write"])]
    public int $price = 0;

    #[Groups(["cheese:collection:post"])]
    #[AppAssert\IsValidOwner]
    public ?User $owner = null;

    #[Groups(['cheese:write'])]
    public bool $isPublished = false;

    #[Assert\NotBlank]
    public string $description = '';

    public static function createFromEntity(?CheeseListing $cheeseListing): self
    {
        $dto = new CheeseListingInput();

        // not an edit, so just return an empty DTO
        if (null === $cheeseListing) {
            return $dto;
        }

        $dto->title = $cheeseListing->getTitle();
        $dto->price = $cheeseListing->getPrice();
        $dto->description = $cheeseListing->getDescription();
        $dto->owner = $cheeseListing->getOwner();
        $dto->isPublished = $cheeseListing->getIsPublished();

        return $dto;
    }

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

    public function createOrUpdateEntity(?CheeseListing $cheeseListing): CheeseListing
    {
        if (null === $cheeseListing) {
            $cheeseListing = new CheeseListing($this->title);
        }

        $cheeseListing->setDescription($this->description);
        $cheeseListing->setPrice($this->price);
        if (null !== $this->owner) {
            $cheeseListing->setOwner($this->owner);
        }
        $cheeseListing->setIsPublished($this->isPublished);

        return $cheeseListing;
    }
}
