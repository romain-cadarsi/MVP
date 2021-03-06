<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use TwitterPhp\Connection\User;

class FacebookController extends AbstractController
{
    /**
     * Link to this controller to start the "connect" process
     * @param ClientRegistry $clientRegistry
     *
     * @Route("/connect/facebook", name="connect_facebook_start")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function connectAction(ClientRegistry $clientRegistry,Request $request)
    {

        return $clientRegistry
            ->getClient('facebook')
            ->redirect([
                'public_profile', 'email'  // the scopes you want to access
            ],[])
            ;
    }

    /**
     * After going to Facebook, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     *
     * @param Request $request
     * @param ClientRegistry $clientRegistry
     *
     * @Route("/connect/facebook/check", name="connect_facebook_check")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {

        $this->addFlash('success', "Bienvenu chez Shoppon " . $this->getUser()->getUsername() . " ! Vous pouvez maintenant prendre place à des offres groupées");

        if($request->cookies->get('referer')){
            return $this->redirect($request->cookies->get('referer'));
        }
        else{
            return $this->redirectToRoute('home');
        }


    }
}