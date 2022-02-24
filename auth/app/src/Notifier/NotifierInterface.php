<?php


namespace App\Notifier;

interface NotifierInterface
{
    public function debug(...$messages): void;

    public function info(...$messages): void;

    public function notice(...$messages): void;

    public function warning(...$messages): void;

    public function error(...$messages): void;

    public function critical(...$messages): void;

    public function alert(...$messages): void;

    public function emergency(...$messages): void;

    public function log(...$messages): void;
}
