<?php


namespace App\EventListener;

use App\Notifier\NotifierInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    private NotifierInterface     $notifier;

    public function __construct(
        NotifierInterface $notifier
    ) {
        $this->notifier              = $notifier;
    }

    /**
     * @throws \Throwable
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $this->notifier->error($event->getThrowable());
    }
}
