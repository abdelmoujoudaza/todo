<?php

namespace App\DataFixtures;

use Faker\Factory;
use Faker\Generator;
use App\Entity\Todo;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class TodoFixtures extends Fixture
{
    protected Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        for ($index = 0; $index < 20; $index++) {
            $todo = new Todo();

            $todo->setTitle($this->faker->realText(50))
                ->setCreatedAt($this->faker->dateTimeThisCentury)
                ->setUpdatedAt($this->faker->dateTimeThisCentury);

            $this->addReference("todo.{$index}", $todo);

            $manager->persist($todo);
        }

        $manager->flush();
    }
}
