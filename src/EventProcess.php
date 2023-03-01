<?php

namespace Thewindear\EasyswooleEventBus;

use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\Process\Config;
use EasySwoole\Component\Process\Manager;
use EasySwoole\Queue\Job;

class EventProcess extends AbstractProcess
{
    use CallListener;

    protected function run($arg)
    {
        self::listen();
    }

    /**
     * 监听消费
     * @return void
     * @throws \Throwable
     */
    public static function listen()
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
            self::callListeners($listeners, $event, true);
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