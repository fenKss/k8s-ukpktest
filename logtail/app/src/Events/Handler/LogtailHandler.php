<?php

namespace App\Events\Handler;

use App\Events\Event;
use App\Repository\UserRepository;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;

class LogtailHandler
{
    private UserRepository   $repository;
    private ChatterInterface $chatter;

    public function __construct(
        UserRepository $repository,
        ChatterInterface $chatter
    ) {
        $this->chatter    = $chatter;
        $this->repository = $repository;
    }

    public function logEvent(Event $event)
    {
        $content = $event->get('content');
        if (!$content) {
            return;
        }
        $chatMessage     = new ChatMessage($content);
        $telegramOptions = (new TelegramOptions())->parseMode('html')
                                                  ->disableWebPagePreview(true)
                                                  ->disableNotification(true);
        $chatMessage->options($telegramOptions);
        try {
            $this->chatter->send($chatMessage);
        } catch (\Throwable $e) {
            return false;
        }
        return true;
    }
}