<?php
namespace Yoanm\JsonRpcServer\Domain;

use Yoanm\JsonRpcServer\Domain\Event\JsonRpcServerEvent;

/**
 * Class JsonRpcServerDispatcherInterface
 */
interface JsonRpcServerDispatcherInterface
{
    /**
     * @param JsonRpcServerEvent $event
     *
     * @return void
     */
    public function dispatchJsonRpcEvent(string $eventName, JsonRpcServerEvent $event = null);

    /**
     * @param string   $eventName
     * @param callable $listener
     *
     * @return void
     */
    public function addJsonRpcListener(string $eventName, $listener);
}
