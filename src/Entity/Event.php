<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getCategory", "getEvent", "getOneEvent", "getUser"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getCategory", "getEvent", "getOneEvent", "getUser"])]
    #[Assert\NotBlank(message: 'Title is required.')]
    #[Assert\Length(max: 50, maxMessage: 'Title is too long.')]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(["getCategory", "getOneEvent"])]
    #[Assert\NotBlank(message: 'Description is required.')]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getCategory", "getEvent", "getUser", "getOneEvent"])]
    #[Assert\NotBlank(message: 'City is required.')]
    private ?City $city = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getEvent", "getOneEvent"])]
    #[Assert\NotBlank(message: 'Category is required.')]
    private ?Category $category = null;

    #[ORM\Column]
    #[Groups(["getCategory", "getEvent", "getOneEvent"])]
    #[Assert\NotBlank(message: 'Start date is required.')]
    #[Assert\GreaterThan('today', message: 'Start date must be in the future.')]
    private ?\DateTimeImmutable $start_at = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[Groups(["getCategory", "getOneEvent"])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Country $country = null;

    #[ORM\ManyToOne(inversedBy: 'eventsCreated')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getOneEvent", "getOneEvent"])]
    private ?User $creator = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'events')]
    #[Groups(["getOneEvent", "getOneEvent"])]
    private Collection $participant;

    #[ORM\Column(nullable: true)]
    #[Groups(["getOneEvent"])]
    private ?int $participantLimit = null;

    #[ORM\Column]
    #[Groups(["getEvent", "getOneEvent"])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["getEvent", "getOneEvent"])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->participant = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->start_at;
    }

    public function setStartAt(\DateTimeImmutable $start_at): static
    {
        $this->start_at = $start_at;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        if ($this->creator !== null) {
            return $this;
        }

        $this->creator = $creator;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipant(): Collection
    {
        return $this->participant;
    }

    public function addParticipant(User $participant): static
    {
        if (!$this->participant->contains($participant)) {
            $this->participant->add($participant);
        }

        return $this;
    }

    public function removeParticipant(User $participant): static
    {
        $this->participant->removeElement($participant);

        return $this;
    }

    public function getParticipantLimit(): ?int
    {
        return $this->participantLimit;
    }

    public function setParticipantLimit(?int $participantLimit): static
    {
        $this->participantLimit = $participantLimit;

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
}
