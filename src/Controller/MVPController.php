<?php

namespace App\Controller;

use App\Service\ImageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MVPController extends AbstractController
{
    /**
     * @Route("/", name="mvp")
     */
    public function index(): Response
    {
        return $this->render('mvp/index.html.twig', [
            'controller_name' => 'MVPController',
        ]);
    }

    /**
     * @Route("/flyer", name="flyer")
     */
    public function flyer(): Response
    {
        return $this->render('mvp/flyer.html.twig', [
        ]);
    }

    /**
     * @Route("/creerCampagne", name="creerCampagne")
     */
    public function creerCampagne(Request $request, ImageService $imageService): Response
    {
        $imageService->saveToDisk($request->files->get('image1'));
        dump($request);
    }
}
