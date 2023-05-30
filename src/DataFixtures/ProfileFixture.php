<?php

namespace App\DataFixtures;

use App\Entity\Profile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProfileFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $profile = new Profile();
        $profile->setRs('GitHub');
        $profile->setUrl('https://github.com/RakoGa');

        $profile1 = new Profile();
        $profile1->setRs('LinkedIn');
        $profile1->setUrl('https://www.linkedin.com/in/gaetan-rakotomalala/');

        $manager->persist($profile);
        $manager->persist($profile1);
        $manager->flush();
    }
}
