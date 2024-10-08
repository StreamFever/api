<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteUserController;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/users/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'un utilisateur'],
        normalizationContext: ['groups' => ['user:read']],
        security: 'is_granted("ROLE_USER") or is_granted("ROLE_ADMIN") or object == user',
        securityMessage: 'Vous n\'avez pas les droits pour accéder à cette ressource.',
    ),
    new GetCollection(
        uriTemplate: '/users',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les utilisateurs'],
        normalizationContext: ['groups' => ['user:read']],
        security: 'is_granted("ROLE_ADMIN")',
        securityMessage: 'Seulement les administrateurs peuvent accéder à la liste des utilisateurs.',
    ),
    new Post(
        uriTemplate: '/users/add',
        status: 201,
        schemes: ['https'],
        openapiContext: ['summary' => 'Ajouter un utilisateur'],
        normalizationContext: ['groups' => ['user:read']],
        denormalizationContext: ['groups' => ['user:write']],
        security: 'is_granted("ROLE_ADMIN")',
        securityMessage: 'Seulement les administrateurs peuvent ajouter des utilisateurs sans inscription'
    ),
    new Put(
        uriTemplate: '/users/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un utilisateur'],
        normalizationContext: ['groups' => ['user:read']],
        denormalizationContext: ['groups' => ['user:write']],
        security: 'is_granted("ROLE_ADMIN") or object == user',
        securityMessage: 'Vous ne pouvez pas modifier les données d\'un autre utilisateur',
    ),
    new Delete(
        uriTemplate: '/users/{uuid}',
        uriVariables: "uuid",
        status: 204,
        schemes: ['https'],
        controller: DeleteUserController::class,
        openapiContext: ['summary' => 'Supprimer un utilisateur'],
        normalizationContext: ['groups' => ['user:read']],
        denormalizationContext: ['groups' => ['user:write']],
        security: 'is_granted("ROLE_ADMIN")',
        securityMessage: 'Vous n\'avez pas les droits pour supprimer un utilisateur',
    )
], schemes: ['https'], normalizationContext: ['groups' => ['user:read']], denormalizationContext: ['groups' => ['user:write']], openapiContext: ['summary' => 'Utilisateur'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read','overlay:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read', 'user:write','overlay:read'])]
    private ?string $uuid;

    #[ORM\Column]
    #[Groups(['user:read', 'user:write'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Groups(['user:write'])]
    private ?string $password = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['user:read', 'user:write','overlay:read'])]
    private ?string $email = null;

    #[ORM\OneToMany(mappedBy: 'userOwner', targetEntity: Overlay::class)]
    #[Groups(['user:read', 'overlay:read'])]
    private Collection $overlays;

    #[ORM\ManyToMany(targetEntity: Overlay::class, mappedBy: 'userAccess')]
    #[Groups(['user:read', 'overlay:read'])]
    private Collection $overlaysAccess;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeInterface $createdDate;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $modifiedDate;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write','overlay:read'])]
    private ?string $pseudo = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read', 'user:write','overlay:read'])]
    private ?string $discordId = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['user:read', 'user:write','overlay:read'])]
    private ?string $avatar = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read', 'user:write','overlay:read'])]
    private ?string $ssoLogin = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read', 'user:write','overlay:read'])]
    private ?string $twitchId = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['user:read', 'user:write','overlay:read'])]
    private ?string $twitchAccessToken = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['user:read', 'user:write','overlay:read'])]
    private ?string $twitchRefreshToken = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user:read', 'user:write','overlay:read'])]
    private ?int $twitchExpiresIn = null;

    // Affilié ou non
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $twitchStatus = null;

    #[ORM\Column(nullable: true)]
    private array $token = [];

    public function __construct()
    {
        $this->overlays = new ArrayCollection();
        $this->overlaysAccess = new ArrayCollection();
        $this->createdDate = new \DateTimeImmutable();
        $this->modifiedDate = new \DateTime();
        $this->uuid = Uuid::v4();
    }

    public function __toString(): string
    {
        return $this->email;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->uuid;
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
        $this->roles = $roles;

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

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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
     * @return Collection<int, Overlay>
     */
    public function getOverlays(): Collection
    {
        return $this->overlays;
    }

    public function addOverlay(Overlay $overlay): self
    {
        if (!$this->overlays->contains($overlay)) {
            $this->overlays->add($overlay);
            $overlay->setUserOwner($this);
        }

        return $this;
    }

    public function removeOverlay(Overlay $overlay): self
    {
        if ($this->overlays->removeElement($overlay)) {
            // set the owning side to null (unless already changed)
            if ($overlay->getUserOwner() === $this) {
                $overlay->setUserOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Overlay>
     */
    public function getOverlaysAccess(): Collection
    {
        return $this->overlaysAccess;
    }

    public function addOverlaysAccess(Overlay $overlaysAccess): self
    {
        if (!$this->overlaysAccess->contains($overlaysAccess)) {
            $this->overlaysAccess->add($overlaysAccess);
            $overlaysAccess->addUserAccess($this);
        }

        return $this;
    }

    public function removeOverlaysAccess(Overlay $overlaysAccess): self
    {
        if ($this->overlaysAccess->removeElement($overlaysAccess)) {
            $overlaysAccess->removeUserAccess($this);
        }

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getModifiedDate(): ?\DateTimeInterface
    {
        return $this->modifiedDate;
    }

    public function setModifiedDate(\DateTimeInterface $modifiedDate): self
    {
        $this->modifiedDate = $modifiedDate;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getDiscordId(): ?string
    {
        return $this->discordId;
    }

    public function setDiscordId(?string $discordId): self
    {
        $this->discordId = $discordId;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getSsoLogin(): ?string
    {
        return $this->ssoLogin;
    }

    public function setSsoLogin(?string $ssoLogin): self
    {
        $this->ssoLogin = $ssoLogin;

        return $this;
    }

    public function getTwitchId(): ?string
    {
        return $this->twitchId;
    }

    public function setTwitchId(?string $twitchId): self
    {
        $this->twitchId = $twitchId;

        return $this;
    }

    public function getTwitchAccessToken(): ?string
    {
        return $this->twitchAccessToken;
    }

    public function setTwitchAccessToken(?string $twitchAccessToken): self
    {
        $this->twitchAccessToken = $twitchAccessToken;

        return $this;
    }

    public function getTwitchRefreshToken(): ?string
    {
        return $this->twitchRefreshToken;
    }

    public function setTwitchRefreshToken(?string $twitchRefreshToken): self
    {
        $this->twitchRefreshToken = $twitchRefreshToken;

        return $this;
    }

    public function getTwitchExpiresIn(): ?int
    {
        return $this->twitchExpiresIn;
    }

    public function setTwitchExpiresIn(?int $twitchExpiresIn): self
    {
        $this->twitchExpiresIn = $twitchExpiresIn;

        return $this;
    }

    public function getTwitchStatus(): ?string
    {
        return $this->twitchStatus;
    }

    public function setTwitchStatus(?string $twitchStatus): self
    {
        $this->twitchStatus = $twitchStatus;

        return $this;
    }

    public function getToken(): array
    {
        return $this->token;
    }

    public function setToken(?array $token): static
    {
        $this->token = $token;

        return $this;
    }
}
