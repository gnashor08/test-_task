<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminUserFixtures extends Fixture
{
    public function __construct(protected UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager, ): void
    {
        $user = new User();
        $user->setFirstName('Karl');
        $user->setLastName('Franz');
        $user->setSecondName('Emperor');
        $user->setBirthdayDate(new \DateTime('1990-01-01'));
        $user->setEmail('karlfranz@gmail.com');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'galmaraz'));

        $manager->persist($user);
        $manager->flush();
    }
}
