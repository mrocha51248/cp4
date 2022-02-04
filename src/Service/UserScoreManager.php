<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\User;
use App\Entity\UserScore;
use App\Repository\UserScoreRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserScoreManager
{
    public const DEFAULT_ELO = 1500;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserScoreRepository $userScoreRepository
    ) {
    }

    public function getScore(User $user, Category $category): int
    {
        $userScore = $this->userScoreRepository->findOneBy(['user' => $user, 'category' => $category]);
        if (!$userScore) {
            return self::DEFAULT_ELO;
        }
        return $userScore->getElo();
    }

    public function saveScore(User $user, Category $category, int $elo, bool $flush = true): int
    {
        $userScore = $this->userScoreRepository->findOneBy(['user' => $user, 'category' => $category]);

        if (!$userScore) {
            $previousElo = self::DEFAULT_ELO;
            $userScore = new UserScore();
            $user->addScore($userScore);
            $category->addUserScore($userScore);
            $this->entityManager->persist($userScore);
        } else {
            $previousElo = $userScore->getElo();
        }

        $userScore->setElo($elo);
        if ($flush) {
            $this->entityManager->flush();
        }

        return $previousElo;
    }
}
