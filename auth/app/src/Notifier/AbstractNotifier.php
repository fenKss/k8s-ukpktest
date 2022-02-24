<?php


namespace App\Notifier;

use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractNotifier implements NotifierInterface
{
    const
        DEBUG_TYPE     = 'DEBUG',
        INFO_TYPE      = 'INFO',
        NOTICE_TYPE    = 'NOTICE',
        WARNING_TYPE   = 'WARNING',
        ERROR_TYPE     = 'ERROR',
        CRITICAL_TYPE  = 'CRITICAL',
        ALERT_TYPE     = 'ALERT',
        EMERGENCY_TYPE = 'EMERGENCY';

    private ?Request $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    abstract protected function send(string $content): bool;

    public function debug(...$messages): void
    {
        $this->__send($this->__combineMessages(self::DEBUG_TYPE, $messages));
    }

    public function info(...$messages): void
    {
        $this->__send($this->__combineMessages(self::INFO_TYPE, $messages));
    }

    public function notice(...$messages): void
    {
        $this->__send($this->__combineMessages(self::NOTICE_TYPE, $messages));
    }

    public function warning(...$messages): void
    {
        $this->__send($this->__combineMessages(self::WARNING_TYPE, $messages));
    }

    public function error(...$messages): void
    {
        $this->__send($this->__combineMessages(self::ERROR_TYPE, $messages));
    }

    public function critical(...$messages): void
    {
        $this->__send($this->__combineMessages(self::CRITICAL_TYPE, $messages));
    }

    public function alert(...$messages): void
    {
        $this->__send($this->__combineMessages(self::ALERT_TYPE, $messages));
    }

    public function emergency(...$messages): void
    {
        $this->__send($this->__combineMessages(self::EMERGENCY_TYPE, $messages));
    }

    public function log(...$messages): void
    {
        $this->__send($this->__combineMessages(null, $messages));
    }

    public function getHeader(): string
    {
        [$ip, $method, $uri] = $this->getRequestData();
        $content    = [date("Y-m-d H:i:s")." ".$ip];
        if ($method || $uri) {
            $content[] = $method." ".$uri;
        }
        return $this->modifyDefaultContent(implode(PHP_EOL, $content));
    }

    protected function modifyDefaultContent(string $content): string
    {
        return $content;
    }

    protected function modifyTrace(string $trace): string
    {
        return $trace;
    }

    protected function getRequestData(): array
    {
        if ($this->request instanceof Request) {
            $ip     = $this->request->getClientIp();
            $method = $this->request->getMethod();
            $uri    = $this->request->getUri();
        } else {
            $ip     = "CRON";
            $method = "";
            $uri    = "";
        }
        return [$ip, $method, $uri];
    }

    private function __getContentFromMessages(array $messages): string
    {
        $body = [];
        foreach ($messages as $message) {
            if ($message instanceof \Throwable) {
                $body[] = implode(PHP_EOL, [
                    $message->getMessage(),
                    $message->getFile()."({$message->getLine()})",
                    $this->modifyTrace($message->getTraceAsString()),
                ]);
            } else {
                $body[] = print_r($message, true);
            }
        }
        return PHP_EOL . implode(PHP_EOL, $body) . PHP_EOL;
    }

    private function __combineMessages(?string $type, array $messages): string
    {
        $header = $this->getHeader();
        $type    = !$type ?: "[$type] ";
        return $type.$header.$this->__getContentFromMessages($messages);
    }

    private function __send(string $content): void
    {
        error_log($content);
        $this->send($content);
    }
}
