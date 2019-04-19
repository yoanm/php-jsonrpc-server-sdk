<?php
namespace Yoanm\JsonRpcServer\Domain\Event\Action;

use Yoanm\JsonRpcServer\Domain\Event\JsonRpcServerEvent;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

/**
 * Class OnExceptionEvent
 *
 * Dispatched when an exception occurred during sdk execution (For method execution exception see OnMethodFailureEvent)
 */
class OnExceptionEvent implements JsonRpcServerEvent
{
    const EVENT_NAME = 'json_rpc_server_skd.on_exception';

    /** @var \Exception */
    private $exception;
    /** @var null|JsonRpcRequest */
    private $fromJsonRpcRequest;

    /**
     * @param \Exception          $exception
     * @param JsonRpcRequest|null $fromJsonRpcRequest
     */
    public function __construct(\Exception $exception, JsonRpcRequest $fromJsonRpcRequest = null)
    {
        $this->exception = $exception;
        $this->fromJsonRpcRequest = $fromJsonRpcRequest;
    }

    /**
     * @return \Exception
     */
    public function getException() : \Exception
    {
        return $this->exception;
    }

    /**
     * @return null|JsonRpcRequest
     */
    public function getFromJsonRpcRequest() : ?JsonRpcRequest
    {
        return $this->fromJsonRpcRequest;
    }

    /**
     * @param \Exception $exception
     *
     * @return self
     */
    public function setException(\Exception $exception) : self
    {
        $this->exception = $exception;

        return $this;
    }
}
