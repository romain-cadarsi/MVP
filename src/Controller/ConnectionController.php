<?php

namespace App\Controller;

use App\Entity\Commercant;
use App\Entity\Participant;
use App\Security\CommercantAuthenticator;
use App\Security\FacebookAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Routing\Annotation\Route;

class ConnectionController extends ShopponController
{

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

}