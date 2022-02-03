<?php

namespace App\Entity;

use App\Repository\RaceResultRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RaceResultRepository::class)]
class RaceResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime_immutable')]
    private $startedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $finishedAt;

    #[ORM\Column(type: 'integer')]
    private $startElo;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $finishElo;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'raceResults')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\ManyToOne(targetEntity: Race::class, inversedBy: 'results')]
    #[ORM\JoinColumn(nullable: false)]
    private $race;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): self
    {
        $this->startedAt = $startedAt;

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

    public function getStartElo(): ?int
    {
        return $this->startElo;
    }

    public function setStartElo(int $startElo): self
    {
        $this->startElo = $startElo;

        return $this;
    }

    public function getFinishElo(): ?int
    {
        return $this->finishElo;
    }

    public function setFinishElo(?int $finishElo): self
    {
        $this->finishElo = $finishElo;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getRace(): ?Race
    {
        return $this->race;
    }

    public function setRace(?Race $race): self
    {
        $this->race = $race;

        return $this;
    }

    public function isFinished(): bool
    {
        return $this->getFinishedAt() !== null;
    }

    public function isForfeited(): bool
    {
        return $this->isFinished() && $this->getFinishedAt() <= $this->getStartedAt();
    }

    public function compare(RaceResult $other): int
    {
        $makeScore = fn($x) => !$x->isFinished() ? 2 : ($x->isForfeited() ? 1 : 0);
        $thisScore = $makeScore($this);
        $otherScore = $makeScore($other);
        if ($thisScore === $otherScore) {
            if (!$this->isFinished() || $this->isForfeited()) {
                return $this->getStartedAt() <=> $other->getStartedAt();
            }

            // Can't compare DateInterval directly
            $now = new DateTimeImmutable();
            $thisTime = $now->add($this->getFinishedAt()->diff($this->getStartedAt()));
            $otherTime = $now->add($other->getFinishedAt()->diff($other->getStartedAt()));
            return $otherTime <=> $thisTime;
        }
        return $thisScore <=> $otherScore;
    }
}
