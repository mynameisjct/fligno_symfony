<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProfileRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\FileUploader;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class RegisterController extends AbstractController{

    private $entityManager;
    private $encoder;
    private $profile;
    private $fileLoader;
    protected $container;
    protected $path;

    public function __construct(Container $container,EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, ProfileRepository $profile, FileUploader $loader){
        $this->entityManager = $manager;
        $this->encoder = $encoder;
        $this->profile = $profile;
        $this->fileLoader = $loader;
        $this->container = $container;
        $this->path = $this->container->getParameter('upload_dir');
    }

    /**
     * @Route("/register", name="app_register_user")
     */
    public function register(Request $req) : Response{
        $data = json_decode($req->getContent(), true);

        # get credentials to check existing email, returns array of boolean, user,  [boolean, pp_path, cp_path]
        $isExist = $this->getCredentials($data);

        # validate email format
        if($this->emailFormatValidation($data['email'])){
            if(!$isExist[0]){
                $response = $this->saveProfile($data,$isExist);
                if(is_bool($response)){
                    return $this->json(['message'=> 'Looks nice!', 'error' => '']);
                }else
                    return $this->json(['message'=> '','error' => $response]);
            }else
                return $this->json(['message'=> '','error' =>$isExist[1]]);
        }else
            return $this->json(['message'=> '','error' =>'Invalid Email Address format.']);
    }

    public function emailFormatValidation($email){
  
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }else
            return true;
    }

    public function getCredentials($data){
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if($user){
            # existing user
            return [true,'Email Address already existed! Please try again with another email.'];
        }else{
            # non-existing user
            $newUser = $this->saveUser($data);

            # upload pictures
            $path = $this->uploadAction($data,$newUser); # returns an arry of boolean, profile_picture_path, cover_photo_path
           
            return [false,$newUser,$path[0][1],$path[0][2]];
        }
    }

    public function saveUser($data){
        $user = new User();
        $user->setEmail($data['email']);
        $user->setName($data['name']);
        $encoded = $this->encoder->encodePassword($user,$data['password']);
        $user->setPassword($encoded);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function saveProfile($data,$user){
        try{

            // perform update instead
            $this->profile->insert($data,$user);

            /*
                $user = new User();
                $user->setEmail($data['email']);
                $encoded = $this->encoder->encodePassword($user, $data['password']);
                $user->setPassword($encoded);
                $user->setName($data['nickName']);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            */

            return true;
        }catch(Exception $exception){
            return $exception->getMessage();
        }
    }

    public function uploadAction($data,$user){
        
        // upload($path, $blob, $extension, $user, $imageType)
        $uploadProfilePicture = $this->fileLoader->upload($this->path,$data,$user);

        # returns boolean-profile_picture_path-cover_photo_path
        return [$uploadProfilePicture];
    }
}