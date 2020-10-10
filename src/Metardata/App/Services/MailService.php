<?php

namespace Metardata\App\Services;

use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;
use Swift_Attachment;

/**
* This class allow to send mails with SwiftMailer
**/
class MailService
{
    protected $mailer;

    /**
    * Mail service constructor
    **/
    public function __construct(){
        $transport =
        (new Swift_SmtpTransport(
            SMTP_HOST,
            SMTP_PORT,
            SMTP_PROTOCOL
        ))
        ->setUsername(SMTP_LOGIN)
        ->setPassword(SMTP_PASSWORD);

        $this->mailer = new Swift_Mailer($transport);
    }

    /**
    * This function will send a mail to the support mail adress.
    **/
    public function sendSupportMessage($firstname,
                                       $name,
                                       $mail,
                                       $country,
                                       $message) {

        $userMessage  = "Bonjour $firstname, <br/>";
        $userMessage .= "<br/>";
        $userMessage .= "Nous avons bien reçu votre message et nous le traiterons";
        $userMessage .= " dans les plus brefs délais. <br/>";
        $userMessage .= "<br/>";
        $userMessage .= "Erwann, de l'équipe Métar'data";

        $supportMessage  = "Contact - $name $firstname <br/>";
        $supportMessage .= "<br/>";
        $supportMessage  = "Message: <br/>";
        $supportMessage .= "<br/>";
        $supportMessage .= "$message <br/>";
        $supportMessage .= "<br/>";
        $supportMessage .= "Bonne journée,";

        $mailUser = (new Swift_Message('Message enregistré'))
            ->setFrom(["21500894@etu.unicaen.fr" => 'Métar\'data'])
            ->setTo([$mail])
            ->setBody($userMessage, 'text/html');

        $mailSupport = (new Swift_Message("Contact - $name $firstname"))
            ->setFrom(["21500894@etu.unicaen.fr" => "$firstname $name"])
            ->setTo([SMTP_SUPPORT_ADRESS])
            ->setBody($supportMessage, 'text/html');

        // Send the message
        $resultUser    = $this->mailer->send($mailUser);
        $resultSupport = $this->mailer->send($mailSupport);
    }

    /**
    * This function will send a mail to the customer after he
    * pictures command.
    **/
    public function sendPictures($firstname, $mail, $attachments) {
        $userMessage  = "Bonjour $firstname, <br/>";
        $userMessage .= "<br/>";
        $userMessage .= "Merci d'avoir acheté sur Métar'data ! <br/>";
        $userMessage .= "<br/>";
        $userMessage .= "Les images achetées se trouvent en pièces jointes de ce mail.<br/>";
        $userMessage .= "<br/>";
        $userMessage .= "A très bientot ! <br/>";
        $userMessage .= '<img src="https://dev-21500894.users.info.unicaen.fr/web-application-project/public/skins/logo_mail.bmp"></img>';

        $mailUser = (new Swift_Message('Métar\'data - Livraison des images !'))
            ->setFrom(["21500894@etu.unicaen.fr" => 'Métar\'data'])
            ->setTo([$mail])
            ->setBody($userMessage, 'text/html');

        foreach ($attachments as $att) {
            $filename = explode("/", $att->getPath());

            $mailUser->attach(
                Swift_Attachment::fromPath($att->getPath())->setFilename($filename[sizeof($filename) - 1])
            );
        }

        $pictureMail = $this->mailer->send($mailUser);

    }
}
