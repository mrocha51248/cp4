<?php

namespace App\DataFixtures;

use App\Entity\Game;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class GameFixtures extends Fixture
{
    public const GAMES = [
        [
            'name' => 'The Legend of Zelda: A Link to the Past',
            'logo' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co3vzn.png',
        ],
        [
            'name' => 'The Legend of Zelda: Ocarina of Time',
            'logo' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co3nnx.png',
        ],
        [
            'name' => 'The Legend of Zelda: Link\'s Awakening (2019)',
            'logo' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1qve.png',
        ],
        [
            'name' => 'Super Metroid',
            'logo' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1o11.png',
        ],
        [
            'name' => 'Metroid Dread',
            'logo' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co39zx.png',
        ],
        [
            'name' => 'Bloodstained: Curse of the Moon 2',
            'logo' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co2d4b.png',
        ],
        [
            'name' => 'Super Meat Boy',
            'logo' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co276m.png',
        ],
        [
            'name' => 'Celeste',
            'logo' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co3byy.png',
        ],
        [
            'name' => 'Baba is You',
            'logo' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co2hqt.png',
        ],
        [
            'name' => 'Nioh 2',
            'logo' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1sh7.png',
        ],
        [
            'name' => 'Triangle Strategy',
            'logo' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co3vno.png',
        ],
        [
            'name' => 'Pokémon Red',
            'logo' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co2s5t.png',
        ],
    ];

    public function __construct(private SluggerInterface $slugger)
    {
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::GAMES as $gameData) {
            $game = (new Game())
                ->setName($gameData['name'])
                ->setLogo($gameData['logo'])
                ->setSlug($this->slugger->slug(($gameData['name'])))
            ;

            $manager->persist($game);
            $this->addReference('game_' . $gameData['name'], $game);
        }

        $manager->flush();
    }
}
