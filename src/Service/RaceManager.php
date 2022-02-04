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
use EloRating\Player as EloPlayer;
use EloRating\Game as EloGame;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RaceManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RaceResultRepository $raceResultRepository,
        private RaceRepository $raceRepository,
        private UserScoreManager $userScoreManager,
        private DateIntervalConverter $dateIntervalConverter
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

    public function closeRace(Race $race): void
    {
        if ($race->getFinishedAt()) {
            return;
        }

        $race->setFinishedAt(new DateTimeImmutable());

        $results = $race->getResults();
        foreach ($results as $result) {
            $elo = $this->userScoreManager->getScore($result->getUser(), $race->getCategory());
            $result->setStartElo($elo);

            if (!$result->getFinishedAt()) {
                $result->setFinishedAt($race->getCreatedAt());
            }
        }

        list($min, $max) = $this->getScoringRange($race);
        if ($min === null || $max === null) {
            foreach ($results as $result) {
                $result->setFinishElo($result->getStartElo());
            }

            $this->entityManager->flush();
            return;
        }

        $times = $this->getRaceTimes($race);
        foreach ($times as list('time' => $timeMs, 'result' => $result)) {
            $elo = $result->getStartElo();
            foreach ($times as list('time' => $otherTimeMs, 'result' => $otherResult)) {
                if ($otherResult === $result) {
                    continue;
                }

                $player1 = new EloPlayer($result->getStartElo());
                $player2 = new EloPlayer($otherResult->getStartElo());

                $game = new EloGame($player1, $player2);
                // TODO: Factor in scoring range
                $game->setScore(
                        $timeMs !== null && ($timeMs <= $otherTimeMs || $otherTimeMs === null) ? 1 : 0,
                        $otherTimeMs !== null && ($otherTimeMs <= $timeMs || $timeMs === null) ? 1 : 0
                    )
                    ->setK(32)
                    ->count();

                $elo += $game->getPlayer1()->getRating() - $result->getStartElo();
            }
            $result->setFinishElo($elo);
            $this->userScoreManager->saveScore($result->getUser(), $race->getCategory(), $elo);
        }

        $this->entityManager->flush();
    }

    public function getRaceTimes(Race $race): array
    {
        $times = [];
        foreach ($race->getResults() as $result) {
            if (!$result->isFinished() || $result->isForfeited()) {
                $times[] = ['time' => null, 'result' => $result];
                continue;
            }
            $timeMs = $this->dateIntervalConverter->getMillisecondsTotal(
                $result->getStartedAt()->diff($result->getFinishedAt())
            );
            $times[] = ['time' => $timeMs, 'result' => $result];
        }
        return $times;
    }

    public function getScoringRange(Race $race): array
    {
        list($min, $max) = [null, null];
        foreach ($race->getResults() as $result) {
            if (!$result->isFinished() || $result->isForfeited()) {
                continue;
            }
            $timeMs = $this->dateIntervalConverter->getMillisecondsTotal(
                $result->getStartedAt()->diff($result->getFinishedAt())
            );
            if ($timeMs > $max || $max === null) {
                $max = $timeMs;
            }
            if ($timeMs < $min || $min === null) {
                $min = $timeMs;
            }
        }
        return [$min, $max];
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
