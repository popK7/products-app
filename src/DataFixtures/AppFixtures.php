<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Product;
use App\Enum\InventoryStatus;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        // Create 100 products

        for ($i = 0; $i < 100; $i++) {

            $product = new Product;

            $product->setCode($faker->uuid);
            $product->setName($faker->name);
            $product->setDescription($faker->realText($faker->numberBetween(10, 20)));
            $product->setImage($faker->imageUrl(640, 480));
            $product->setCategory($faker->domainWord());
            $product->setPrice($faker->randomNumber(2));
            $product->setQuantity($faker->randomNumber(2));
            $product->setInternalReference($faker->uuid);
            $product->setShellId($faker->randomNumber(2));
            $product->setInventoryStatus(InventoryStatus::INSTOCK);
            $product->setRating($faker->randomNumber(1));

            $manager->persist($product);
        }

        $manager->flush();
    }
}
