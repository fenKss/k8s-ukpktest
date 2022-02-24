<?php

namespace App\Events;

interface EventInterface
{
    public function getName() : string;

    public function getAll(): iterable;

    public function get(string $key);

    public function set(string $key, $value);

    public function getTopic(): string;

    public function setTopic(string $topic);
}