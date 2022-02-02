<?php

namespace App\DataFixtures;

use App\Entity\Race;
use App\Entity\RaceResult;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RaceResultFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach ($this->referenceRepository->getReferences() as $userKey => $userReference) {
            if (!($userReference instanceof User)) {
                continue;
            }

            /** @var User */
            $user = $this->getReference($userKey);

            foreach ($this->referenceRepository->getReferences() as $raceKey => $raceReference) {
                if (!($raceReference instanceof Race)) {
                    continue;
                }

                if ((crc32($userKey . $raceKey) / 0xFFFFFFFF) < 0.5) {
                    continue;
                }

                /** @var Race */
                $race = $this->getReference($raceKey);

                $startElo = 1000 + (500 * (crc32($userKey . $raceKey . '1') / 0xFFFFFFFF) * 2);
                $finishElo = 1000 + (500 * (crc32($userKey . $raceKey . '2') / 0xFFFFFFFF) * 2);

                $result = (new RaceResult())
                    ->setStartedAt(new DateTimeImmutable('2021-12-31 15:00'))
                    ->setFinishedAt(new DateTimeImmutable('2022-02-02 15:00'))
                    ->setStartElo($startElo)
                    ->setFinishElo($finishElo)
                ;
                $user->addRaceResult($result);
                $race->addResult($result);

                $manager->persist($result);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            RaceFixtures::class,
            UserFixtures::class,
        ];
    }
}
