<?php

namespace App\Controller;

use App\Entity\Campagne;
use App\Entity\Participation;
use App\Repository\CampagneRepository;
use App\Repository\CategorieRepository;
use App\Repository\MasterCategorieRepository;
use App\Service\MailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ShopponController extends AbstractController
{

    /**
     * @Route("/", name="home")
     */
    public function allCampagne(CampagneRepository $campagneRepository,CategorieRepository $categorieRepository, MasterCategorieRepository $masterCategorieRepository): Response
    {
        $mostAdvancedCampagne = $campagneRepository->getMostAdvancedCampagne();
        $categories = $categorieRepository->findBy([],["id" => 'DESC']);
        $masterCategories = $masterCategorieRepository->findBy([],['id' => 'DESC']);
        $mcs = (array_map(function ($mc) use ($campagneRepository,$masterCategorieRepository) {
            return ['masterCategory' => $mc,
                'advancedCampagne' => $campagneRepository->findBy(['id' => $masterCategorieRepository->getMostAdvancedCampagne($mc->getId())])];
        },$masterCategories));
        $campagnes = $campagneRepository->findAll();
        $campagnesValides = [];
        foreach ($campagnes as $campagne){
            if($campagne->isValid()){
                array_push($campagnesValides,$campagne);
            }
        }
        return $this->render('mvp/allCampagne.html.twig', [
            'mostAdvancedCampagne' => $mostAdvancedCampagne,
            'campagnes' => $campagnesValides,
            'categoriesBag' => array_chunk($categories,2),
            'masterCategories' => $mcs
        ]);

    }



    /**
     * @Route("/testMail", name="testMail")
     */
    public function testMail(MailService $mailService)
    {
        $participation = $this->getDoctrine()->getRepository(Participation::class)->find("59");
        $mailService->mailConfirmation($participation,$this->renderView('mvp/mailConfirmation.html.twig'));
    }

    /**
     * @Route("/search", name="search")
     */
    public function search(Request $request): Response
    {
        $word = $request->get('q');
        $campagnes = $this->getDoctrine()->getRepository(Campagne::class)->searchForKeyWords($word);
        return $this->render('mvp/search.html.twig',[
            'campagnes' => $campagnes,
            'word' => $word
        ]);
    }










}
