<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Repository\UserRepository;

class SecurityController extends BaseUserController
{
    private $userRepo;

    /**
     * @Route("/security", name="security")
     */
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SecurityController.php',
        ]);
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //    $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();


        # when returning response
        # returning last user username
        # return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);

        # return json instead
        $err = '';
        if(isset($error)){
            $err = $error->getMessage();
        }
   
         return $this->json(['last_username' => $lastUsername, 'error' => $err]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }

    /**
     * @Route("/validate/email", name="app_email_validation")
     */

    public function validateEmail(Request $req, UserRepository $userRepo) : Response{
        $data = json_decode($req->getContent(), true);

        if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
            return $this->json(['validation' => false]);
        }else{
            // check for existing email
            $email = $userRepo->findIdByEmail($data['email']);
            if($email !== null){
                //existing
                return $this->json(['validation' => false]);
            }else{
                return $this->json(['validation' => true]);
            }
            
        }
            
    }

}
