<?php
namespace App\Service;

use App\Entity\Participation;
use App\PHPMailer\PHPMailer;
use Exception;

class MailService{

    public function mailConfirmation(Participation $participation,$html){
        try {
            $mail = new PHPMailer(true);
            $mail->CharSet = "utf-8";
            $subject = "Merci pour votre commande !";
            $name = "Shoppon";
            $mail             = new PHPMailer();
            $mail->IsSMTP();
            $mail->SMTPAuth   = true;
            $mail->SMTPOptions = array('ssl' => array('verify_peer' => false,'verify_peer_name' => false,'allow_self_signed' => true)); // ignorer l'erreur de certificat.
            $mail->Host       = "mail.shoppon.fr";
            $mail->Port       = 587;
            $mail->Username   = "automatic@shoppon.fr";
            $mail->Password   = "shC7r0_5";
            $mail->From       = "automatic@shoppon.fr"; //adresse d’envoi correspondant au login entré précédemment
            $mail->FromName   = $name; // nom qui sera affiché
            $mail->Subject    = $subject; // sujet
            $mail->Body = $html;
            // pièce jointe si besoin
            $mail->AddAddress($participation->getParticipant()->getEmail());
            $mail->IsHTML(true); // envoyer au format html, passer a false si en mode texte
            if(!$mail->Send()) {
                return "Mailer Error: " . $mail->ErrorInfo;
            } else {
                return "0";
            }
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }


}