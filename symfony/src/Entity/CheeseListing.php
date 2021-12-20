<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\ApiPlatform\CheeseSearchFilter;
use App\Doctrine\CheeseListingSetOwnerListener;
use App\Dto\CheeseListingInput;
use App\Dto\CheeseListingOutput;
use App\Repository\CheeseListingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as AppAssert;

#[ApiResource(
    collectionOperations: [
        "get",
        "post" => [
            "security" => "is_granted('ROLE_USER')",
        ],
    ],
    itemOperations: [
        "get",
        "put" => [
            "security" => "is_granted('EDIT', object)",
            "security_message" => "Only the creator can edit a cheese listing.",
        ],
        "patch" => [
            "security" => "is_granted('EDIT', object)",
            "security_message" => "Only the creator can edit a cheese listing.",
        ],
        "delete" => [
            "security" => "is_granted('ROLE_ADMIN')"
        ],
    ],
    shortName: "Cheese",
    attributes: [
        "pagination_items_per_page" => 10,
        "formats" => ["jsonld", "json", "html", "jsonhal", "csv" => ["text/csv"]],
    ],
    input: CheeseListingInput::class,
    output: CheeseListingOutput::class,
)]
#[ApiFilter(BooleanFilter::class, properties: ["isPublished"])]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        "title" => "partial",
        "description" => "partial",
        "owner" => "exact",
        "owner.username" => "partial",
    ]
)]
#[ApiFilter(RangeFilter::class, properties: ["price"])]
#[ApiFilter(PropertyFilter::class)]
#[ApiFilter(CheeseSearchFilter::class)]
#[ORM\Entity(repositoryClass: CheeseListingRepository::class)]
#[ORM\EntityListeners([CheeseListingSetOwnerListener::class])]
#[AppAssert\ValidIsPublished]
class CheeseListing
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id;

    /**
     * Title of the listing.
     */
    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50, maxMessage: "The title should be 50 characters or less")]
    private ?string $title;

    /**
     * HTML-formatted description of this listing.
     */
    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private string $description;

    /**
     * Price of the cheese in cents.
     */
    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank]
    #[Assert\GreaterThan(value: 0)]
    private int $price;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'boolean')]
    private bool $isPublished = false;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'cheeseListings')]
    #[ORM\JoinColumn(nullable: false)]
    #[AppAssert\IsValidOwner]
    private ?User $owner = null;

    public function __construct(string $title = null)
    {
        $this->title = $title;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Shortened text of the description.
     */
    public function getShortDescription(): ?string
    {
        if (40 > strlen($this->getDescription())) {
            return $this->getDescription();
        }

        return substr($this->description, 0, 40).'...';
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
