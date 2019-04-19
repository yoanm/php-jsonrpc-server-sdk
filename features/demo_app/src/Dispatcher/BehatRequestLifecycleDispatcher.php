<?php
namespace DemoApp\Dispatcher;

use Yoanm\JsonRpcServer\Domain\Event\JsonRpcServerEvent;
use Yoanm\JsonRpcServer\Domain\JsonRpcServerDispatcherInterface;

/**
 * Class BehatRequestLifecycleDispatcher
 */
class BehatRequestLifecycleDispatcher implements JsonRpcServerDispatcherInterface
{
    /** @var callable[] */
    private $listenerList = [];
    /** @var array */
    private $eventDispatchedList = [];

    /**
     * {@inheritdoc}
     */
    public function dispatchJsonRpcEvent(string $eventName, JsonRpcServerEvent $event = null) : void
    {
        $this->eventDispatchedList[] = [$eventName, $event];
        if (!array_key_exists($eventName, $this->listenerList)) {
            return;
        }

        foreach ($this->listenerList[$eventName] as $listener) {
            $listener($event, $eventName);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addJsonRpcListener(string $eventName, $listener) : void
    {
        if (!is_callable($listener)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'listener must be a callale, "%s" given',
                    is_object($listener) ? get_class($listener) : gettype($listener)
                )
            );
        }
        $this->listenerList[$eventName][] = $listener;
    }

    /**
     * @return array
     */
    public function getEventDispatchedList()
    {
        return $this->eventDispatchedList;
    }
}
