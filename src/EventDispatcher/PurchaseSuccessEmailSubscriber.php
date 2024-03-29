<?php

namespace App\EventDispatcher;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use App\Event\PurchaseSuccessEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PurchaseSuccessEmailSubscriber implements EventSubscriberInterface
{
    protected $logger;
    protected $mailer;
    protected $security;

    public function __construct(LoggerInterface $logger, MailerInterface $mailer, Security $security)
    {
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->security = $security;
    }
    public static function getSubscribedEvents()
    {
        return [
            'purchase.success' => 'sendSuccessEmail'
        ];
    }

    public function sendSuccessEmail(PurchaseSuccessEvent $purchaseSuccessEvent)
    {
        //1. Récuperer l'utilisateur actuellement en ligne (address mail) => Security componant
        /** @var User */
        $currentUser = $this->security->getUser();

        //2.Recuperer la commande PurchaseSuccessEvent obj -> commande
        $purchase = $purchaseSuccessEvent->getPurchase();

        //3. Ecrire le mail (nouveau templatedEmail)
        $email = (new TemplatedEmail());
        $email
            ->to(new Address($currentUser->getEmail(), $currentUser->getFullName()))
            ->from("contact@mail.com")
            ->subject("Bravo, votre commande ({$purchase->getId()}) a bien été confirmée")
            ->htmlTemplate('emails/purchase_success.html.twig')
            ->context([
                'purchase' => $purchase,
                'user' => $currentUser
            ]);

        //4. envoyer le mail (MailerInterface)
        $this->mailer->send($email);

        $this->logger->info("Email envoyé pour la commence numéro" . $purchaseSuccessEvent->getPurchase()->getId());
    }
}
