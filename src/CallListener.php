<?php

namespace Thewindear\EasyswooleEventBus;

trait CallListener
{
    private static function callListeners(array $listeners, $event, $isAsync = false)
    {
        foreach($listeners as $listener) {
            if ($isAsync) {
                // 这里开启协程进行消费
                go(function () use ($listener, $event) {
                    self::callListener($listener, $event);
                });
            } else {
                self::callListener($listener, $event);
            }
        }
    }

    private static function callListener(array $listener, $event)
    {
        $className = $listener[0];
        $handle = $listener[1];
        $instance = new $className();
        try {
            if (!method_exists($instance, $handle)) {
                throw new \BadMethodCallException("{$className} not found method {$handle}.");
            }
            $instance->$handle($event);
        } catch (\Throwable $e) {
            if (!method_exists($instance, 'failed')) {
                throw $e;
            }
            // 出现异常后调用failed方法将异常和event传入
            $instance->failed($event, $e);
        }
    }
}