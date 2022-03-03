<?php

namespace App\DataFixtures;

use Faker\Factory;
use Faker\Generator;
use App\Entity\Admin;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminFixtures extends Fixture
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
        $admin    = new Admin();
        $password = $this->hasher->hashPassword($admin, 'password');

        $admin->setEmail('admin@test.com')
            ->setPassword($password)
            ->setRoles([Admin::ROLE_SUPER_ADMIN]);

        $manager->persist($admin);

        $manager->flush();
    }
}
