<?php

namespace App\DataFixtures;

use Faker\Factory;
use Faker\Generator;
use App\Entity\UserTodo;
use App\DataFixtures\TodoFixtures;
use App\DataFixtures\UserFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class UserTodoFixtures extends Fixture implements DependentFixtureInterface
{
    protected Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        for ($index = 0; $index < 20; $index++) {
            $userTodo = new UserTodo();

            $userTodo->setTodo($this->getReference("todo.{$index}"))
                ->setUser($this->getReference(sprintf('user.%d', $this->faker->numberBetween(0, 3))))
                ->setIsOwner(true);

            $manager->persist($userTodo);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            TodoFixtures::class,
        ];
    }
}
