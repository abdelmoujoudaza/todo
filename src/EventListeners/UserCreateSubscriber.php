<?php

namespace App\EventListener;

use App\Event\UserCreateEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserCreateSubscriber implements EventSubscriberInterface
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserCreateEvent::NAME => [
                ['notifyAdmin', 1],
            ],
        ];
    }

    public function notifyAdmin(UserCreateEvent $event): void
    {
        $user = $event->getUser();
        $email = (new TemplatedEmail())
            ->subject('Bienvenue à notre nouvel collaborateur')
            ->htmlTemplate('emails/notify_admin.html.twig')
            ->context([
                'user' => $user,
            ]);

        $this->mailer->send($email);
    }
}