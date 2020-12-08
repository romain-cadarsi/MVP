<?php
namespace App\Service;

use App\Entity\Participation;
use App\PHPMailer\PHPMailer;
use Exception;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailService{

    private $mailer;

    public function __construct(MailerInterface $mailer){
        $this->mailer = $mailer;
    }
    public function mailConfirmation(Participation $participation,$html){
        $email = (new Email())
            ->from('automatic@shoppon.com')
            ->to($participation->getParticipant()->getEmail())
            ->subject('Participation enregistrée')
            ->html("<p>Merci de votre participation, elle reste enregistrée pendant 24h jusqu'elle soit réglée. Pour la régler, veuillez cliquer ici <a href='http://localhost:8003/participationEffectue?id=" . $participation->getId() . "'> Payer </a> </p>");

        $this->mailer->send($email);
        return '0';
    }
    public function mailPaye(Participation $participation){
        $email = (new Email())
            ->from('automatic@shoppon.com')
            ->to($participation->getParticipant()->getEmail())
            ->subject('Participation confirmée')
            ->html("<p>Merci de votre participation , vous serez notifié lorsque la campagne touchera à sa fin. Veuillez trouver ci-dessous les détails de votre achat</p>");

        $this->mailer->send($email);
    }

    public function reponseQuestion($user,$campagne,$link){
        $email = (new Email())
            ->from('automatic@shoppon.com')
            ->to($user->getEmail())
            ->subject('Vous avez recu une réponse !')
            ->html("<p>" . $user->getPseudo() . " à répondu à votre question sur l'offre groupée sur <b>" . $campagne->getTitre() . " <b> , <a href='$link'> Accedez y ! </a> </p>");

        $this->mailer->send($email);
    }

}