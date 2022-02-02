<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Game;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoryFixtures extends Fixture implements DependentFixtureInterface
{
    public const CATEGORIES = [
        'The Legend of Zelda: A Link to the Past' => ['Any% NMG', '100%', '100% NMG',],
        'The Legend of Zelda: Ocarina of Time' => ['100%', 'All Dungeons'],
        'The Legend of Zelda: Link\'s Awakening (2019)' => ['Any% Glitchless', '100%'],
        'Super Metroid' => ['100%', 'Low%', 'RBO', 'Any% Glitched'],
        'Metroid Dread' => [],
        'Bloodstained: Curse of the Moon 2' => [],
        'Super Meat Boy' => [],
        'Celeste' => [],
        'Baba is You' => [],
        'Nioh 2' => [],
        'Triangle Strategy' => [],
        'Pokémon Red' => [],
    ];

    public function __construct(private SluggerInterface $slugger)
    {
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->referenceRepository->getReferences() as $key => $reference) {
            if (!($reference instanceof Game)) {
                continue;
            }

            /** @var Game */
            $game = $this->getReference($key);
            $categories = array_merge(self::CATEGORIES[$game->getName()] ?? [], ['Any%']);

            foreach ($categories as $categoryName) {
                $category = (new Category())
                    ->setName($categoryName)
                    ->setSlug($this->slugger->slug($categoryName))
                ;
                $game->addCategory($category);

                $manager->persist($category);
                $this->addReference("category_{$game->getName()}_$categoryName", $category);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameFixtures::class,
        ];
    }
}
