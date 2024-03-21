<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        public UserPasswordHasherInterface $userPasswordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setUsername('adminhajeddine');
        $admin->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $hashedAdminPassword = $this->userPasswordHasher->hashPassword($admin, 'password_adminhajeddine');
        $admin->setPassword($hashedAdminPassword);
        $manager->persist($admin);

        $user = new User();
        $user->setUsername('useraphina');
        $user->setRoles(['ROLE_USER']);
        $hashedUserPassword = $this->userPasswordHasher->hashPassword($user, 'password_useraphina');
        $user->setPassword($hashedUserPassword);
        $manager->persist($user);

        $manager->flush();
    }
}
