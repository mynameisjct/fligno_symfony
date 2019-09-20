<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Profile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    private $manager;
    private $encoder;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entity, UserPasswordEncoderInterface $encoder)
    {
        parent::__construct($registry, User::class);
        $this->manager = $entity;
        $this->encoder = $encoder;
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findIdById($id): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findIdByEmail($email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :val')
            ->setParameter('val', $email)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getUserIdFromProfile($user): ?User{

        $qb = $this->createQueryBuilder('u')
                    ->select(['u'])
                    ->from('App\Entity\User', 'u')
                    ->leftJoin('App\Entity\Profile', 'p')
                    ->getQuery()
                    ->getOneOrNullResult();

        return $qb;
    }

    public function delete($user){
        return $this->createQueryBuilder('u')
                    ->delete()
                    ->where('u.id = :id')
                    ->setParameter('id', $user)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    public function save($data): ?User{
        $user = new User();
        $user->setEmail($data['email']);
        $encoded = $this->encoder->encodePassword($user,$data['password']);
        $user->setPassword($encoded);
        $user->setName('');

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;    
    }

    public function resetPassword($user, $password){
        $qb = $this->createQueryBuilder('u');

                $qb->update()
                    ->set('u.password', '?1')
                    ->where('u.id = ?2')
                    ->setParameter(1, $password)
                    ->setParameter(2, $user)
                    ->getQuery()
                    ->getOneOrNullResult()
        ;

        return $qb;
    }
}
