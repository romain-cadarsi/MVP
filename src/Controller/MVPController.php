<?php

namespace App\Controller;

use App\Entity\Campagne;
use App\Entity\Commercant;
use App\Repository\CampagneRepository;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MVPController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        return $this->render('mvp/home.html.twig', [
        ]);
    }

    /**
     * @Route("/formCampagne", name="formCampagne")
     */
    public function formCampangne(): Response
    {
        return $this->render('mvp/createCampagne.html.twig', [
        ]);
    }




    /**
     * @Route("/flyer/{id}", name="flyer", methods="get")
     */
    public function flyer($id,EntityManagerInterface  $entityManager): Response
    {
        $campagne = $entityManager->getRepository(Campagne::class)->find($id);
        return $this->render('mvp/flyer.html.twig', [
            'campagne' => $campagne
        ]);
    }

    /**
     * @Route("/creerCampagne", name="creerCampagne")
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
                }
            }
            else{
                if(!is_null($file)){
                    $campagne->addImagesProduit($imageService->saveToDisk($file));
                }
            }
        }

        $entityManager->persist($campagne);
        $entityManager->flush();

        if($request->isXmlHttpRequest()){
            return $this->render('mvp/shareAJAXpage.html.twig',[ 'campagne' => $campagne]);
        }
        else{
            return $this->shareCampagne($entityManager,$campagne->getId(),$request);
        }

    }

    /**
     * @Route("/shareCampagne", name="shareCampagne")
     */
    public function shareCampagne(EntityManagerInterface $entityManager,$campagne = null,Request $request): Response
    {
        if(is_null($campagne)){
            $campagne = $request->get('id');
        }
        $campagne = $entityManager->getRepository(Campagne::class)->find($campagne);

        return $this->render('mvp/share.html.twig', [
            'campagne' => $campagne
        ]);
    }

    /**
     * @Route("/loader", name="loader")
     */
    public function loader(): Response
    {

        return $this->render('mvp/loader.html.twig', [
        ]);
    }



    /**
     * @Route("/viewCampagne/{id}", name="viewCampagne", methods="get")
     */
    public function viewCampagne($id,EntityManagerInterface $entityManager): Response
    {
        $campagne = $entityManager->getRepository(Campagne::class)->find($id);
        if($campagne){
            return $this->render('mvp/view.html.twig', [
                'campagne' => $campagne,
            ]);
        }
        else{
            return new Response("Cette page n'existe pas");
        }

    }

    /**
     * @Route("/allCampagne", name="allCampagne")
     */
    public function allCampagne(CampagneRepository $campagneRepository): Response
    {
        $campagnes = $campagneRepository->findAll();
        return $this->render('mvp/allCampagne.html.twig', [
            'campagnes' => $campagnes
        ]);
    }
}
