<?php
namespace Yoanm\JsonRpcServer\Domain\Event\Action;

use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

/**
 * Class OnMethodFailureEvent
 *
 * Dispatched only in case JSON-RPC method thrown an exception.
 */
class OnMethodFailureEvent extends AbstractOnMethodEvent
{
    const EVENT_NAME = 'json_rpc_server_skd.on_method_failure';

    /** @var \Exception */
    private $exception;

    /**
     * @param \Exception             $exception
     * @param JsonRpcMethodInterface $method
     * @param JsonRpcRequest|null    $jsonRpcRequest
     */
    public function __construct(
        \Exception $exception,
        JsonRpcMethodInterface $method,
        JsonRpcRequest $jsonRpcRequest = null
    ) {
        $this->exception = $exception;

        parent::__construct($method, $jsonRpcRequest);
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param \Exception $exception
     */
    public function setException($exception)
    {
        $this->exception = $exception;
    }
}
