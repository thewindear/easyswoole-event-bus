<?php

namespace Thewindear\EasyswooleEventBus;

class Event
{
    use SerializesModels;

    //public $queue = 'default';
    //public $pool = 'default';
    public $delay = 0;
    public $data;
    public function __construct(array $data)
    {
        $this->data = $data;
    }
}