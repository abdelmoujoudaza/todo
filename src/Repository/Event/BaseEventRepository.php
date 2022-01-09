<?php

namespace App\Repository\Event;

use App\Service\DispatchEventService;

class BaseEventRepository
{
    protected $dispatchEventService;

    public function __construct(DispatchEventService $dispatchEventService)
    {
        $this->dispatchEventService = $dispatchEventService;
    }
}