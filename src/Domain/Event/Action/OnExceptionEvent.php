<?php
namespace Yoanm\JsonRpcServer\Domain\Event\Action;

use Yoanm\JsonRpcServer\Domain\Event\JsonRpcServerEvent;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

/**
 * Class OnExceptionEvent
 *
 * Dispatched when a response has been successfully serialized by the endpoint and will be returned
 */
class OnExceptionEvent implements JsonRpcServerEvent
{
    const EVENT_NAME = 'json_rpc_server_skd.on_exception';

    /** @var \Exception */
    private $exception;
    /** @var JsonRpcRequest */
    private $fromJsonRpcRequest;

    /**
     * @param \Exception     $exception
     * @param JsonRpcRequest $fromJsonRpcRequest
     */
    public function __construct(\Exception $exception, JsonRpcRequest $fromJsonRpcRequest)
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
     * @return JsonRpcRequest
     */
    public function getFromJsonRpcRequest() : JsonRpcRequest
    {
        return $this->fromJsonRpcRequest;
    }

    /**
     * @param \Exception $exception
     *
     * @return OnExceptionEvent
     */
    public function setException(\Exception $exception) : OnExceptionEvent
    {
        $this->exception = $exception;

        return $this;
    }
}
