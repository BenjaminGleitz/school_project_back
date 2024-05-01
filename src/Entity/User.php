<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getUser", "getEvent"])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(["getUser", "getEvent"])]
    #[Assert\NotBlank(message: 'Email is required.')]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(["getUser"])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: 'Password is required.')]
    private ?string $password = null;

    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'creator', orphanRemoval: true)]
    private Collection $eventsCreated;

    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'participant')]
    private Collection $events;

    #[ORM\Column(length: 255)]
    #[Groups(["getUser"])]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getUser"])]
    private ?string $lastname = null;

    #[ORM\Column]
    #[Groups(["getUser"])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["getUser"])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[Groups(["getUser"])]
    private ?City $favoriteCity = null;

    public function __construct()
    {
        $this->eventsCreated = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
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
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
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

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        // Ajoutez votre logique pour vérifier si l'utilisateur est un administrateur.
        // Par exemple, si vous avez une propriété "roles" qui contient le rôle de l'utilisateur,
        // vous pouvez vérifier si ce tableau de rôles contient le rôle d'administrateur.
        return \in_array('ROLE_ADMIN', $this->roles, true);
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEventsCreated(): Collection
    {
        return $this->eventsCreated;
    }

    public function addEventsCreated(Event $eventsCreated): static
    {
        if (!$this->eventsCreated->contains($eventsCreated)) {
            $this->eventsCreated->add($eventsCreated);
            $eventsCreated->setCreator($this);
        }

        return $this;
    }

    public function removeEventsCreated(Event $eventsCreated): static
    {
        if ($this->eventsCreated->removeElement($eventsCreated)) {
            // set the owning side to null (unless already changed)
            if ($eventsCreated->getCreator() === $this) {
                $eventsCreated->setCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->addParticipant($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            $event->removeParticipant($this);
        }

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getFavoriteCity(): ?City
    {
        return $this->favoriteCity;
    }

    public function setFavoriteCity(?City $favoriteCity): static
    {
        $this->favoriteCity = $favoriteCity;

        return $this;
    }
}
