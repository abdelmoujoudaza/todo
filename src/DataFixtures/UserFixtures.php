<?php

namespace App\DataFixtures;

use Faker\Factory;
use Faker\Generator;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    protected Generator $faker;
    protected UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        for ($index = 0; $index < 4; $index++) {
            $user     = new User();
            $password = $this->hasher->hashPassword($user, 'password');

            $user->setName($this->faker->name)
                ->setEmail($this->faker->email)
                ->setPassword($password)
                ->setIsVerified(true);

            $this->addReference("user.{$index}", $user);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
