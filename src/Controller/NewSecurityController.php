<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class NewSecurityController extends BaseUserController
{

    private $entityManager;
    private $urlGenerator;
    private $passwordEncoder;
    private $error;
    private $user;
    private $success;
    
    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->passwordEncoder = $passwordEncoder;
        $this->user = [];
        $this->error = '';
        $this->success = false;
    }

    /**
     * @Route("/newlogin", name="new_app_login")
     */
    public function login(Request $request): Response
    {
        if($request->isMethod('POST')){
            // execute login controller
            $this->getCredentials($request);
        }else{
            // get last login details
        }

        

        return $this->json(['user' => !isset($this->user['user']) ? '' : $this->user['user'],'id'=> !isset($this->user['id']) ? '' : $this->user['id'], 'error'=>$this->error,'success'=>$this->success]);
    }

    public function getCredentials($request){
        $data = json_decode($request->getContent(), true);
        $credentials = [
            'email' => $data['email'], # $request->request->get('email'),
            'password' => $data['password'], # $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
       
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        $this->getUserDetails($credentials);
        return true;
    }

    public function getUserDetails($credentials)
    {       
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);
       
        if (!$user) {
            $this->error = 'Email address does not exist!';
            return true;
        }

        $this->checkCredentials($credentials, $user);
        return true;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // var_dump($credentials['password']);
         if(!$this->passwordEncoder->isPasswordValid($user, $credentials['password'])){
            $this->error = 'Invalid Credentials!';
            return true;
         }else{
             $this->user = ['user'=>$user->getEmail(),'id' => $user->getId()];
             $this->success = true;
         }

        //  $encoded = $this->passwordEncoder->encodePassword($user,$credentials['password']);
        //  echo 'password: '.$credentials['password'].'   ====> '.$encoded;
         $this->user = ['user'=>$user->getEmail(),'id' => $user->getId()];
         $this->success = true;
         return true;
    }

}
