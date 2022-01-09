<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

class DispatchEventService
{
    private EventDispatcherInterface $eventDispatcher;
    private LoggerInterface $logger;

    public function __construct(EventDispatcherInterface $eventDispatcher, LoggerInterface $logger)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    public function dispatchEvent(Event $event, string $eventName, array $subscribers = []): void
    {
        if ( ! empty($subscribers)) {
            array_walk($subscribers, function ($subscriber) {
                $this->eventDispatcher->addSubscriber($subscriber);
            });
        }

        $this->eventDispatcher->dispatch($event, $eventName);
        //log dispatched event
        $this->logger->info('Event dispatched '.$eventName, [$event]);
    }
}