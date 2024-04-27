<?php

namespace App\Entity;

use App\Repository\EventRepository;
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
    #[Groups(["getCategory", "getEvent"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getCategory", "getEvent"])]
    #[Assert\NotBlank(message: 'Title is required.')]
    #[Assert\Length(max: 50, maxMessage: 'Title is too long.')]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(["getCategory", "getEvent"])]
    #[Assert\NotBlank(message: 'Description is required.')]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getCategory", "getEvent"])]
    #[Assert\NotBlank(message: 'City is required.')]
    private ?City $city = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getEvent"])]
    #[Assert\NotBlank(message: 'Category is required.')]
    private ?Category $category = null;

    #[ORM\Column]
    #[Groups(["getCategory", "getEvent"])]
    #[Assert\NotBlank(message: 'Start date is required.')]
    #[Assert\GreaterThan('today', message: 'Start date must be in the future.')]
    private ?\DateTimeImmutable $start_at = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[Assert\NotBlank(message: 'Country is required.')]
    #[Groups(["getCategory", "getEvent"])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Country $country = null;

    #[ORM\ManyToOne(inversedBy: 'eventsCreated')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getEvent"])]
    private ?User $creator = null;

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
}
