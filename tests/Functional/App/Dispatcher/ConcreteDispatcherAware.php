<?php

namespace Tests\Functional\App\Dispatcher;

use Yoanm\JsonRpcServer\App\Dispatcher\JsonRpcServerDispatcherAwareTrait;

/**
 * Class ConcreteDispatcherAware
 */
class ConcreteDispatcherAware
{
    use JsonRpcServerDispatcherAwareTrait;

    public function testDispatchJsonRpcEvent($eventName, $event)
    {
        $this->dispatchJsonRpcEvent($eventName, $event);
    }
}
