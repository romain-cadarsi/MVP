<?php

namespace App\Controller;

use App\Entity\Campagne;
use App\Entity\Commentary;
use App\Entity\Commercant;
use App\Entity\Participant;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;

class CommentaryController extends ShopponController
{
    /**
     * @Route("/offre/commentaire/creer", name="createCommentary")
     */
    public function createCommentary( EntityManagerInterface $entityManager,Request $request,MailService $mailService)
    {

        $commentary = new Commentary();
        $campagne = $entityManager->getRepository(Campagne::class)->find($request->get('campagneId'));
        if($request->get('participantId')){
            $commentary->setParticipant($entityManager->getRepository(Participant::class)->find($request->get('participantId')));
        }
        else{
            $commentary->setCommercant($entityManager->getRepository(Commercant::class)->find($request->get('commercantId')));
        }
        if($request->get('linkedCommentary')){
            $linkedCommentary = $entityManager->getRepository(Commentary::class)->find($request->get('linkedCommentary'));
            $commentary->setLinkedCommentary($linkedCommentary);
            $mailService->reponseQuestion($commentary->getUser(),$campagne,$this->generateUrl('viewCampagne',['id' => $campagne->getId()],UrlGeneratorInterface::ABSOLUTE_URL));

        }
        $commentary->setCampagne($campagne)
            ->setCommentary($request->get('comment'))
            ->setDatetime(new \DateTime());
        $entityManager->persist($commentary);
        $entityManager->flush();
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/offre/commentaire", name="allCommentary")
     */
    public function allCommentary(EntityManagerInterface $entityManager, Request $request): Response
    {
        $campagne = $entityManager->getRepository(Campagne::class)->find($request->get('idCampagne'));
        $commentaries = $entityManager->getRepository(Commentary::class)->findBy(['linkedCommentary' => null , "campagne" => $campagne->getId()]);
        if($campagne){
            return $this->render('mvp/component/allComments.html.twig', [
                'campagne' => $campagne,
                'comments' => $commentaries,
            ]);
        }

    }
}
