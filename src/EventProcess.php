<?php

namespace Thewindear\EasyswooleEventBus;

use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\Process\Config;
use EasySwoole\Component\Process\Manager;
use EasySwoole\Queue\Job;

class EventProcess extends AbstractProcess
{
    protected function run($arg)
    {
        self::handle();
    }

    /**
     * 监听消费
     * @return void
     * @throws \Throwable
     */
    public static function handle()
    {
        go(function () {
            EventBus::getInstance()->getQueue()->consumer()->listen(function (Job $job) {
                self::jobConsumer($job);
            });
        });
    }

    public static function jobConsumer(Job $job)
    {
        $event = unserialize($job->getJobData());
        $refObj = new \ReflectionObject($event);
        $className = $refObj->getName();
        $listeners = EventBus::getInstance()->getListeners($className);
        if (!empty($listeners)) {
            foreach($listeners as $listener) {
                // 这里开启协程进行消费
                go(function () use ($listener, $event) {
                    $listenerClassName = $listener[0];
                    $listenerHandle = $listener[1];
                    $instance = new $listenerClassName();
                    try {
                        $instance->$listenerHandle($event);
                    } catch (\Throwable $e) {
                        // 出现异常后调用failed方法将异常和event传入
                        $instance->failed($event, $e);
                    }
                });
            }
        }
    }

    /**
     * 添加消费process
     * @return void
     */
    public static function addProcess()
    {
        $config = new Config([
            'processName'=>'EventBusProcess',
            'processGroup'=>'EventBusQueue',
            'enableCoroutine'=>true
        ]);
        Manager::getInstance()->addProcess(new self($config));
    }

}