<?php

namespace App\Repository\Event;

use App\Entity\User;
use App\Event\UserCreateEvent;
use App\Service\DispatchEventService;
use App\EventListener\UserCreateSubscriber;
use Symfony\Component\Mailer\MailerInterface;

class UserEventRepository extends BaseEventRepository
{
    private $mailer;

    public function __construct(DispatchEventService $dispatchEventService, MailerInterface $mailer)
    {
        parent::__construct($dispatchEventService);
        $this->mailer = $mailer;
    }

    public function dispatchUserCreateEvent(User $user): void
    {
        $event = new UserCreateEvent($user);
        $subscribers = $this->getUserCreateEventSubscribers();
        $this->dispatchEventService->dispatchEvent($event, UserCreateEvent::NAME, $subscribers);
    }

    private function getUserCreateEventSubscribers(): array
    {
        return [
            new UserCreateSubscriber($this->mailer),
        ];
    }
}