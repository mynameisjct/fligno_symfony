<?php

namespace App\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Repository\UserRepository;

class EmailController extends AbstractController{

    private $userRepo;
    private $encoder;
    private $mailer;
    private $error;
    private $userData;

    public function __construct(\Swift_Mailer $mailer, UserPasswordEncoderInterface $encoder, UserRepository $user){
        $this->mailer = $mailer;
        $this->userRepo = $user;
        $this->encoder = $encoder;
    }

    /**
     * @Route("/resetpassword/{email}", name="app_resetpassword", methods={"POST"})
     */

     public function mail(string $email){
        $newPassword = $this->generateNewPassword();

        try{
            if(!$this->checkEmail($email)){
                throw new \Exception($email . " Does not exist!");
            }
            # encode password
            $encodedPassword = $this->encoder->encodePassword($this->userData,$newPassword);
            
            # update password in Database
            $this->userRepo->resetPassword($this->userData,$encodedPassword);

            # send email
            $message = (new \Swift_Message('Forgot Password'))
                    ->setFrom('zxcv12.john@gmail.com')
                    ->setTo($email)
                    ->setBody("You received this email because someone requested to reset this accounts' password. Use password generated here:  ".$newPassword. " and login to the app to proceed.");

            $this->mailer->send($message);

            return $this->json(['message'=>'Sent!']);
        }catch(\Exception $er){
            return $this->json(['message' => $er->getMessage()]);
        }        
     }

     public function generateNewPassword(){
         $charList = "QWERTYUIOPASDFGHJKLZXCVBNMmnbvcxzlkjhgfdsapoiuytrewq";

         $newpassword = substr(str_shuffle(str_repeat($charList, mt_rand(1,12))),1,12);

         return $newpassword;
     }

     public function checkEmail($email){
        $user = $this->userRepo->findOneBy(['email' => $email]);
       
        if (!$user) {
            return false;
        }else{
            $this->userData = $user;
            return true;
        }
     }
}