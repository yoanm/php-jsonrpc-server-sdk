<?php
namespace Tests\Functional\BehatContext\App;

use Yoanm\JsonRpcServer\Domain\Event\Action\OnExceptionEvent;
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
     * @param string                  $eventName
     * @param JsonRpcServerEvent|null $event
     */
    public function dispatchJsonRpcEvent(string $eventName, JsonRpcServerEvent $event = null)
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
     * @param string   $eventName
     * @param callable $listener
     *
     * @return BehatRequestLifecycleDispatcher
     */
    public function addJsonRpcListener(string $eventName, $listener) : BehatRequestLifecycleDispatcher
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

        return $this;
    }

    /**
     * @return array
     */
    public function getEventDispatchedList()
    {
        return $this->eventDispatchedList;
    }
}
