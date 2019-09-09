<?php
namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UploadHelper extends AbstractController{

    protected $container;
    protected $path;
    private $user;
    private $urlGenerator;

    public function __construct(Container $container, UserRepository $user, UrlGeneratorInterface $generator){
        $this->container = $container;
        $this->path = $this->container->getParameter('upload_dir');
        $this->user = $user;
        $this->urlGenerator = $generator;
    }

    /**
     * @Route("/download/profile/{id}", name="app_image_download_pp", methods={"GET"})
     */
    public function getProfilePP(int $id){

        $file = $this->path.'/'.$id.'/profile_picture.jpeg'; # profile picture default filename
                return new BinaryFileResponse($file);

        /*
        if(!isset($data['email']) || !empty($data['email'])){

            $user = $this->user->findIdByEmail($data['email']);

            if($user !== null){
                $userid = $user->getId();
                
                $file = $this->path.'/'.$id.'/profile_picture.jpeg'; # profile picture default filename
                return new BinaryFileResponse($file);
                
            }else{

                //return BinaryFileReponse an image from the internet that is empty or default image
                return false;
            }
    
        }  
        */
    }

    /**
     * @Route("/download/cover/{id}", name="app_image_download_cp", methods={"GET"})
     */
    public function getCoverPhoto(int $id){
        $file = $this->path.'/'.$id.'/cover_photo.jpeg'; # cover photo default filename
                return new BinaryFileResponse($file);
    }

     /**
     * @Route("/get/profile_base_path/{id}", name="profile_base_path", methods={"GET"})
     */
    public function getBasePathProfilePicture(int $id): Response{
        return $this->json(['path'=>$this->urlGenerator->generate('app_image_download_pp',['id'=>$id])]);
    }

    /**
     * @Route("/get/cover_base_path/{id}", name="cover_base_path", methods={"GET"})
     */
    public function getBasePathCoverPhoto(int $id): Response{
        return $this->json(['path'=>$this->urlGenerator->generate('app_image_download_cp',['id'=>$id])]);
    }

}