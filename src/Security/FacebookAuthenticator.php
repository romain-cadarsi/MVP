<?php

namespace App\Security;

use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use KnpU\OAuth2ClientBundle\Client\Provider\FacebookClient;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\FacebookUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use TwitterPhp\Connection\User;

class FacebookAuthenticator extends SocialAuthenticator
{
    /**
     * @var ClientRegistry
     */
    private $clientRegistry;

    /**
     * @var EntityManagerInterface
     */
    private $em;


    private $encoder;

    /**
     * FacebookAuthenticator constructor.
     * @param ClientRegistry $clientRegistry
     * @param EntityManagerInterface $em
     */
    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em,UserPasswordEncoderInterface $encoder)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->encoder = $encoder;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_facebook_check';
    }

    /**
     * @param Request $request
     * @return \League\OAuth2\Client\Token\AccessToken|mixed
     */
    public function getCredentials(Request $request)
    {
        // this method is only called if supports() returns true

        return $this->fetchAccessToken($this->getFacebookClient());
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return Participant|null|object|\Symfony\Component\Security\Core\User\UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var FacebookUser $facebookUser */
        $facebookUser = $this->getFacebookClient()
            ->fetchUserFromToken($credentials);
        $email = $facebookUser->getEmail();
        $firstName = $facebookUser->getFirstName();
        $lastName = $facebookUser->getLastName();
        $username = $facebookUser->getName();

        // 1) have they logged in with Facebook before? Easy!
        $existingUser = $this->em->getRepository(Participant::class)
            ->findOneBy(['facebookId' => $facebookUser->getId()]);

        if ($existingUser) {
            $user = $existingUser;
        } else {
            // 2) do we have a matching user by email?
            $user = $this->em->getRepository(Participant::class)
                ->findOneBy(['email' => $email]);

            if (!$user) {
                /** @var Participant $user */
                $user = new Participant();
                $user->setEmail($email)
                    ->setFacebookId($facebookUser->getId())
                    ->setNom($lastName)
                    ->setPrenom($firstName)
                    ->setRoles(['ROLE_USER'])
                    ->setUsername($username)
                    ->setPassword($this->encoder->encodePassword($user,bin2hex(openssl_random_pseudo_bytes(4))))
                    ->setPictureUrl($facebookUser->getPictureUrl());


            }
        }

        // 3) Maybe you just want to "register" them by creating
        // a User object
        $user->setFacebookId($facebookUser->getId());
        $this->em->persist($user);
        $this->em->flush();

        return $userProvider->loadUserByUsername($user->getUsername());
    }

    /**
     * @return FacebookClient
     */
    private function getFacebookClient()
    {
        return $this->clientRegistry->getClient('facebook');
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return null|Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return null|Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     *
     * @param Request $request
     * @param AuthenticationException|null $authException
     *
     * @return RedirectResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            '/connect/', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

    /**
     * return encoded Password
     * @param $user
     * @param $plainPassword
     * @return string
     */
    public function encodePassword( $user, $plainPassword){
        return $this->encoder->encodePassword($user,$plainPassword);
    }

    /** Check if password is valid
     * @param $user
     * @param $plainPassword
     * @return bool
     */
    public function checkPassword($user,$plainPassword){
        return $this->encoder->isPasswordValid($user,$plainPassword);
    }

    public function loadDatabaseUser($username,UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($username);
    }
}