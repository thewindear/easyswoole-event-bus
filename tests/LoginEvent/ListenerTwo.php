<?php

namespace Test\LoginEvent;

class ListenerTwo
{
    public function handle(DemoEvent $event)
    {
        $time = (int) microtime(true) * 1000;
        file_put_contents(__DIR__ . "/test_log_{$time}", json_encode($event->data));
        throw new \Exception('exception to failed');
    }

    public function failed(DemoEvent $event, \Exception $e)
    {
        file_put_contents(__DIR__ . "/test_exception.log", "{$e->getMessage()} data: {$event->toJSON()}");
    }
}