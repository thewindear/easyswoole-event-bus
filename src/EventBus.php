<?php

namespace Thewindear\EasyswooleEventBus;

use EasySwoole\Component\Singleton;
use EasySwoole\Queue\Job;
use EasySwoole\Queue\Queue;
use EasySwoole\Queue\QueueDriverInterface;

class EventBus
{
    use Singleton;
    use CallListener;

    public static array $listen = [
    ];

    private Queue $queue;

    public function __construct(QueueDriverInterface $queueDriver)
    {
        $this->queue = new Queue($queueDriver);
    }

    /**
     * 获取event绑定的所有listener
     * @param $event
     * @return array|mixed
     */
    public function getListeners($event)
    {
        return self::$listen[$event] ?? [];
    }

    /**
     * 获取队列
     * @return Queue
     */
    public function getQueue(): Queue
    {
        return $this->queue;
    }


    /**
     * 添加事件绑定监听
     * @param string $event
     * @param array $listen
     * @return void
     * @throws \ReflectionException
     */
    public function add(string $event, array $listen)
    {
        $eventRefClass = new \ReflectionClass($event);
        if (!$eventRefClass->isSubclassOf(Event::class)) {
            throw new \TypeError("{$event} Does not inherit from Event");
        }
        if (count($listen) != 2) {
            throw new \InvalidArgumentException("The Listener array format is: ['ListenerClassName', 'ListenerMethodName']");
        }
        if (empty(self::$listen[$eventRefClass->getName()])) {
            self::$listen[$eventRefClass->getName()] = [];
        }
        foreach(self::$listen[$eventRefClass->getName()] as $listener) {
            if ($listener[0] == $listen[0] && $listener[1] == $listen[1]) {
                return;
            }
        }
        self::$listen[$eventRefClass->getName()][] = $listen;
    }

    /**
     * 触发同步事件的Listener
     * @param Event $event
     * @return void
     */
    public function fireSync(Event $event)
    {
        foreach(self::$listen as $eventName=>$listeners) {
            if ($event instanceof $eventName) {
                self::callListeners($listeners, $event);
            }
        }
    }

    /**
     * 触发异步事件的Listener
     * @param Event $event
     * @return void
     */
    public function fireAsync(Event $event): bool
    {
        $serialized = serialize($event);
        $job = new Job();
        $job->setJobId(intval(microtime(true) * 10000));
        $job->setJobData($serialized);
        if ($event->delay > 0) {
            $job->setDelayTime($event->delay);
        }
        return $this->queue->producer()->push($job);
    }

}