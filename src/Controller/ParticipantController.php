<?php

namespace App\Controller;

use App\Entity\Campagne;
use App\Entity\Commentary;
use App\Entity\Participant;
use App\Entity\Participation;
use App\Security\FacebookAuthenticator;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends ShopponController
{
    /**
     * @Route("/offre/{id}", name="viewCampagne", methods="get")
     */
    public function viewCampagne($id,EntityManagerInterface $entityManager, Request $request): Response
    {
        $commercantReponse = false;
        $campagne = $entityManager->getRepository(Campagne::class)->find($id);
        $backUrl = $request->headers->get('referer');
        $shareLink = $this->generateUrl('viewCampagne',['id' => $campagne->getId()],UrlGeneratorInterface::ABSOLUTE_URL);
        $commentaries = $entityManager->getRepository(Commentary::class)->findBy(['linkedCommentary' => null , "campagne" => $campagne->getId()],null,4);
        foreach ($campagne->getCommentaries() as $commentaire){
            if ($commentaire->getUser() == $campagne->getCommercant()){
                $commercantReponse = true;
            }
        }
        if($campagne){
            return $this->render('mvp/view.html.twig', [
                'campagne' => $campagne,
                'backurl' => $backUrl,
                'shareLink' => $shareLink,
                'comments' => $commentaries,
                'commercantReponse' => $commercantReponse
            ]);
        }
        else{
            return new Response("Cette page n'existe pas");
        }

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
    public function createParticipation(Request $request, EntityManagerInterface $entityManager, MailService $mailService)
    {
        $campagne = $entityManager->getRepository(Campagne::class)->find($request->get('idCampagne'));
        $quantity = $request->get("quantity");
        $user = $this->getUser();
        $participation = new Participation();
        $participation->setQuantity($quantity);
        $participation->setCampagne($campagne)->setParticipant($user);
        $entityManager->persist($participation);
        $participation->setPaid(false)->setOrderId("noOrderId");
        $entityManager->flush();
        $response = $mailService->mailConfirmation($participation,$this->renderView('mvp/mailConfirmation.html.twig'));
        if($response == "0"){
            return new Response($this->generateUrl('participationEffectue',['id' => $participation->getId()]));
        }
        else return new Response("L'envoi du mail à échoué",500);
    }

    /**
     * @Route("/offre/participation/effectue", name="participationEffectue")
     */
    public function participationEffectue(Request $request, EntityManagerInterface $entityManager , $id = null): Response
    {
        if($request->get('cancelled')){
            $this->addFlash('danger',"Le paiement n'a pas abouti, veuillez recommencer");
        }

        if(is_null($id)){
            $id = $request->get('id');
        }
        $participation = $entityManager->getRepository(Participation::class)->find($id);
        $idCampagne = $participation->getId();
        $url = $shareLink = $this->generateUrl('viewCampagne',['id' => $idCampagne],UrlGeneratorInterface::ABSOLUTE_URL);
        return $this->render('mvp/payerParticipation.html.twig',[
            'participation' => $participation,
            'shareLink' => $url
        ]);
    }

    /**
     * @Route("/register/participant", name="registerParticipant")
     */
    public function registerParticipant(): Response
    {
        return $this->render('security/registerParticipant.html.twig', [
        ]);
    }


    /**
     * @Route("/register/participant/create", name="createParticipant")
     */
    public function createParticipant(Request $request,FacebookAuthenticator $facebookAuthenticator, EntityManagerInterface $entityManager): Response
    {
        $entity = $entityManager->getRepository(Participant::class)->findOneBy(['email' => $request->get('email')]);
        if(!is_null($entity)){
            $this->addFlash('warning','Il semble que vous ayez déjà un compte, essayez de vous connecter à la place');
            $this->redirect($request->headers->get('referer'));
        }
        else{
            $participant = new Participant();
            $participant->setUsername($request->get('pseudo'))
                ->setNom($request->get('nom'))
                ->setPrenom($request->get('prenom'))
                ->setPassword($facebookAuthenticator->encodePassword($participant,$request->get('mdp')))
                ->setEmail($request->get('email'))
                ->setFacebookId('0')
                ->setPictureUrl('0')
                ->setVerified(false);
            $entityManager->persist($participant);
            $entityManager->flush();

            $token = new UsernamePasswordToken($participant, $participant->getPassword(), 'public', $participant->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->addFlash('success', "Bienvenu chez Shoppon " . $participant->getUsername() . " ! Vous pouvez maintenant prendre place à des offres groupées");
            return $this->redirectToRoute('allCampagne');
        }

        return $this->render('security/registerParticipant.html.twig', [
        ]);
    }

}