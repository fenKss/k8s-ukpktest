<?php


namespace App\Notifier;

use App\Service\KafkaService;
use Symfony\Component\HttpFoundation\RequestStack;
use Throwable;

class Notifier extends AbstractNotifier implements NotifierInterface
{

    /**
     * @var \App\Service\KafkaService
     */
    private KafkaService $kafkaService;

    public function __construct(
        RequestStack $requestStack,
        KafkaService $kafkaService
    ) {
        parent::__construct($requestStack);
        $this->kafkaService = $kafkaService;
    }

    protected function modifyTrace(string $trace): string
    {
        return "<code> $trace</code>";
    }

    protected function send(string $content): bool
    {
        try {
            $data = [
                '__event' => "log",
                'content' => $_ENV['HOSTNAME'].PHP_EOL.$content
            ];
            $this->kafkaService->send('logtail', json_encode($data));
        } catch (Throwable $e) {
            return false;
        }
        return true;
    }
}
