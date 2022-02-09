<?php

namespace App\Entity;

use App\Repository\RaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RaceRepository::class)]
class Race
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime_immutable_microseconds')]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable_microseconds', nullable: true)]
    private $finishedAt;

    #[ORM\OneToMany(mappedBy: 'race', targetEntity: RaceResult::class, orphanRemoval: true)]
    private $results;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'races')]
    #[ORM\JoinColumn(nullable: false)]
    private $category;

    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'races')]
    #[ORM\JoinColumn(nullable: false)]
    private $game;

    public function __construct()
    {
        $this->results = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTimeImmutable $finishedAt): self
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    /**
     * @return Collection|RaceResult[]
     */
    public function getResults(): Collection
    {
        return $this->results;
    }

    public function addResult(RaceResult $result): self
    {
        if (!$this->results->contains($result)) {
            $this->results[] = $result;
            $result->setRace($this);
        }

        return $this;
    }

    public function removeResult(RaceResult $result): self
    {
        if ($this->results->removeElement($result)) {
            // set the owning side to null (unless already changed)
            if ($result->getRace() === $this) {
                $result->setRace(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;

        return $this;
    }

    public function isFinished(): bool
    {
        return $this->getFinishedAt() !== null;
    }
}
