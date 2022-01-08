<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Task;
use Faker\Generator;
use App\DataFixtures\TodoFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    protected Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        for ($index = 0; $index < 100; $index++) {
            $task = new Task();

            $task->setBody($this->faker->realText(50))
                ->setTodo($this->getReference(sprintf('todo.%d', $this->faker->numberBetween(0, 19))));

            $manager->persist($task);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TodoFixtures::class
        ];
    }
}
