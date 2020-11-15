<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
