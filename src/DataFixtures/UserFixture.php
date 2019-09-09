<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends BaseFixture
{
    private $encoder;
    public function __construct(UserPasswordEncoderInterface $encoder){
        $this->encoder = $encoder;
    }

    protected function loadData(ObjectManager $manager){

        $this->createMany(10,'main_users', function($i){
            $user = new User();
            $user->setEmail(sprintf('spacebar%d@example.com', $i));
            $user->setName($this->faker->name);
            $encoded = $this->encoder->encodePassword($user,'123');
            $user->setPassword($encoded);

            return $user;
        });

        $this->createMany(3,'admin_users', function($i){
            $user = new User();
            $user->setEmail(sprintf('keyboard%d@example.com', $i));
            $user->setName($this->faker->name);
            $encoded = $this->encoder->encodePassword($user,'123');
            $user->setPassword($encoded);
            $user->setRoles(['ROLE_ADMIN']);

            return $user;
        });

        $manager->flush();
    }

    // public function load(ObjectManager $manager)
    // {
    //     // $product = new Product();
    //     // $manager->persist($product);

    //     $manager->flush();
    // }
}
