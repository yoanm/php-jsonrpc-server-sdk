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
    public function getFromJsonRpcRequest()
    {
        return $this->fromJsonRpcRequest;
    }

    /**
     * @param \Exception $exception
     */
    public function setException(\Exception $exception)
    {
        $this->exception = $exception;
    }
}
