<?php

namespace App\Controller;

use App\Entity\Campagne;
use App\Entity\Commercant;
use App\Entity\Participant;
use App\Entity\Participation;
use App\Repository\CampagneRepository;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
            return new Response(
                $this->generateUrl('shareCampagne', ['id' => $campagne->getId()],UrlGeneratorInterface::ABSOLUTE_URL)
            );
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
            'link' => $this->generateUrl('viewCampagne', ['id' => $campagne->getId()],UrlGeneratorInterface::ABSOLUTE_URL),
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

    /**
     * @Route("/xhr/helpBoost", name="helpBoost")
     */
    public function helpBoost(): Response
    {

        return $this->render('mvp/boosteTaCampagne.html.twig', [
        ]);
    }

    /**
     * @Route("/xhr/participationPage", name="participationPage")
     */
    public function participationPage(): Response
    {

        return $this->render('mvp/participer.html.twig', [
        ]);
    }

    /**
     * @Route("/xhr/createParticipation", name="createParticipation")
     */
    public function createParticipation(Request $request, EntityManagerInterface $entityManager)
    {
        $campagne = $entityManager->getRepository(Campagne::class)->find($request->get('idCampagne'));
        $client = $entityManager->getRepository(Participant::class)->findOneBy(['mail' => $request->get('email')]);
        if(!$client){
            $client = new Participant();
            $client->setTelephone($request->get('telephone'))
                ->setMail($request->get('email'));
            $entityManager->persist($client);
        }
        $participation = new Participation();
        $participation->setCampagne($campagne)->setParticipant($client);
        $entityManager->persist($participation);
        $entityManager->flush();


        return new Response($this->generateUrl('participationEffectue',['id' => $participation->getId()]));

    }

    /**
     * @Route("/participationEffectue", name="participationEffectue")
     */
    public function participationEffectue(Request $request, EntityManagerInterface $entityManager , $id = null): Response
    {
        if(is_null($id)){
           $id = $request->get('id');
        }
        $participation = $entityManager->getRepository(Participation::class)->find($id);

        return $this->render('htmlTemplate/page4.html.twig', [
            'participation' => $participation
        ]);


    }


}
