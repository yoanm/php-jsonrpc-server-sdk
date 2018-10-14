<?php
namespace Yoanm\JsonRpcServer\Domain;

use Yoanm\JsonRpcServer\Domain\Event\JsonRpcServerEvent;

/**
 * Class JsonRpcServerDispatcherInterface
 */
interface JsonRpcServerDispatcherInterface
{
    /**
     * @param string                  $eventName
     * @param JsonRpcServerEvent|null $event
     *
     * @return void
     */
    public function dispatchJsonRpcEvent(string $eventName, JsonRpcServerEvent $event = null) : void;

    /**
     * @param string   $eventName
     * @param callable $listener
     *
     * @return void
     */
    public function addJsonRpcListener(string $eventName, $listener) : void;
}
