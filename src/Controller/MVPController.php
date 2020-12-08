<?php

namespace App\Controller;

use App\Entity\Campagne;
use App\Entity\Commentary;
use App\Entity\Commercant;
use App\Entity\Participant;
use App\Entity\Participation;
use App\Repository\CampagneRepository;
use App\Repository\CategorieRepository;
use App\Repository\MasterCategorieRepository;
use App\Repository\ParticipantRepository;
use App\Repository\ParticipationRepository;
use App\Security\CommercantAuthenticator;
use App\Security\FacebookAuthenticator;
use App\Service\ImageService;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Stripe\Stripe;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class MVPController extends AbstractController
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
        $commercantReponse = false;
        $campagne = $entityManager->getRepository(Campagne::class)->find($id);
        $backUrl = $request->headers->get('referer');
        $shareLink = $this->generateUrl('viewCampagne',['id' => $campagne->getId()],UrlGeneratorInterface::ABSOLUTE_URL);
        $commentaries = $entityManager->getRepository(Commentary::class)->findBy(['linkedCommentary' => null , "campagne" => $campagne->getId()]);
        foreach ($campagne->getCommentaries() as $commentaire){
            if ($commentaire->getUser() == $campagne->getCommercant()){
                $commercantReponse = true;
            }
        }
        dump($commentaries, $commercantReponse);
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
     * @Route("/", name="home")
     */
    public function allCampagne(CampagneRepository $campagneRepository,CategorieRepository $categorieRepository, MasterCategorieRepository $masterCategorieRepository): Response
    {
        $mostAdvancedCampagne = $campagneRepository->getMostAdvancedCampagne();
        $categories = $categorieRepository->findBy([],["id" => 'DESC'],8);
        $masterCategories = $masterCategorieRepository->findBy([],['id' => 'DESC']);
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
            'masterCategories' => $masterCategories
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
        $participation->setPaid(false)->setOrderId("noOrderId");
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
                    if(array_search("ROLE_ADMIN",$commercant->getRoles())){
                        $this->addFlash('success', "Bienvenu administrateur.");
                        return $this->redirectToRoute('admin');
                    }

                    $this->addFlash('success', "Bienvenu chez Shoppon " . $commercant->getEmail() . " ! Vous pouvez maintenant créer des offres groupées ! ");
                    return $this->redirectToRoute('formCampagne');
                }
                else{
                    $this->addFlash("danger", "Votre adresse mail ou votre mot de passe n'est pas correct");
                    return $this->redirectToRoute('app_login');
                }
            }

        }
        else{
            if($facebookAuthenticator->checkPassword($participant,$credentials['mdp'])){
                $token = new UsernamePasswordToken($participant, $participant->getPassword(), 'public', $participant->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->addFlash('success', "Bienvenu chez Shoppon " . $participant->getUsername() . " ! Vous pouvez maintenant prendre place à des offres groupées");
                if($request->cookies->get('referer')){
                    return $this->redirect($request->cookies->get('referer'));
                }else{
                    return $this->redirectToRoute('home');
                }
            }
            else{
                $this->addFlash('danger','Votre mot de passe est incorrect');
                $this->redirect($request->headers->get('referer'));
            }
        }
        return $this->redirectToRoute('app_login');
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

    /**
     * @Route("/paiementConfirme", name="paiementConfirme")
     */
    public function paiementConfirme(Request $request,ParticipationRepository $participationRepository,MailService $mailService): Response
    {
        $em = $this->getDoctrine()->getManager();
        $participation = $participationRepository->find($request->get('id'));
        if($request->get('session_id') == $participation->getOrderId()){
            $participation->setPaid(true);
        }
        $em->persist($participation);
        $em->flush();
        $mailService->mailPaye($participation);
        $url = $shareLink = $this->generateUrl('viewCampagne',['id' => $participation->getCampagne()->getId()],UrlGeneratorInterface::ABSOLUTE_URL);
        return $this->render('mvp/successPaiement.html.twig',[
            'participation' => $participation,
            'shareLink' => $url
        ]);
    }

    /**
     * @Route("/getStripeSession", name="getStripeSession")
     */
    public function getStripeSession( EntityManagerInterface $entityManager,Request $request,ParticipationRepository $participationRepository)
    {
        $participation = $participationRepository->find($request->get('participationId'));
        if($participation->getPaid()){
            return new Response('alreadyPaid');
        }
        Stripe::setApiKey('sk_test_51Hs83lFxEIGkLsaFTjw4xNqV16HQq8klCxbABCdhd326eaPSY1mwBJ0anhMTy9pF4h87XmWjN9sW1f9UnOYPEd0k002veln1bt');
        $options = [
            'payment_method_types' => ['card'],
            'line_items' => [],
            'mode' => 'payment',
            'success_url' => $this->generateUrl("paiementConfirme",[],UrlGeneratorInterface::ABSOLUTE_URL) ."?session_id={CHECKOUT_SESSION_ID}&id=".$participation->getId(),
            'cancel_url' => $this->generateUrl("participationEffectue",['cancelled' => true],UrlGeneratorInterface::ABSOLUTE_URL) ."&id=".$participation->getId(),
        ];

        $campagne = $participation->getCampagne();

        array_push($options['line_items'],
            [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $campagne->getTitre()  ,
                        'images' => [
                            $campagne->getLogo()->getUrlPath()
                        ]
                    ],
                    'unit_amount' => $campagne->getPrixPromotion() * 100,
                ],
                'quantity' => $participation->getQuantity(),
            ]);
        return new Response(json_encode(\Stripe\Checkout\Session::create($options)));

    }

    /**
     * @Route("/createOrder", name="createOrder")
     */
    public function createOrder( EntityManagerInterface $entityManager,Request $request,ParticipationRepository $participationRepository)
    {
        $participation = $participationRepository->find($request->get('participationId'));
        $participation->setOrderId($request->get('session_id'));
        $entityManager->persist($participation);
        $entityManager->flush();
        return new Response();
    }

    /**
     * @Route("/createCommentary", name="createCommentary")
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






}
