<?php

namespace App\Controller;

use App\Entity\Campagne;
use App\Entity\Commercant;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommercantController extends ShopponController
{

    /**
     * @Route("/commercant", name="landingCommercant")
     */
    public function index(): Response
    {
        return $this->render('mvp/home.html.twig', [
        ]);
    }


    /**
     * @Route("/campagne/creation", name="formCampagne")
     */
    public function formCampangne(): Response
    {
        return $this->render('mvp/createCampagne.html.twig', [
        ]);
    }

    /**
     * @Route("/campagne/flyer/{id}", name="flyer", methods="get")
     */
    public function flyer($id,EntityManagerInterface  $entityManager): Response
    {
        $campagne = $entityManager->getRepository(Campagne::class)->find($id);
        return $this->render('mvp/flyer.html.twig', [
            'campagne' => $campagne
        ]);
    }

    /**
     * @Route("/campagne/creer", name="creerCampagne")
     */
    public function creerCampagne(Request $request, ImageService $imageService,EntityManagerInterface $entityManager): Response
    {
        $campagne = new Campagne();
        $campagne->setDatetimeRetrait(date_create_from_format('d/m/Y H:m',$request->get('jourRetrait')))
            ->setDebutCampagne(new \DateTime('now'))
            ->setDescription($request->get('description'))
            ->setDureeCampagne($request->get('dureeCampagne'))
            ->setNombreParticipants($request->get('participants'))
            ->setEmail($request->get('email'))
            ->setTelephone($request->get('telephone'))
            ->setMoyen($request->get('methode'))
            ->setPrixPromotion($request->get('prixPromotion'))
            ->setValeurProduit($request->get('valeur'))
            ->setNomVendeur($request->get('vendeur'))
            ->setTitre($request->get('titre'))
            ->setVille('Mèze')
            ->setCommercant($entityManager->getRepository(Commercant::class)->findOneBy(['email' => $this->getUser()->getUsername()]))
        ;
        $index = 0;
        foreach ($request->files as $file){
            if($index == 0){
                if(!is_null($file)){
                    $campagne->setLogo($imageService->saveToDisk($file));
                    $index += 1 ;
                }
            }
            else{
                if(!is_null($file)){
                    $campagne->addImagesAdditionnelle($imageService->saveAdditionnalImageToDisk($file,$campagne));
                    $index += 1;
                }
            }
        }
        $entityManager->persist($campagne);
        $entityManager->flush();

        if($request->isXmlHttpRequest()){
            return new Response(
                $this->generateUrl('shareCampagne', ['id' => $campagne->getId()],UrlGeneratorInterface::ABSOLUTE_URL)
            );
        }
        else{
            return $this->shareCampagne($entityManager,$campagne->getId(),$request);
        }

    }

    /**
     * @Route("/campagne/partager", name="shareCampagne")
     */
    public function shareCampagne(EntityManagerInterface $entityManager,$campagne = null,Request $request): Response
    {
        if(is_null($campagne)){
            $campagne = $request->get('id');
        }
        $campagne = $entityManager->getRepository(Campagne::class)->find($campagne);

        return $this->render('mvp/share.html.twig', [
            'link' => $this->generateUrl('viewCampagne', ['id' => $campagne->getId()],UrlGeneratorInterface::ABSOLUTE_URL),
            'campagne' => $campagne
        ]);
    }

}