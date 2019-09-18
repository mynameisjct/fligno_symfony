<?php

namespace App\Repository;

use App\Entity\Profile;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;

/**
 * @method Profile|null find($id, $lockMode = null, $lockVersion = null)
 * @method Profile|null findOneBy(array $criteria, array $orderBy = null)
 * @method Profile[]    findAll()
 * @method Profile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfileRepository extends ServiceEntityRepository
{
    private $entityManager;

    public function __construct(ManagerRegistry $registry,EntityManagerInterface $entity)
    {
        parent::__construct($registry, Profile::class);
        
        $this->entityManager = $entity;
    }

    // /**
    //  * @return Profile[] Returns an array of Profile objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    
    public function findOneBySomeField($user): ?Profile
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :val')
            ->setParameter('val', $user->getId())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /* this is for the saving profile form */
    public function rewrite($data,$user)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $q = $qb->update(Profile::class, 'p')
                ->set('p.firstname', '?1')
                ->set('p.middlename', '?2')
                ->set('p.lastname', '?3')   
                ->set('p.pp_path', '?4')
                ->set('p.pp_cover_path', '?5')
                ->set('p.description', '?6')
                ->where('p.user = ?7')
                ->setParameter(1 ,$data['firstname'])
                ->setParameter(2 ,$data['middlename'])
                ->setParameter(3 ,$data['lastname'])
                ->setParameter(4 ,$user[2])
                ->setParameter(5 ,$user[3])
                ->setParameter(6 ,$data['description'])
                ->setParameter(7 ,$user[1])
                ->getQuery();
     
        return $q->execute(); 
    }

    public function update($path,$user)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $q = $qb->update(Profile::class, 'p')
                ->set('p.pp_path', '?1')
                ->where('p.user = ?2')
                ->setParameter(1 ,$path)
                ->setParameter(2 ,$user)
                ->getQuery();
     
        return $q->execute(); 

        // return $this->createQueryBuilder('p')
        //             ->update(Profile::class, 'p')
        //             ->set('p.pp_path', $path)
        //             ->where('p.user = ?2')
        //             ->setParameter(2 ,$user)
        //             ->getQuery()
        //             ->execute()
        //         ;
    }
    
    public function insert($data,$user): ?Profile{
        $profile = new Profile();
        $profile->setFirstname($data['firstname']);
        $profile->setMiddlename($data['middlename']);
        $profile->setLastname($data['lastname']);
        $profile->setDescription($data['description']);
        $profile->setPpCoverPath($user[2]);
        $profile->setPpPath($user[3]);
        $profile->setUser($user[1]);

        $this->entityManager->persist($profile);
        $this->entityManager->flush();

        return $profile;
    }

    public function select(){
        $qb = $this->entityManager->createQuery(
            'SELECT p.lastname,p.firstname,p.middlename,p.description,p.pp_path, p.pp_cover_path,u.email,u.id 
             FROM App\Entity\User u 
             JOIN App\Entity\Profile p 
             where p.user = u.id'
        );

        $res = $qb->getResult();

        return $res;
    }

    public function getUserByEmail($email){
        $qb =  $this->createQueryBuilder('p');

                    $qb->select('p.id,p.lastname, p.middlename, p.firstname, p.description,u.id as userid')
                    ->leftJoin(User::class, 'u', Expr\Join::WITH, $qb->expr()->eq('u.id', 'p.user'))
                    ->where('u.email = ?1')
                    ->setParameter(1,$email)
        ;

        return $qb->getQuery()->getOneOrNullResult();
        // echo $qb; // to view query builder
    }

    public function updateProfile($data,$user)
    {
        $qb = $this->createQueryBuilder('p');

             $qb->update()
                ->set('p.firstname', '?1')
                ->set('p.middlename', '?2')
                ->set('p.lastname', '?3')   
                // ->set('p.pp_path', '?4')
                // ->set('p.pp_cover_path', '?5')
                ->set('p.description', '?6')
                ->where('p.user = ?7')
                ->setParameter(1 ,$data['firstname'])
                ->setParameter(2 ,$data['middlename'])
                ->setParameter(3 ,$data['lastname'])
                // ->setParameter(4 ,$user[2])
                // ->setParameter(5 ,$user[3])
                ->setParameter(6 ,$data['description'])
                ->setParameter(7 ,$user)
        ;

        return $qb->getQuery()->getOneOrNullResult();

    }

    public function saveProfile($data,$user){
        $profile = new Profile();
        $profile->setFirstname($data['firstname']);
        $profile->setMiddlename($data['middlename']);
        $profile->setLastname($data['lastname']);
        $profile->setDescription($data['description']);
        $profile->setPpCoverPath('');
        $profile->setPpPath('');
        $profile->setUser($user);

        $this->entityManager->persist($profile);
        $this->entityManager->flush();

        return $profile;
    }
       
    
}
