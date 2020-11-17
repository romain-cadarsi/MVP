<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;

class ImageService
{
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    function saveToDisk(UploadedFile $image) {
        $uploadDirectory = 'uploads/images';
        $path = $this->kernel->getProjectDir().'/public/' . $uploadDirectory;
        $imageName = uniqid() . '.' . $image->guessExtension();
        $image->move($path, $imageName);
        $pattern = "/^.*?((?:\w+)+)$/i";
        preg_match($pattern, "$path/$imageName",$matches); // Outputs 1
        /**if($matches[1] != "webp"){
            if(!file_exists("$path/$imageName.webp")){
                if($matches[1] == "jpg" || $matches[1] == "JPEG" || $matches[1] == "JPG"){
                    $matches[1] = "jpeg";
                }
                if(method_exists($this,'imagecreatefrom' . $matches[1])){
                    $img = call_user_func('imagecreatefrom' . $matches[1],"$path/$imageName");
                    imagepalettetotruecolor($img);
                    imagealphablending($img,true);
                    imagesavealpha($img,true);
                    imagewebp($img, "$path/$imageName.webp",80);
                    $imageName = $imageName.".webp";
                }
                else echo ('createimagefrom' . $matches[1] . "  function DONT EXIST , image used without rescaling<br>");
            }
        }
        **/
        return $imageName;
    }

    function searchFor($fileName){

    }
    function createWebpEmbedImages(){
        $uploadDirectory = 'uploads/images/';
        $path = $this->kernel->getProjectDir().'/public/' . $uploadDirectory;
        $files = array_diff(scandir($path), array('..', '.'));
        $pattern = "/^.*?((?:\w+)+)$/i";
        $imageScaled = 0;
        $alreadyScaled = 0;
        foreach ($files as $file){
            preg_match($pattern, $path . $file,$matches); // Outputs 1
            if($matches[1] != "webp"){
                if(!file_exists($path . $file . ".webp")){
                    if($matches[1] == "jpg" || $matches[1] == "JPEG" || $matches[1] == "JPG"){
                        $matches[1] = "jpeg";
                    }
                    if(method_exists($this,'imagecreatefrom' . $matches[1])){
                        $img = call_user_func('imagecreatefrom' . $matches[1],$path . $file);
                        imagepalettetotruecolor($img);
                        imagealphablending($img,true);
                        imagesavealpha($img,true);
                        imagewebp($img, $path. $file . ".webp",80);
                        $imageScaled += 1;
                    }
                    else echo ('createimagefrom' . $matches[1] . " DONT EXIST <br>");
                }
            }
            else{
                $alreadyScaled += 1;
            }

        }
        echo "$imageScaled images scaled";
        echo "$alreadyScaled images already scaled";



    }

    function imagecreatefrompng($filename){
        return imagecreatefrompng($filename);
    }
    function imagecreatefromwebp($filename){
        return imagecreatefromwebp($filename);
    }
    function imagecreatefromjpeg($filename){
        return imagecreatefromjpg($filename);
    }

    function basenameWithoutExtension($basename){
        return $basename;
    }
}