<?php

namespace App\Controller;

use App\Entity\Campagne;
use App\Entity\Commercant;
use App\Entity\Participant;
use App\Entity\Participation;
use App\Entity\User;
use App\Repository\CampagneRepository;
use App\Security\CommercantAuthenticator;
use App\Security\FacebookAuthenticator;
use App\Service\ImageService;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;

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
    public function viewCampagne($id,EntityManagerInterface $entityManager, Request $request): Response
    {
        $campagne = $entityManager->getRepository(Campagne::class)->find($id);
        $backUrl = $request->headers->get('referer');
        if($campagne){
            return $this->render('mvp/view.html.twig', [
                'campagne' => $campagne,
                'backurl' => $backUrl
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
        $campagnesValides = [];
        foreach ($campagnes as $campagne){
            if($campagne->isValid()){
                array_push($campagnesValides,$campagne);
            }
        }
        return $this->render('mvp/allCampagne.html.twig', [
            'campagnes' => $campagnesValides
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
    public function createParticipation(Request $request, EntityManagerInterface $entityManager, MailService $mailService)
    {
        $campagne = $entityManager->getRepository(Campagne::class)->find($request->get('idCampagne'));
        $quantity = $request->get("quantity");
        $user = $this->getUser();
        $participation = new Participation();
        $participation->setQuantity($quantity);
        $participation->setCampagne($campagne)->setParticipant($user);
        $entityManager->persist($participation);
        $entityManager->flush();
        $response = $mailService->mailConfirmation($participation,$this->renderView('mvp/mailConfirmation.html.twig'));
        if($response == "0"){
            return new Response($this->generateUrl('participationEffectue',['id' => $participation->getId()]));
        }
        else return new Response("L'envoi du mail à échoué",500);
    }

    /**
     * @Route("/xhr/connect", name="connect")
     */
    public function connect(Request $request, EntityManagerInterface $entityManager, MailService $mailService)
    {

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

    /**
     * @Route("/connect/check", name="connectCheck")
     */
    public function connectCheck(Request $request,FacebookAuthenticator $facebookAuthenticator, EntityManagerInterface $entityManager, CommercantAuthenticator $commercantAuthenticator)
    {
        $credentials = $request->query->all();
        $participant = $entityManager->getRepository(Participant::class)->findOneBy(['email' => $credentials['email']]);
        if(is_null($participant)){
            $commercant = $entityManager->getRepository(Commercant::class)->findOneBy(['email' => $credentials['email']]);
            if(is_null($commercant)){
                $this->addFlash("danger", "Votre adresse mail ou votre mot de passe n'est pas correct");
                return $this->redirectToRoute('app_login');
            }
            else{
                if ($commercantAuthenticator->checkCredentials($credentials,$commercant)){
                    $token = new UsernamePasswordToken($commercant, $commercant->getTelephone(), 'public', $commercant->getRoles());
                    $this->get('security.token_storage')->setToken($token);
                    $this->addFlash('success', "Bienvenu chez Shoppon " . $commercant->getEmail() . " ! Vous pouvez maintenant créer des offres groupées ! ");
                    return $this->redirectToRoute('formCampagne');
                }
            }

        }
        else{
            if($facebookAuthenticator->checkPassword($participant,$credentials['mdp'])){
                $token = new UsernamePasswordToken($participant, $participant->getPassword(), 'public', $participant->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->addFlash('success', "Bienvenu chez Shoppon " . $participant->getUsername() . " ! Vous pouvez maintenant prendre place à des offres groupées");
                return $this->redirectToRoute('allCampagne');
            }
            else{
                $this->addFlash('danger','Votre mot de passe est incorrect');
                $this->redirect($request->headers->get('referer'));
            }
        }
            return $this->redirectToRoute('app_login');
    }




}
