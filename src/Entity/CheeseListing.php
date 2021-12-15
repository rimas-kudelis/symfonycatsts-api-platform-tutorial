<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Repository\CheeseListingRepository;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    collectionOperations: [
        "get",
        "post" => [
            "security" => "is_granted('ROLE_USER')",
        ],
    ],
    itemOperations: [
        "get" => [
            "normalization_context" => ["groups" => ["cheese_listing:read", "cheese_listing:item:get"]]
        ],
        "put" => [
            "security" => "is_granted('ROLE_USER')",
        ],
        "patch" => [
            "security" => "is_granted('ROLE_USER')",
        ],
        "delete" => [
            "security" => "is_granted('ROLE_ADMIN')"
        ],
    ],
    shortName: "Cheeses",
    attributes: [
        "pagination_items_per_page" => 10,
        "formats" => ["jsonld", "json", "html", "jsonhal", "csv" => ["text/csv"]],
    ],
    denormalizationContext: ["groups" => ["cheese_listing:write"], "swagger_definition_name" => "Write"],
    normalizationContext: ["groups" => ["cheese_listing:read"], "swagger_definition_name" => "Read"],
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
#[ORM\Entity(repositoryClass: CheeseListingRepository::class)]
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
    #[Groups(["cheese_listing:read", "cheese_listing:write", "user:read", "user:write"])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50, maxMessage: "The title should be 50 characters or less")]
    private ?string $title;

    /**
     * HTML-formatted description of this listing.
     */
    #[ORM\Column(type: 'text')]
    #[Groups(["cheese_listing:read"])]
    #[Assert\NotBlank]
    private string $description;

    /**
     * Price of the cheese in cents.
     */
    #[ORM\Column(type: 'integer')]
    #[Groups(["cheese_listing:read", "cheese_listing:write", "user:read", "user:write"])]
    #[Assert\NotBlank]
    #[Assert\GreaterThan(value: 0)]
    private int $price;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'boolean')]
    private bool $isPublished = false;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'cheeseListings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["cheese_listing:read", "cheese_listing:write"])]
    #[Assert\Valid]
    private ?User $owner;

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

//    public function setTitle(string $title): self
//    {
//        $this->title = $title;
//
//        return $this;
//    }
//
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Shortened text of the description.
     */
    #[Groups(["cheese_listing:read"])]
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

    /**
     * Description of this listing as plain text.
     */
    #[Groups(["cheese_listing:write", "user:write"])]
    #[SerializedName("description")]
    public function setTextDescription(string $description): self
    {
        $this->description = nl2br($description);

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

    /**
     * Time since this listing has been added.
     */
    #[Groups(["cheese_listing:read"])]
    public function getCreatedAtAgo(): string
    {
        return Carbon::instance($this->getCreatedAt())->diffForHumans();
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

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
