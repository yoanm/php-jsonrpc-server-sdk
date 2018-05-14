<?php
namespace Yoanm\JsonRpcServer\App\Dispatcher;

use Yoanm\JsonRpcServer\Domain\Event\JsonRpcServerEvent;
use Yoanm\JsonRpcServer\Domain\JsonRpcServerDispatcherInterface;

/**
 * Class JsonRpcServerDispatcherAwareTrait
 */
Trait JsonRpcServerDispatcherAwareTrait
{
    /** @var JsonRpcServerDispatcherInterface */
    private $jsonRpcServerDispatcher = null;

    /**
     * @param JsonRpcServerDispatcherInterface $jsonRpcServerDispatcher
     */
    public function setJsonRpcServerDispatcher(JsonRpcServerDispatcherInterface $jsonRpcServerDispatcher)
    {
        $this->jsonRpcServerDispatcher = $jsonRpcServerDispatcher;
    }

    /**
     * @param string                  $eventName
     * @param JsonRpcServerEvent|null $event
     */
    protected function dispatchJsonRpcEvent(string $eventName, JsonRpcServerEvent $event = null)
    {
        // Do nothing if dispatcher is not there
        if ($this->jsonRpcServerDispatcher) {
            $this->jsonRpcServerDispatcher->dispatchJsonRpcEvent($eventName, $event);
        }
    }
}
