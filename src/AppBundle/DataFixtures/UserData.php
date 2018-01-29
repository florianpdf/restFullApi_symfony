<?php

namespace AppBundle\DataFixtures;

use AppBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;

class UserData extends Fixture{

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager){
        $faker = Faker\Factory::create();
        for ($i = 0; $i < 10; $i++){
            $user = new User();
            $user->setEmail($faker->email);
            $user->setFirstname($faker->firstName());
            $user->setName($faker->name);
            $user->setPicture($faker->imageUrl());
            $user->setBut(0);

            $manager->persist($user);
        }

        $manager->flush();
    }
}