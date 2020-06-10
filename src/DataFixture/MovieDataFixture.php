<?php

namespace App\DataFixture;

use App\Constant\BatchConstant;
use App\Constant\GenreConstant;
use App\Entity\Movie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use function iter\chunk;

class MovieDataFixture extends Fixture
{
    private Factory $fakerFactory;
    /**
     * @var GenreConstant
     */
    private GenreConstant $genreConstant;
    private int $numRows;

    public function __construct(Factory $fakerFactory, GenreConstant $genreConstant, int $numRows = 1000)
    {
        $this->fakerFactory = $fakerFactory;
        $this->genreConstant = $genreConstant;
        $this->numRows = $numRows;
    }

    public function load(ObjectManager $manager)
    {
        $faker = $this->fakerFactory::create();
        $genres = $this->genreConstant->asArray();
        $range = range(1, $this->numRows);
        foreach (chunk($range, BatchConstant::SIZE) as $items) {
            foreach ($items as $item) {
                $entity = new Movie(
                    $faker->randomElement($genres),
                    $faker->unique()->name,
                    $faker->dateTimeInInterval('now', '+1 year')
                );
                $manager->persist($entity);
            }
            $manager->flush();
            $manager->clear();
        }
    }
}
