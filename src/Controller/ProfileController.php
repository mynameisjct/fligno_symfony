<?php

namespace App\Controller;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

# required for upload
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use App\Service\FileUploader;

use App\Repository\ProfileRepository;

class ProfileController extends AbstractController
{
    private $entityManager;
    private $userRepo;
    protected $container;
    protected $path;
    private $profile_repo;

    public function __construct(EntityManagerInterface $manager, UserRepository $userRepo, Container $container,ProfileRepository $prof){
        $this->container = $container;
        $this->entityManager = $manager;
        $this->userRepo = $userRepo;
        $this->path = $this->container->getParameter('upload_dir');
        $this->profile_repo = $prof;
    }

    /**
     * @Route("/profile", name="app_profile")
     */
    public function index()
    {
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
        ]);
    }

    /**
     * @Route("/admin", name="app_profile_admin")
     */
    public function admin(AuthorizationCheckerInterface $roleChecker){
        if(false === $roleChecker->isGranted('ROLE_ADMIN')){
            return $this->json(["data" => "Access Denied"]);
        }else{
            return $this->json(["data" => "Hello, this is ADMIN page."]);
        }
    }

    /**
     * @Route("/profile/picture_upload", name="profile_picture_upload")
     */
    public function uploadAction(Request $request, FileUploader $loader) : Response{

        # file was embedded in the response body, json format
        // $data = json_decode($request->getContent(), true);

        $imageType = $_POST['type'];
        $email = $_POST['email'];
        $file_extension = $_POST['file_extension'];
        $file = $_POST['uploaded_file'];

        if(empty($file)){
            return $this->json(['error' => 'No file specified to upload.','message' => '']);
        }

        # get user id based from email and verify to proceed
        $user = $this->userRepo->findIdByEmail($email);
        if($user !== null){
            # perform upload
            # call upload
            $result = $loader->upload($this->path,$file,$file_extension,$user,$imageType);

            if($result[0]){
                return $this->json(['error' => '','message' => 'Picture Uploaded Successfully!']);
            }else
                return $this->json(['error' => $result[1],'message' => '']);
        }else{
            return $this->json(['error' => 'User is not verified. Please login.','message' => '']);
        }
    }

    /**
     * @Route("/profile/all", name="app_select_profiles")
     */
    public function profiles(): Response{
        return $this->json($this->profile_repo->select());
    }

    /**
     * @Route("/delete/profile", name="app_delete_profile")
     */
    public function delete(Request $request) : Response{

        $data = json_decode($request->getContent(),true);
        $message = '';
        $error = '';

        $i = 0;
        $size = count($data) - 1;
    
        while($i <= $size){
            $userid = $this->userRepo->findIdById($data[$i]);

            if(!$userid !== null){
                $this->userRepo->delete($userid);
                $message = "Successfully deleted!";
            }else{
                $message = 'Error while delete, user not found!';
            }

            $i++;
        }

        return $this->json(['message'=>$message,'error'=>$error]);
    }
}

// $this->userRepo = $userRepo;
// $this->profile_repo = $prof;