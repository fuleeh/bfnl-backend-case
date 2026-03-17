<?php

namespace App\DataFixtures;

use App\Entity\SportVenue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create();
        for ($i = 0; $i < 50; $i++) {
            $sportVenue = new SportVenue();
            $sportVenue->setName($faker->company());
            $sportVenue->setLat($faker->latitude());
            $sportVenue->setLng($faker->longitude());
            $manager->persist($sportVenue);
        }
        $manager->flush();
    }
}
