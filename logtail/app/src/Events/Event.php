<?php

namespace App\Events;

class Event implements EventInterface
{
    private iterable $data  = [];
    private ?string  $topic = null;

    public function __construct(iterable $data)
    {
        $this->data = $data;
    }

    public function getName(): string
    {
        return $this->data["__event"] ?? '';
    }

    public function getAll(): iterable
    {
        return $this->data;
    }

    public function get(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function set(string $key, $value): Event
    {
        $this->data[$key] = $value;

        return $this;

    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function setTopic(string $topic): Event
    {
        $this->topic = $topic;

        return $this;
    }
}