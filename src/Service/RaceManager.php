<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Race;
use App\Entity\RaceResult;
use App\Entity\User;
use App\Repository\RaceRepository;
use App\Repository\RaceResultRepository;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RaceManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RaceResultRepository $raceResultRepository,
        private RaceRepository $raceRepository,
        private UserScoreManager $userScoreManager
    ) {
    }

    public function createRace(Category $category): Race
    {
        $race = (new Race())
            ->setCreatedAt(new DateTimeImmutable())
        ;
        $category->addRace($race);
        $category->getGame()->addRace($race);

        $this->entityManager->persist($race);
        $this->entityManager->flush();

        return $race;
    }

    public function getRaceToJoin(User $user, Category $category): ?Race
    {
        $joinableRaces = $this->raceRepository->findJoinableRaces($user, $category);
        if (!count($joinableRaces)) {
            $joinableRaces = [$this->createRace($category)];
        }
        $randomKey = array_rand($joinableRaces);
        return $joinableRaces[$randomKey];
    }

    public function play(User $user, Category $category): RaceResult
    {
        if (count($this->raceResultRepository->findByUserNotFinished($user))) {
            throw new AccessDeniedException('User is already racing');
        }

        $race = $this->getRaceToJoin($user, $category);
        if (!$race) {
            throw new AccessDeniedException('Could not find a race to join');
        }

        $userElo = $this->userScoreManager->getScore($user, $category);
        $startedAt = (new DateTimeImmutable())->add(DateInterval::createFromDateString('15 seconds'));

        $result = (new RaceResult())
            ->setStartedAt($startedAt)
            ->setStartElo($userElo)
        ;
        $user->addRaceResult($result);
        $race->addResult($result);

        $this->entityManager->persist($result);
        $this->entityManager->flush();

        return $result;
    }

    public function userDone(RaceResult $result, ?DateTimeImmutable $time = new DateTimeImmutable()): void
    {
        if (!$time) {
            $time = $result->getRace()->getCreatedAt();
        }

        $result->setFinishedAt($time);
        $this->entityManager->flush();
    }

    public function userForfeit(RaceResult $result): void
    {
        $this->userDone($result, null);
    }
}
