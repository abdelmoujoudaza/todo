<?php

namespace App\EventListeners;

use App\Entity\User;
use Doctrine\ORM\Events;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;

class UserCreateSubscriber implements EventSubscriberInterface
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ( ! $entity instanceof User) {
            return;
        }

        $email = (new TemplatedEmail())
            ->subject('Bienvenue à notre nouvel collaborateur')
            ->htmlTemplate('emails/notify_admin.html.twig')
            ->context([
                'user' => $entity,
            ]);

        $this->mailer->send($email);
    }
}