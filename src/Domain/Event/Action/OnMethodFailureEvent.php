<?php
namespace Yoanm\JsonRpcServer\Domain\Event\Action;

use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

/**
 * Class OnMethodFailureEvent
 *
 * Dispatched only in case JSON-RPC method throw an exception during execution
 */
class OnMethodFailureEvent extends AbstractOnMethodEvent
{
    const EVENT_NAME = 'json_rpc_server_skd.on_method_failure';

    /** @var \Exception */
    private $exception;

    /**
     * @param \Exception             $exception
     * @param JsonRpcMethodInterface $method
     * @param JsonRpcRequest         $jsonRpcRequest
     */
    public function __construct(
        \Exception $exception,
        JsonRpcMethodInterface $method,
        JsonRpcRequest $jsonRpcRequest
    ) {
        $this->exception = $exception;

        parent::__construct($method, $jsonRpcRequest);
    }

    /**
     * @return \Exception
     */
    public function getException() : \Exception
    {
        return $this->exception;
    }

    /**
     * @param \Exception $exception
     *
     * @return OnMethodFailureEvent
     */
    public function setException(\Exception $exception) : OnMethodFailureEvent
    {
        $this->exception = $exception;

        return $this;
    }
}
