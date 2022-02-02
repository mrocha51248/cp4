<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Game;
use App\Entity\Race;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RaceFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach ($this->referenceRepository->getReferences() as $key => $reference) {
            if (!($reference instanceof Game)) {
                continue;
            }

            /** @var Game */
            $game = $this->getReference($key);
            /** @var Category */
            $category = $this->getReference("category_{$game->getName()}_Any%");

            foreach (range(1, 20) as $index) {
                $race = (new Race())
                    ->setCreatedAt(new DateTimeImmutable('2021-12-31 15:00'))
                    ->setFinishedAt(new DateTimeImmutable('2022-02-02 15:00'))
                ;
                $category->addRace($race);

                $manager->persist($race);
                $this->addReference("race_{$game->getName()}_{$category->getName()}_$index", $race);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            GameFixtures::class,
        ];
    }
}
