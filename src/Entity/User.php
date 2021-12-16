<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    collectionOperations: [
        "get",
        "post" => [
            "security" => "is_granted('IS_AUTHENTICATED_ANONYMOUSLY')",
            "validation_groups" => [ "Default", "create" ],
        ],
    ],
    itemOperations: [
        "get",
        "put" => [
            "security" => "is_granted('ROLE_USER') and object == user",
        ],
        "patch" => [
            "security" => "is_granted('ROLE_USER') and object == user",
        ],
        "delete" => [
            "security" => "is_granted('ROLE_ADMIN')",
        ],
    ],
    security: "is_granted('ROLE_USER')",
)]
#[ApiFilter(PropertyFilter::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Groups(["user:read", "user:write"])]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    #[Groups(["admin:write"])]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private string $password;

    #[Groups(["user:write"])]
    #[SerializedName('password')]
    #[Assert\NotBlank(groups: ["create"])]
    private ?string $plainPassword = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Groups(["user:read", "user:write", "cheese:item:get"])]
    #[Assert\NotBlank]
    private string $username;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: CheeseListing::class, cascade: ['persist'], orphanRemoval: true)]
    #[Groups(["user:write" ])]
    #[Assert\Valid]
    private Collection $cheeseListings;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(["admin:read", "user:write", "owner:read" ])]
    private ?string $phoneNumber = null;

    public function __construct()
    {
        $this->cheeseListings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function getUsername(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = array_unique($roles);

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection|CheeseListing[]
     */
    public function getCheeseListings(): Collection
    {
        return $this->cheeseListings;
    }

    public function addCheeseListing(CheeseListing $cheeseListing): self
    {
        if (!$this->cheeseListings->contains($cheeseListing)) {
            $this->cheeseListings[] = $cheeseListing;
            $cheeseListing->setOwner($this);
        }

        return $this;
    }

    public function removeCheeseListing(CheeseListing $cheeseListing): self
    {
        if ($this->cheeseListings->removeElement($cheeseListing)) {
            // set the owning side to null (unless already changed)
            if ($cheeseListing->getOwner() === $this) {
                $cheeseListing->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CheeseListing[]
     */
    #[Groups(["user:read"])]
    #[SerializedName("cheeseListings")]
    public function getPublishedCheeseListings(): Collection
    {
        return $this->getCheeseListings()->filter(
            static fn (CheeseListing $cheeseListing) => $cheeseListing->getIsPublished(),
        );
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }
}
