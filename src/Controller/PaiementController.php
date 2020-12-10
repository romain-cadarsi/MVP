<?php

namespace App\Controller;

use App\Repository\ParticipationRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;

class PaiementController extends ShopponController
{


    /**
     * @Route("/paiement/confirme", name="paiementConfirme")
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
     * @Route("/paiement/getStripeSession", name="getStripeSession")
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
     * @Route("/paiement/createOrder", name="createOrder")
     */
    public function createOrder( EntityManagerInterface $entityManager,Request $request,ParticipationRepository $participationRepository)
    {
        $participation = $participationRepository->find($request->get('participationId'));
        $participation->setOrderId($request->get('session_id'));
        $entityManager->persist($participation);
        $entityManager->flush();
        return new Response();
    }
}