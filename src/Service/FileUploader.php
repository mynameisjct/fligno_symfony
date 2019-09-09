<?php
namespace App\Service;

use App\Repository\ProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class FileUploader{
    private $uploadedFile;
    private $path;
    private $file;
    private $entityManager;
    private $profileRepo;
    
    public function __construct(Filesystem $file, EntityManagerInterface $manager, ProfileRepository $prof){
        $this->file = $file;
        $this->entityManager = $manager;
        $this->profileRepo = $prof;
    }

   public function upload($path, $data, $user){

        $dir_path = $path.'/'.$user->getId();
    /*
        if($imageType === 'pp'){
            $persistToDIRFile = $dir_path.'/profile_picture.'.$extension;
        }else{
            $persistToDIRFile = $dir_path.'/cover_photo.'.$extension;
        }
    */

        try{

            if(!$this->file->exists($dir_path)){
                $this->file->mkdir($dir_path);
            }

            $profilePicturePath = $dir_path.'/profile_picture.'.$data['pp']['file_extension'];
            $coverPhotoPath = $dir_path.'/cover_photo.'.$data['cp']['file_extension'];

            //save blob or base64 image data
            $this->file->dumpFile($profilePicturePath, file_get_contents($data['pp']['source']));
            $this->file->dumpFile($coverPhotoPath, file_get_contents($data['cp']['source']));
        
        /*
            //save path to database
            // check if has record else update
            $profile = $this->profileRepo->findOneBySomeField($user);
            if($profile){
                //perform update
                $update = $this->profileRepo->update($dir_path,$user);
                if($update === 1){
                    return [false,'Unable to perform Update'];
                }
            }else{
                // insert new
                $this->profileRepo->insert($dir_path,$user);
            }
        */

            // return path /download/profile/ or /download/cover/
            // or get using url generator
            $profilePicturePath = 'download/profile/'.$user->getId();
            $coverPhotoPath = 'download/cover/'.$user->getId();

            return [true,$profilePicturePath,$coverPhotoPath];

        }catch(IOExceptionInterface $err){
            return [false,$err->getMessage()];
        }
   }

}