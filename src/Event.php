<?php

namespace Thewindear\EasyswooleEventBus;

class Event
{
    use SerializesModels;

    //public $queue = 'default';
    //public $pool = 'default';
    public int $delay = 0;
    public array $data;
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function toJSON(): string
    {
        return json_encode($this->data);
    }

}