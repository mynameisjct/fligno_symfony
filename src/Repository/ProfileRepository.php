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
        date_default_timezone_set("Asia/Manila");
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
        $profile->setDateCreated(new \DateTime('@'.strtotime('now')));
        $profile->setUser($user);

        $this->entityManager->persist($profile);
        $this->entityManager->flush();

        return $profile;
    }

    public function search($searchStr,$limit){
        $qb = $this->createQueryBuilder('p');

        return $this->createQueryBuilder('p')
            ->select('p.id,p.lastname,p.firstname,p.middlename,p.description,u.email')
            ->leftJoin(User::class, 'u', Expr\Join::WITH, $qb->expr()->eq('u.id', 'p.user')) // WITH is the ON in doctrine
            ->andWhere("lower(concat(p.lastname, ', ',p.firstname, ' ',p.middlename)) like lower(:toSearch)")
            ->setParameter('toSearch', '%'.$searchStr.'%')
            ->orderBy('p.lastname', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function yearlyStat($year){
        
        $jan = [$year.'-01-01',''];$feb = [$year.'-02-01',''];$mar = [$year.'-03-01',''];$apr = [$year.'-04-01',''];
        $may = [$year.'-05-01',''];$jun = [$year.'-06-01',''];$jul = [$year.'-07-01',''];$aug = [$year.'-08-01',''];
        $sep = [$year.'-09-01',''];$oct = [$year.'-10-01',''];$nov = [$year.'-11-01',''];$dec = [$year.'-12-01',''];

        $jan[1] = $this->getLastDate('01',$year);
        $feb[1] = $this->getLastDate('02',$year);
        $mar[1] = $this->getLastDate('03',$year);
        $apr[1] = $this->getLastDate('04',$year);
        $may[1] = $this->getLastDate('05',$year);
        $jun[1] = $this->getLastDate('06',$year);
        $jul[1] = $this->getLastDate('07',$year);
        $aug[1] = $this->getLastDate('08',$year);
        $sep[1] = $this->getLastDate('09',$year);
        $oct[1] = $this->getLastDate('10',$year);
        $nov[1] = $this->getLastDate('11',$year);
        $dec[1] = $this->getLastDate('12',$year);


        return $this->createQueryBuilder('p')
                    ->select('count(p.id) as index')
                    ->addSelect('(SELECT count(z.id) from App\Entity\Profile as z where z.date_created between :fm1 and :em1) as jan')
                    ->setParameter('fm1',$jan[0])
                    ->setParameter('em1',$jan[1])
                    ->addSelect('(SELECT count(x.id) from App\Entity\Profile as x where x.date_created between :fm2 and :em2) as feb')
                    ->setParameter('fm2',$feb[0])
                    ->setParameter('em2',$feb[1])
                    ->addSelect('(SELECT count(w.id) from App\Entity\Profile as w where w.date_created between :fm3 and :em3) as mar')
                    ->setParameter('fm3',$mar[0])
                    ->setParameter('em3',$mar[1])
                    ->addSelect('(SELECT count(r.id) from App\Entity\Profile as r where r.date_created between :fm4 and :em4) as apr')
                    ->setParameter('fm4',$apr[0])
                    ->setParameter('em4',$apr[1])
                    ->addSelect('(SELECT count(t.id) from App\Entity\Profile as t where t.date_created between :fm5 and :em5) as may')
                    ->setParameter('fm5',$may[0])
                    ->setParameter('em5',$may[1])
                    ->addSelect('(SELECT count(q.id) from App\Entity\Profile as q where q.date_created between :fm6 and :em6) as jun')
                    ->setParameter('fm6',$jun[0])
                    ->setParameter('em6',$jun[1])
                    ->addSelect('(SELECT count(b.id) from App\Entity\Profile as b where b.date_created between :fm7 and :em7) as jul')
                    ->setParameter('fm7',$jul[0])
                    ->setParameter('em7',$jul[1])
                    ->addSelect('(SELECT count(c.id) from App\Entity\Profile as c where c.date_created between :fm8 and :em8) as aug')
                    ->setParameter('fm8',$aug[0])
                    ->setParameter('em8',$aug[1])
                    ->addSelect('(SELECT count(v.id) from App\Entity\Profile as v where v.date_created between :fm9 and :em9) as sep')
                    ->setParameter('fm9',$sep[0])
                    ->setParameter('em9',$sep[1])
                    ->addSelect('(SELECT count(e.id) from App\Entity\Profile as e where e.date_created between :fm10 and :em10) as oct')
                    ->setParameter('fm10',$oct[0])
                    ->setParameter('em10',$oct[1])
                    ->addSelect('(SELECT count(i.id) from App\Entity\Profile as i where i.date_created between :fm11 and :em11) as nov')
                    ->setParameter('fm11',$nov[0])
                    ->setParameter('em11',$nov[1])
                    ->addSelect('(SELECT count(o.id) from App\Entity\Profile as o where o.date_created between :fm12 and :em12) as dec')
                    ->setParameter('fm12',$dec[0])
                    ->setParameter('em12',$dec[1])
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult()
                ;
    }

    private function getLastDate($monthname,$year){ //monthname in number

        $temp_date = $year."-".$monthname."-01";
        $r = date("Y-m-t", strtotime($temp_date));
        return $r;
    }
       
    
}
