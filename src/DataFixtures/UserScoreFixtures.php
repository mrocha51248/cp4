<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\User;
use App\Entity\UserScore;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserScoreFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach ($this->referenceRepository->getReferences() as $userKey => $userReference) {
            if (!($userReference instanceof User)) {
                continue;
            }

            /** @var User */
            $user = $this->getReference($userKey);

            foreach ($this->referenceRepository->getReferences() as $categoryKey => $categoryReference) {
                if (!($categoryReference instanceof Category)) {
                    continue;
                }
    
                /** @var Category */
                $category = $this->getReference($categoryKey);

                if (!count($category->getRaces())) {
                    continue;
                }

                $elo = 1000 + intval(500 * (crc32($userKey . $categoryKey) / 0xFFFFFFFF) * 2);
                $score = (new UserScore())
                    ->setElo($elo)
                ;
                $user->addScore($score);
                $category->addUserScore($score);

                $manager->persist($score);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            RaceFixtures::class,
            UserFixtures::class,
        ];
    }
}
