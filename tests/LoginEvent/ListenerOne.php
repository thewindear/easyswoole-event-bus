<?php

namespace Test\LoginEvent;

class ListenerOne
{
    public function handle(DemoEvent $event)
    {
        $time = (int) microtime(true) * 1000;
        file_put_contents(__DIR__ . "/test_{$time}", json_encode($event->data));
    }

}