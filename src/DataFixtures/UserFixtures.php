<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const USERS = [
        [
            'email' => 'admin@cp4.gg',
            'name' => 'Administrator',
            'roles' => ['ROLE_ADMIN'],
        ],
        [
            'email' => 'user1@cp4.gg',
            'name' => 'User1',
        ],
        [
            'email' => 'user2@cp4.gg',
            'name' => 'User2',
        ],
        [
            'email' => 'user3@cp4.gg',
            'name' => 'User3',
        ],
        [
            'email' => 'user4@cp4.gg',
            'name' => 'User4',
        ],
        [
            'email' => 'user5@cp4.gg',
            'name' => 'User5',
        ],
    ];

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::USERS as $userData) {
            $user = new User();
            $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password'] ?? 'password');
            $user
                ->setEmail($userData['email'])
                ->setName($userData['name'])
                ->setRoles($userData['roles'] ?? [])
                ->setPassword($hashedPassword)
            ;

            $manager->persist($user);
            $this->addReference('user_' . strtolower($userData['name']), $user);
        }

        $manager->flush();
    }
}
